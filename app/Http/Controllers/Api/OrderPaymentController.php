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

class OrderPaymentController extends Controller
{
    /**
     * Create a manual / non-Bakong payment record.
     *
     * POST /api/orders/{order}/payment
     */
    public function store(Request $request, Order $order)
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
                'in:cash,aba,acleda,wing,chip_mong,bank_transfer,cash_on_delivery,card,bakong',
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
             * Make the KHQR specific to this real order.
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

        Log::info('BAKONG QR GENERATED', [
            'order_id' => $order->id,
            'md5' => $md5,
            'amount' => $amount,
            'currency' => $currency,
        ]);


            if (!$qr || !$md5) {
                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong SDK did not return a valid KHQR.',
                ], 422);
            }

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
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
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
     */
    public function verify(
        Request $request,
        Order $order
    ) {
        $validated = $request->validate([
            'md5' => [
                'required',
                'string',
                'max:255',
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
            /*
             * Ask Bakong for the real transaction.
             */
           $response = Http::withToken($token)
            ->acceptJson()
            ->retry(3, 1500, throw: false)
            ->timeout(30)
            ->connectTimeout(15)
                ->withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_RESOLVE => [
                    'api-bakong.nbc.gov.kh:443:13.35.36.32',
                ],
            ],
        ])
            ->post(
                $baseUrl . '/v1/check_transaction_by_md5',
                [
                    'md5' => $validated['md5'],
                ]
            );      
                        if (!$response->successful()) {
                Log::warning(
                    'Bakong transaction verification failed',
                    [
                        'order_id' => $order->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Bakong verification is temporarily unavailable.',
                    'data' => [
                        'verified' => false,
                    ],
                ], 200);
            }

            $bakongData = $response->json();

            Log::info('FULL BAKONG VERIFY RESPONSE', [
                'order_id' => $order->id,
                'requested_md5' => $validated['md5'],
                'http_status' => $response->status(),
                'full_response' => $bakongData,
            ]);

            /*
             * Bakong success responseCode = 0.
             */
            $isSuccessful =
                (int) data_get(
                    $bakongData,
                    'responseCode',
                    -1
                ) === 0;

            $transaction =
                data_get(
                    $bakongData,
                    'data'
                );

            /*
             * Transaction is not confirmed yet.
             */
            if (
                !$isSuccessful ||
                !is_array($transaction)
            ) {
                return response()->json([
                    'status' => 'pending',
                    'message' => data_get(
                        $bakongData,
                        'responseMessage',
                        'Bakong payment has not been confirmed yet.'
                    ),
                    'data' => [
                        'verified' => false,
                        'bakong_response_code' => data_get(
                            $bakongData,
                            'responseCode'
                        ),
                    ],
                ], 200);
            }

            /*  
             * Real transaction amount.
             */
            $transactionAmount =
                (float) data_get(
                    $transaction,
                    'amount',
                    0
                );

            /*
             * Real transaction currency.
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
             * Expected order amount.
             */
            $expectedAmount =
                (float) $order->total;

            /*
             * Expected Bakong currency.
             */
            $expectedCurrency =
                strtoupper(
                    (string) config(
                        'services.bakong.currency',
                        'USD'
                    )
                );

            /*
             * Never mark the order as paid unless
             * amount and currency match.
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
             * Verify that the money was sent to
             * the configured store Bakong account.
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
                $merchantAccountId !== '' &&
                $toAccountId !== '' &&
                $merchantAccountId !==
                    $toAccountId
            ) {
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
             * Convert the verified Bakong amount
             * into USD for amount_usd.
             *
             * Your current configuration is:
             *
             * KHR_PER_USD=4000
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
             * Save payment safely inside a transaction.
             */
            $result = DB::transaction(
                function () use (
                    $order,
                    $transaction,
                    $validated,
                    $transactionAmount,
                    $transactionAmountUsd
                ) {
                    $lockedOrder =
                        Order::lockForUpdate()
                            ->findOrFail(
                                $order->id
                            );

                    /*
                     * Prevent duplicate recording
                     * of the same Bakong transaction.
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
                                $validated['md5']
                            )
                            ->first();

                    if ($existingPayment) {
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
                     * Use amount_usd because the order total
                     * is stored in USD.
                     */
                    $paidAmountUsd =
                        (float) $lockedOrder
                            ->payments()
                            ->sum('amount_usd');

                    $remainingAmountUsd =
                        (float) $lockedOrder->total -
                        $paidAmountUsd;

                    /*
                     * Order is already fully paid.
                     */
                    if ($remainingAmountUsd <= 0.01) {
                        return [
                            'payment' => null,

                            'order' =>
                                $lockedOrder->fresh(),

                            'already_recorded' =>
                                true,
                        ];
                    }

                    /*
                     * Prevent overpayment.
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
                     * IMPORTANT:
                     *
                     * amount_usd is required by your
                     * order_payments database table.
                     */
                    $payment =
                        OrderPayment::create([
                            'order_id' =>
                                $lockedOrder->id,

                            'amount' =>
                                $transactionAmount,

                            'amount_usd' =>
                                $transactionAmountUsd,

                            'currency' =>
                                $transactionCurrency,

                            'payment_method' =>
                                'bakong',

                            'payment_provider' =>
                                'Bakong',

                            'reference_number' =>
                                $validated['md5'],

                            'note' =>
                                data_get(
                                    $transaction,
                                    'description'
                                ),
                        ]);

                    /*
                     * Calculate the new paid amount.
                     */
                    $newPaidAmountUsd =
                        $paidAmountUsd +
                        $transactionAmountUsd;

                    $newRemainingAmountUsd =
                        (float) $lockedOrder->total -
                        $newPaidAmountUsd;

                    /*
                     * Fully paid.
                     */
                    if (
                        $newRemainingAmountUsd <= 0.01
                    ) {
                        $lockedOrder->update([
                            'payment_status' =>
                                'paid',

                            'status' =>
                                'confirmed',
                        ]);
                    } else {
                        /*
                         * Partially paid.
                         */
                        $lockedOrder->update([
                            'payment_status' =>
                                'partial',
                        ]);
                    }

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

            /*
             * Return success to Flutter.
             */
            return response()->json([
                'status' => 'success',

                'message' =>
                    'Bakong payment verified successfully.',

                'data' => [
                    'verified' => true,

                    'already_recorded' =>
                        $result['already_recorded'],

                    'payment' =>
                        $result['payment'],

                    'order' =>
                        $result['order'],

                    'transaction' =>
                        $transaction,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Bakong verification exception',
                [
                    'order_id' =>
                        $order->id,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'status' => 'error',

                'message' =>
                    'Unable to verify Bakong payment.',
            ], 500);
        }
    }
}