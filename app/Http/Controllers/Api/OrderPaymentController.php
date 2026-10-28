<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;
use App\Services\TelegramPaymentNotifier;

class OrderPaymentController extends Controller
{
    /**
     * Create a manual / non-Bakong payment record.
     *
     * POST /api/orders/{order}/payment
     */
    public function store(Request $request, Order $order, TelegramPaymentNotifier $telegram)
    {
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'currency' => [
                'required',
                'in:USD,KHR',
            ],

            'payment_method' => [
                'required',
                'in:cash,aba,acleda,wing,chip_mong,bank_transfer,cash_on_delivery,card',
            ],

            'payment_provider' => [
                'nullable',
                'string',
                'max:255',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        $result = DB::transaction(function () use ($validated, $order) {
            $lockedOrder = Order::lockForUpdate()
                ->findOrFail($order->id);

            /*
             * The order total is stored in USD.
             *
             * Convert KHR payment to USD when necessary.
             */
            $amount = (float) $validated['amount'];

            $amountUsd = strtoupper($validated['currency']) === 'USD'
                ? $amount
                : (
                    $amount /
                    (float) env('KHR_PER_USD', 4000)
                );

            $amountUsd = round($amountUsd, 2);

            /*
             * Calculate how much has already been paid
             * using normalized USD amounts.
             */
            $paidAmountUsd = (float) $lockedOrder
                ->payments()
                ->sum('amount_usd');

            $remainingAmountUsd =
                (float) $lockedOrder->total -
                $paidAmountUsd;

            if ($remainingAmountUsd <= 0.01) {
                abort(
                    422,
                    'This order has already been fully paid.'
                );
            }

            if ($amountUsd > $remainingAmountUsd + 0.01) {
                abort(
                    422,
                    'Payment amount cannot be greater than the remaining amount.'
                );
            }

            $payment = OrderPayment::create([
                'order_id' => $lockedOrder->id,

                'amount' => $amount,

                'amount_usd' => $amountUsd,

                'currency' => strtoupper(
                    $validated['currency']
                ),

                'payment_method' =>
                    $validated['payment_method'],

                'payment_provider' =>
                    $validated['payment_provider'] ?? null,

                'reference_number' =>
                    $validated['reference_number'] ?? null,

                'note' =>
                    $validated['note'] ?? null,
            ]);

            $newPaidAmountUsd =
                $paidAmountUsd + $amountUsd;

            $newRemainingAmountUsd =
                (float) $lockedOrder->total -
                $newPaidAmountUsd;

            if ($newRemainingAmountUsd <= 0.01) {
                $lockedOrder->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'confirmed_at' => $lockedOrder->confirmed_at ?? now(),
                ]);
            } else {
                $lockedOrder->update([
                    'payment_status' => 'partial',
                ]);
            }

            return [
                'payment' => $payment,
                'order' => $lockedOrder->fresh(),
                'paid_amount_usd' => $newPaidAmountUsd,
                'remaining_amount_usd' =>
                    max(0, $newRemainingAmountUsd),
            ];
        });

        $result['payment']->load('order');


        // Manual payments do not send Bakong PAYMENT PAID notifications.

        return response()->json([
            'status' => 'success',

            'message' =>
                'Order payment created successfully',

            'data' => [
                'payment' =>
                    $result['payment'],

                'summary' => [
                    'order_total' =>
                        (float) $result['order']->total,

                    'paid_amount_usd' =>
                        $result['paid_amount_usd'],

                    'remaining_amount_usd' =>
                        $result['remaining_amount_usd'],

                    'payment_status' =>
                        $result['order']->payment_status,

                    'order_status' =>
                        $result['order']->status,
                ],
            ],
        ], 201);
    }


    /**
     * Generate a REAL dynamic KHQR for this order.
     *
     * POST /api/orders/{order}/payment/khqr
     *
     * The amount comes from the real database order total.
     */
    public function khqr(Request $request, Order $order)
    {
        $accountId = trim(
            (string) config('services.bakong.account_id')
        );

        $merchantName = trim(
            (string) config('services.bakong.merchant_name')
        );

        $merchantCity = trim(
            (string) config('services.bakong.merchant_city')
        );

        if (
            $accountId === '' ||
            $merchantName === '' ||
            $merchantCity === ''
        ) {
            return response()->json([
                'status' => 'error',

                'message' =>
                    'Bakong merchant configuration is missing. Please configure BAKONG_ACCOUNT_ID, BAKONG_MERCHANT_NAME and BAKONG_MERCHANT_CITY.',
            ], 500);
        }

        /*
         * REAL DATABASE ORDER TOTAL.
         *
         * No fake amount.
         */
        $amount = (float) $order->total;

        if ($amount <= 0) {
            return response()->json([
                'status' => 'error',

                'message' =>
                    'This order has no payable amount.',
            ], 422);
        }

        $currency = strtoupper(
            (string) config(
                'services.bakong.currency',
                'USD'
            )
        );

        if (!in_array($currency, ['USD', 'KHR'], true)) {
            return response()->json([
                'status' => 'error',

                'message' =>
                    'Bakong currency must be USD or KHR.',
            ], 500);
        }

        try {
            $info = new IndividualInfo(
                bakongAccountID: $accountId,

                merchantName: $merchantName,

                merchantCity: $merchantCity,

                currency: $currency === 'KHR'
                    ? KHQRData::CURRENCY_KHR
                    : KHQRData::CURRENCY_USD,

                amount: $amount,

                expirationTimestamp: strval(
                    floor(microtime(true) * 1000) +
                    (5 * 60 * 1000)
                ),
            );

            /*
             * REAL ORDER IDENTIFIER.
             */
            $info->billNumber =
                'ORDER-' . $order->id;

            $storeLabel = config(
                'services.bakong.store_label'
            );

            $terminalLabel = config(
                'services.bakong.terminal_label'
            );

            $purpose = config(
                'services.bakong.purpose',
                'Clothing order payment'
            );

            if ($storeLabel) {
                $info->storeLabel = $storeLabel;
            }

            if ($terminalLabel) {
                $info->terminalLabel = $terminalLabel;
            }

            if ($purpose) {
                $info->purposeOfTransaction =
                    $purpose;
            }

            /*
             * Generate REAL KHQR.
             */
            $result =
                BakongKHQR::generateIndividual($info);

            $statusCode = (int) data_get(
                $result,
                'status.code'
            );

            if ($statusCode !== 0) {
                return response()->json([
                    'status' => 'error',

                    'message' =>
                        data_get(
                            $result,
                            'status.message',
                            'Unable to generate KHQR.'
                        ),
                ], 422);
            }

            $qr = data_get(
                $result,
                'data.qr'
            );

            $md5 = data_get(
                $result,
                'data.md5'
            );

            if (!$qr || !$md5) {
                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong SDK did not return a valid KHQR.',
                ], 422);
            }

            /*
             * LOG REAL KHQR DATA.
             */
            Log::info('BAKONG QR GENERATED', [
                'order_id' => $order->id,
                'md5' => $md5,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return response()->json([
                'status' => 'success',

                'message' =>
                    'Bakong KHQR generated successfully.',

                'data' => [
                    'order_id' => $order->id,

                    'amount' => $amount,

                    'currency' => $currency,

                    'qr' => $qr,

                    'md5' => $md5,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Bakong KHQR generation exception',
                [
                    'order_id' => $order->id,

                    'message' => $e->getMessage(),

                    'file' => $e->getFile(),

                    'line' => $e->getLine(),
                ]
            );

            return response()->json([
                'status' => 'error',

                'message' =>
                    'Unable to generate Bakong KHQR.',
            ], 500);
        }
    }


    /**
     * Generate Bakong deeplink from a KHQR string.
     *
     * POST /api/orders/{order}/payment/deeplink
     */
    public function deeplink(
        Request $request,
        Order $order
    ) {
        $validated = $request->validate([
            'qr' => [
                'required',
                'string',
            ],
        ]);

        $baseUrl = rtrim(
            config('services.bakong.base_url'),
            '/'
        );

        $token = config(
            'services.bakong.token'
        );

        if (!$baseUrl || !$token) {
            return response()->json([
                'status' => 'error',

                'message' =>
                    'Bakong API configuration is missing.',
            ], 500);
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->connectTimeout(10)
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE =>
                            CURL_IPRESOLVE_V4,

                        CURLOPT_RESOLVE => [
                            'api-bakong.nbc.gov.kh:443:13.35.36.32',
                        ],
                    ],
                ])
                ->post(
                    $baseUrl .
                    '/v1/generate_deeplink_by_qr',
                    [
                        'qr' =>
                            $validated['qr'],

                        'sourceInfo' => [
                            'appIconUrl' =>
                                config(
                                    'services.bakong.app_icon_url'
                                ),

                            'appName' =>
                                config(
                                    'services.bakong.app_name',
                                    config(
                                        'services.bakong.name'
                                    )
                                ),

                            'appDeepLinkCallback' =>
                                config(
                                    'services.bakong.callback'
                                ),
                        ],
                    ]
                );

            if (!$response->successful()) {
                Log::error(
                    'Bakong deeplink request failed',
                    [
                        'status' =>
                            $response->status(),

                        'body' =>
                            $response->body(),
                    ]
                );

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong deeplink generation failed.',

                    'bakong_status' =>
                        $response->status(),

                    'bakong_response' =>
                        $response->json(),
                ], 422);
            }

            return response()->json([
                'status' => 'success',

                'message' =>
                    'Bakong deeplink generated successfully.',

                'data' =>
                    $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Bakong deeplink exception',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' => $e->getFile(),

                    'line' => $e->getLine(),
                ]
            );

            return response()->json([
                'status' => 'error',

                'message' =>
                    'Unable to connect to Bakong API.',
            ], 500);
        }
    }


    /**
     * Verify a REAL Bakong transaction by MD5.
     *
     * POST /api/orders/{order}/payment/verify
     *
     * IMPORTANT:
     * - Bakong responseCode 0 = transaction found.
     * - responseCode 1 = transaction not found yet.
     * - We never create fake payment records.
     */
    public function verify(
        Request $request,
        Order $order,
        TelegramPaymentNotifier $telegram
    ) {
        /*
         * Validate the MD5 returned by the REAL KHQR API.
         */
        $validated = $request->validate([
            'md5' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $md5 = trim($validated['md5']);

        /*
         * FIX:
         * Use Laravel config instead of hard-coding
         * Bakong credentials in this controller.
         */
        $baseUrl = rtrim(
            (string) config('services.bakong.base_url'),
            '/'
        );

        $token = config(
            'services.bakong.token'
        );

        if ($baseUrl === '' || !$token) {
            Log::error(
                'Bakong verification configuration missing',
                [
                    'order_id' => $order->id,
                ]
            );

            return response()->json([
                'status' => 'error',

                'message' =>
                    'Bakong API configuration is missing.',
            ], 500);
        }

        try {
            /*
             * =========================================================
             * API
             * Ask Bakong for the REAL transaction using MD5.
             * =========================================================
             */
            $response = Http::withToken($token)
                ->acceptJson()
                ->retry(
                    3,
                    1500,
                    throw: false
                )
                ->timeout(30)
                ->connectTimeout(15)
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE =>
                            CURL_IPRESOLVE_V4,

                        CURLOPT_RESOLVE => [
                            'api-bakong.nbc.gov.kh:443:13.35.36.32',
                        ],
                    ],
                ])
                ->post(
                    $baseUrl .
                    '/v1/check_transaction_by_md5',
                    [
                        'md5' => $md5,
                    ]
                );

            /*
             * HTTP request failed.
             *
             * Do not mark payment as paid.
             */
            if (!$response->successful()) {
                Log::warning(
                    'Bakong transaction verification HTTP failed',
                    [
                        'order_id' =>
                            $order->id,

                        'requested_md5' =>
                            $md5,

                        'http_status' =>
                            $response->status(),

                        'body' =>
                            $response->body(),
                    ]
                );

                $telegram->pending(
                    $order,
                    'Bakong verification is temporarily unavailable.',
                    $md5
                );

                return response()->json([
                    'status' => 'pending',

                    'message' =>
                        'Bakong verification is temporarily unavailable.',

                    'data' => [
                        'verified' => false,

                        'http_status' =>
                            $response->status(),
                    ],
                ], 200);
            }

            /*
             * Get REAL Bakong JSON response.
             */
            $bakongData = $response->json();

            /*
             * Debug log.
             */
            Log::info(
                'FULL BAKONG VERIFY RESPONSE',
                [
                    'order_id' =>
                        $order->id,

                    'requested_md5' =>
                        $md5,

                    'http_status' =>
                        $response->status(),

                    'full_response' =>
                        $bakongData,
                ]
            );

            /*
             * =========================================================
             * FIX:
             * Bakong responseCode 0 means SUCCESS.
             * =========================================================
             */
            $responseCode = (int) data_get(
                $bakongData,
                'responseCode',
                -1
            );

            $isSuccessful =
                $responseCode === 0;

            /*
             * REAL transaction returned by Bakong.
             */
            $transaction = data_get(
                $bakongData,
                'data'
            );

            /*
             * =========================================================
             * PENDING
             *
             * responseCode 1 means Bakong cannot find
             * the transaction at this moment.
             *
             * We DO NOT create a payment.
             * =========================================================
             */
            if (
                !$isSuccessful ||
                !is_array($transaction)
            ) {
                $telegram->pending(
                    $order,
                    (string) data_get(
                        $bakongData,
                        'responseMessage',
                        'Bakong payment has not been confirmed yet.'
                    ),
                    $md5
                );

                return response()->json([
                    'status' => 'pending',

                    'message' => data_get(
                        $bakongData,
                        'responseMessage',
                        'Bakong payment has not been confirmed yet.'
                    ),

                    'data' => [
                        'verified' => false,

                        'bakong_response_code' =>
                            $responseCode,
                    ],
                ], 200);
            }


            /*
             * =========================================================
             * REAL TRANSACTION DATA
             * =========================================================
             */

            $transactionAmount =
                (float) data_get(
                    $transaction,
                    'amount',
                    0
                );

            /*
             * FIX:
             * Read currency BEFORE DB transaction.
             *
             * This variable MUST also be passed into
             * the DB::transaction closure below.
             */
            $transactionCurrency =
                strtoupper(
                    (string) data_get(
                        $transaction,
                        'currency',
                        ''
                    )
                );

            /*
             * REAL ORDER TOTAL FROM DATABASE.
             */
            $expectedAmount =
                (float) $order->total;

            /*
             * Expected currency from configuration.
             */
            $expectedCurrency =
                strtoupper(
                    (string) config(
                        'services.bakong.currency',
                        'USD'
                    )
                );

            /*
             * =========================================================
             * SECURITY CHECK #1
             *
             * Payment amount and currency MUST match
             * the real order.
             * =========================================================
             */
            if (
                abs(
                    $transactionAmount -
                    $expectedAmount
                ) > 0.01
                ||
                $transactionCurrency !==
                    $expectedCurrency
            ) {
                $telegram->failed(
                    $order,
                    'Bakong payment amount or currency does not match the order.',
                    $md5
                );

                Log::warning(
                    'Bakong payment amount or currency mismatch',
                    [
                        'order_id' =>
                            $order->id,

                        'md5' =>
                            $md5,

                        'order_amount' =>
                            $expectedAmount,

                        'payment_amount' =>
                            $transactionAmount,

                        'order_currency' =>
                            $expectedCurrency,

                        'payment_currency' =>
                            $transactionCurrency,
                    ]
                );

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong payment does not match this order.',

                    'data' => [
                        'verified' => false,

                        'order_total' =>
                            $expectedAmount,

                        'order_currency' =>
                            $expectedCurrency,

                        'payment_amount' =>
                            $transactionAmount,

                        'payment_currency' =>
                            $transactionCurrency,
                    ],
                ], 422);
            }


            /*
             * =========================================================
             * SECURITY CHECK #2
             *
             * Make sure the money was sent to our
             * configured Bakong merchant account.
             * =========================================================
             */
            $merchantAccountId =
                trim(
                    (string) config(
                        'services.bakong.account_id'
                    )
                );

            $toAccountId =
                trim(
                    (string) data_get(
                        $transaction,
                        'toAccountId',
                        ''
                    )
                );

            if (
                $merchantAccountId !== ''
                &&
                $toAccountId !== ''
                &&
                $merchantAccountId !==
                    $toAccountId
            ) {
                $telegram->failed(
                    $order,
                    'Bakong payment receiver does not match the store account.',
                    $md5
                );

                Log::warning(
                    'Bakong payment receiver mismatch',
                    [
                        'order_id' =>
                            $order->id,

                        'md5' =>
                            $md5,

                        'configured_account' =>
                            $merchantAccountId,

                        'transaction_receiver' =>
                            $toAccountId,
                    ]
                );

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong payment receiver does not match the store account.',

                    'data' => [
                        'verified' => false,
                    ],
                ], 422);
            }


            /*
             * =========================================================
             * Convert verified amount to USD.
             *
             * Your project uses:
             *
             * KHR_PER_USD=4000
             * =========================================================
             */
            $transactionAmountUsd =
                $transactionCurrency === 'USD'
                    ? $transactionAmount
                    : (
                        $transactionAmount /
                        (float) env(
                            'KHR_PER_USD',
                            4000
                        )
                    );

            $transactionAmountUsd =
                round(
                    $transactionAmountUsd,
                    2
                );


            /*
             * =========================================================
             * DATABASE TRANSACTION
             *
             * FIX:
             * $transactionCurrency is explicitly included
             * in use (...).
             *
             * THIS FIXES:
             *
             * Undefined variable $transactionCurrency
             * =========================================================
             */
            $result = DB::transaction(
                function () use (
                    $order,
                    $transaction,
                    $validated,
                    $md5,
                    $transactionAmount,
                    $transactionAmountUsd,

                    // FIX:
                    // This was missing before.
                    $transactionCurrency
                ) {
                    /*
                     * Lock the order.
                     *
                     * This prevents duplicate payment updates
                     * when Flutter polls multiple times.
                     */
                    $lockedOrder =
                        Order::lockForUpdate()
                            ->findOrFail(
                                $order->id
                            );


                    /*
                     * =================================================
                     * DUPLICATE CHECK
                     *
                     * Same Bakong MD5 must NEVER be recorded twice.
                     * =================================================
                     */
                    $existingPayment =
                        $lockedOrder
                            ->payments()
                            ->where(
                                'payment_method',
                                'bakong'
                            )
                            ->where(
                                'reference_number',
                                $md5
                            )
                            ->first();

                    if ($existingPayment) {
                        /*
                         * FIX:
                         *
                         * The same transaction was already saved.
                         * Return it safely instead of creating another.
                         */
                        return [
                            'payment' =>
                                $existingPayment,

                            'order' =>
                                $lockedOrder->fresh(),

                            'already_recorded' =>
                                true,
                        ];
                    }


                    /*
                     * =================================================
                     * Calculate existing paid amount.
                     * =================================================
                     */
                    $paidAmountUsd =
                        (float) $lockedOrder
                            ->payments()
                            ->sum('amount_usd');


                    $remainingAmountUsd =
                        (float) $lockedOrder->total -
                        $paidAmountUsd;


                    /*
                     * Order already paid.
                     *
                     * Do not create another payment.
                     */
                    if (
                        $remainingAmountUsd <= 0.01
                    ) {
                        return [
                            'payment' => null,

                            'order' =>
                                $lockedOrder->fresh(),

                            'already_recorded' =>
                                true,
                        ];
                    }


                    /*
                     * =================================================
                     * Prevent overpayment.
                     * =================================================
                     */
                    if (
                        $transactionAmountUsd >
                        $remainingAmountUsd + 0.01
                    ) {
                        abort(
                            422,
                            'Bakong payment is greater than the remaining order amount.'
                        );
                    }


                    /*
                     * =================================================
                     * CREATE REAL PAYMENT RECORD
                     *
                     * No fake data.
                     *
                     * Data comes directly from Bakong API.
                     * =================================================
                     */
                    $payment =
                        OrderPayment::create([
                            'order_id' =>
                                $lockedOrder->id,

                            'amount' =>
                                $transactionAmount,

                            'amount_usd' =>
                                $transactionAmountUsd,

                            /*
                             * FIX:
                             * This now works because
                             * $transactionCurrency is passed
                             * through closure use (...).
                             */
                            'currency' =>
                                $transactionCurrency,

                            'payment_method' =>
                                'bakong',

                            'payment_provider' =>
                                'Bakong',

                            /*
                             * Use the REAL MD5 as reference.
                             */
                            'reference_number' =>
                                $md5,

                            /*
                             * Save the REAL Bakong description.
                             */
                            'note' =>
                                data_get(
                                    $transaction,
                                    'description'
                                ),
                        ]);


                    /*
                     * =================================================
                     * Calculate new paid amount.
                     * =================================================
                     */
                    $newPaidAmountUsd =
                        $paidAmountUsd +
                        $transactionAmountUsd;


                    $newRemainingAmountUsd =
                        (float) $lockedOrder->total -
                        $newPaidAmountUsd;


                    /*
                     * =================================================
                     * UPDATE ORDER
                     * =================================================
                     */
                    if (
                        $newRemainingAmountUsd <= 0.01
                    ) {
                        /*
                         * FULLY PAID
                         */
                       $lockedOrder->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'confirmed_at' => $lockedOrder->confirmed_at ?? now(),
                    ]);
                    } else {
                        /*
                         * PARTIALLY PAID
                         */
                        $lockedOrder->update([
                            'payment_status' =>
                                'partial',
                        ]);
                    }


                    /*
                     * Return everything needed by API response.
                     */
                    return [
                        'payment' =>
                            $payment,

                        'order' =>
                            $lockedOrder->fresh(),

                        'already_recorded' =>
                            false,
                    ];
                }
            );


            // Notify only a newly recorded, fully paid order.
            // Duplicate polling must not send duplicate paid alerts.
            if (
                !($result['already_recorded'] ?? false)
                && ($result['payment'] ?? null) instanceof OrderPayment
                && ($result['order']->payment_status ?? null) === 'paid'
            ) {
                $telegram->paid($result['order'], $result['payment']);
            }

            /*
             * =========================================================
             * SUCCESS RESPONSE
             * =========================================================
             */
            return response()->json([
                'status' => 'success',

                'message' =>
                    'Bakong payment verified successfully.',

                'data' => [
                    'verified' =>
                        true,

                    'already_recorded' =>
                        $result['already_recorded'],

                    'payment' =>
                        $result['payment'],

                    'order' =>
                        $result['order'],

                    /*
                     * Return REAL Bakong transaction.
                     */
                    'transaction' =>
                        $transaction,
                ],
            ]);
        } catch (\Throwable $e) {
            /*
             * =========================================================
             * ERROR LOG
             *
             * FIX:
             * Include file and line so future errors
             * are much easier to find.
             * =========================================================
             */
            Log::error(
                'Bakong verification exception',
                [
                    'order_id' =>
                        $order->id,

                    'md5' =>
                        $md5,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'status' => 'error',

                'message' =>
                    'Unable to verify Bakong payment.',

                /*
                 * Do not expose internal exception details
                 * to the Flutter application.
                 */
                'data' => [
                    'verified' => false,
                ],
            ], 500);
        }
    }
}