<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderPaymentController extends Controller
{
    /**
     * Create a normal payment for an order.
     *
     * Supported currencies:
     * - USD
     * - KHR
     *
     * Order total is stored in USD.
     * KHR is converted to USD using KHR_PER_USD.
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

            $order = Order::lockForUpdate()
                ->findOrFail($order->id);

            $exchangeRate = (float) env(
                'KHR_PER_USD',
                4000
            );

            if ($validated['currency'] === 'KHR') {
                $amountUsd =
                    (float) $validated['amount']
                    / $exchangeRate;
            } else {
                $amountUsd =
                    (float) $validated['amount'];
            }

            $paidAmountUsd = (float) $order
                ->payments()
                ->sum('amount_usd');

            $orderTotalUsd = (float) $order->total;

            $remainingAmountUsd =
                $orderTotalUsd - $paidAmountUsd;

            if ($remainingAmountUsd <= 0) {
                abort(
                    422,
                    'This order has already been fully paid.'
                );
            }

            if (
                $amountUsd >
                ($remainingAmountUsd + 0.01)
            ) {
                abort(
                    422,
                    'Payment amount cannot be greater than remaining amount.'
                );
            }

            $amountUsd = min(
                $amountUsd,
                $remainingAmountUsd
            );

            $payment = OrderPayment::create([
                'order_id' => $order->id,

                'amount' => $validated['amount'],

                'amount_usd' => round(
                    $amountUsd,
                    2
                ),

                'currency' =>
                    $validated['currency'],

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
                $orderTotalUsd - $newPaidAmountUsd;

            if ($newRemainingAmountUsd < 0.01) {
                $newRemainingAmountUsd = 0;
            }

            if ($newRemainingAmountUsd <= 0) {

                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);

            } else {

                $order->update([
                    'payment_status' => 'partial',
                ]);
            }

            return [
                'payment' => $payment,

                'order' =>
                    $order->fresh(),

                'paid_amount_usd' =>
                    round(
                        $newPaidAmountUsd,
                        2
                    ),

                'remaining_amount_usd' =>
                    round(
                        $newRemainingAmountUsd,
                        2
                    ),

                'exchange_rate' =>
                    $exchangeRate,
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
                    'order_total_usd' =>
                        (float) $result['order']->total,

                    'paid_amount_usd' =>
                        $result['paid_amount_usd'],

                    'remaining_amount_usd' =>
                        $result['remaining_amount_usd'],

                    'exchange_rate' =>
                        $result['exchange_rate'],

                    'payment_status' =>
                        $result['order']->payment_status,

                    'order_status' =>
                        $result['order']->status,
                ],
            ],
        ], 201);
    }


    /**
     * Generate Bakong deeplink from KHQR.
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

        $token =
            config('services.bakong.token');

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
                ->timeout(30)
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
                                    'services.bakong.name'
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

            $bakongData =
                $response->json();

            if (
                data_get(
                    $bakongData,
                    'responseCode'
                ) !== 0
            ) {

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        data_get(
                            $bakongData,
                            'responseMessage',
                            'Bakong deeplink generation failed.'
                        ),

                    'bakong_response' =>
                        $bakongData,
                ], 422);
            }

            return response()->json([
                'status' => 'success',

                'message' =>
                    'Bakong deeplink generated successfully.',

                'data' =>
                    $bakongData,
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
     * Verify Bakong transaction by MD5.
     *
     * If Bakong confirms the transaction:
     * - Create OrderPayment
     * - Calculate USD amount
     * - Update order payment status
     * - Confirm order when fully paid
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

        $token =
            config('services.bakong.token');

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
                ->timeout(30)
                ->post(
                    $baseUrl .
                    '/v1/check_transaction_by_md5',
                    [
                        'md5' =>
                            $validated['md5'],
                    ]
                );

            if (!$response->successful()) {

                Log::warning(
                    'Bakong transaction verification failed',
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
                        'Bakong transaction verification failed.',

                    'bakong_status' =>
                        $response->status(),

                    'bakong_response' =>
                        $response->json(),
                ], 422);
            }

            $bakongData =
                $response->json();

            /*
             * Bakong responseCode:
             *
             * 0 = success
             * 1 = failed
             */
            $isSuccessful =
                (int) data_get(
                    $bakongData,
                    'responseCode',
                    1
                ) === 0;

            if (!$isSuccessful) {

                return response()->json([
                    'status' => 'success',

                    'message' =>
                        'Bakong transaction has not been confirmed.',

                    'data' => [
                        'verified' => false,

                        'bakong_response' =>
                            $bakongData,
                    ],
                ]);
            }

            /*
             * Transaction information returned by Bakong.
             */
            $transaction =
                data_get(
                    $bakongData,
                    'data'
                );

            if (!$transaction) {

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong returned no transaction data.',
                ], 422);
            }

            $transactionHash =
                data_get(
                    $transaction,
                    'hash'
                );

            $transactionCurrency =
                strtoupper(
                    data_get(
                        $transaction,
                        'currency'
                    )
                );

            $transactionAmount =
                (float) data_get(
                    $transaction,
                    'amount'
                );

            if (!$transactionHash) {

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Bakong transaction hash is missing.',
                ], 422);
            }

            if (!in_array(
                $transactionCurrency,
                ['USD', 'KHR'],
                true
            )) {

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Unsupported Bakong transaction currency.',
                ], 422);
            }

            if ($transactionAmount <= 0) {

                return response()->json([
                    'status' => 'error',

                    'message' =>
                        'Invalid Bakong transaction amount.',
                ], 422);
            }

            /*
             * Prevent the same Bakong transaction
             * from being recorded twice.
             */
            $existingPayment =
                OrderPayment::where(
                    'transaction_hash',
                    $transactionHash
                )->first();

            if ($existingPayment) {

                return response()->json([
                    'status' => 'success',

                    'message' =>
                        'Bakong transaction has already been recorded.',

                    'data' => [
                        'verified' => true,

                        'already_recorded' => true,

                        'payment' =>
                            $existingPayment->load('order'),

                        'bakong_response' =>
                            $bakongData,
                    ],
                ]);
            }

            $result = DB::transaction(
                function () use (
                    $order,
                    $transaction,
                    $transactionHash,
                    $transactionCurrency,
                    $transactionAmount
                ) {

                    $order = Order::lockForUpdate()
                        ->findOrFail($order->id);

                    /*
                     * Make sure this order is not already fully paid.
                     */
                    $paidAmountUsd =
                        (float) $order
                            ->payments()
                            ->sum('amount_usd');

                    $orderTotalUsd =
                        (float) $order->total;

                    $remainingAmountUsd =
                        $orderTotalUsd
                        - $paidAmountUsd;

                    if ($remainingAmountUsd <= 0) {

                        abort(
                            422,
                            'This order has already been fully paid.'
                        );
                    }

                    /*
                     * Convert KHR to USD.
                     */
                    $exchangeRate = (float) env(
                        'KHR_PER_USD',
                        4000
                    );

                    if (
                        $transactionCurrency === 'KHR'
                    ) {

                        $amountUsd =
                            $transactionAmount
                            / $exchangeRate;

                    } else {

                        $amountUsd =
                            $transactionAmount;
                    }

                    /*
                     * Prevent overpayment.
                     */
                    if (
                        $amountUsd >
                        ($remainingAmountUsd + 0.01)
                    ) {

                        abort(
                            422,
                            'Bakong payment amount is greater than the remaining order amount.'
                        );
                    }

                    $amountUsd = min(
                        $amountUsd,
                        $remainingAmountUsd
                    );

                    /*
                     * Create payment record.
                     */
                    $payment =
                        OrderPayment::create([
                            'order_id' =>
                                $order->id,

                            'amount' =>
                                $transactionAmount,

                            'amount_usd' =>
                                round(
                                    $amountUsd,
                                    2
                                ),

                            'currency' =>
                                $transactionCurrency,

                            'payment_method' =>
                                'bakong',

                            'payment_provider' =>
                                'Bakong',

                            'reference_number' =>
                                $transactionHash,

                            'transaction_hash' =>
                                $transactionHash,

                            'md5' =>
                                null,

                            'note' =>
                                'Verified Bakong payment',
                        ]);

                    /*
                     * Calculate new totals.
                     */
                    $newPaidAmountUsd =
                        $paidAmountUsd
                        + $amountUsd;

                    $newRemainingAmountUsd =
                        $orderTotalUsd
                        - $newPaidAmountUsd;

                    if (
                        $newRemainingAmountUsd < 0.01
                    ) {

                        $newRemainingAmountUsd = 0;
                    }

                    /*
                     * Update order.
                     */
                    if (
                        $newRemainingAmountUsd <= 0
                    ) {

                        $order->update([
                            'payment_status' =>
                                'paid',

                            'status' =>
                                'confirmed',
                        ]);

                    } else {

                        $order->update([
                            'payment_status' =>
                                'partial',
                        ]);
                    }

                    return [
                        'payment' =>
                            $payment,

                        'order' =>
                            $order->fresh(),

                        'paid_amount_usd' =>
                            round(
                                $newPaidAmountUsd,
                                2
                            ),

                        'remaining_amount_usd' =>
                            round(
                                $newRemainingAmountUsd,
                                2
                            ),

                        'exchange_rate' =>
                            $exchangeRate,
                    ];
                }
            );

            $result['payment']->load('order');

            return response()->json([
                'status' => 'success',

                'message' =>
                    'Bakong transaction verified and payment recorded successfully.',

                'data' => [
                    'verified' => true,

                    'already_recorded' => false,

                    'payment' =>
                        $result['payment'],

                    'summary' => [
                        'order_total_usd' =>
                            (float) $result['order']->total,

                        'paid_amount_usd' =>
                            $result['paid_amount_usd'],

                        'remaining_amount_usd' =>
                            $result['remaining_amount_usd'],

                        'exchange_rate' =>
                            $result['exchange_rate'],

                        'payment_status' =>
                            $result['order']->payment_status,

                        'order_status' =>
                            $result['order']->status,
                    ],

                    'bakong_response' =>
                        $bakongData,
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
                    'Unable to verify Bakong transaction.',
            ], 500);
        }
    }
}
