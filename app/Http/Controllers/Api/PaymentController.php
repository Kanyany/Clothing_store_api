<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display all payments.
     */
    public function index()
    {
        $payments = Payment::with([
            'sale',
        ])
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ]);
    }

    /**
     * Store a new payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => [
                'required',
                'integer',
                'exists:sales,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'payment_method' => [
                'required',
                'in:cash,aba,acleda,wing,chip_mong,bank_transfer,cash_on_delivery,card',
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

        $payment = DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Find Sale
            |--------------------------------------------------------------------------
            */

            $sale = Sale::lockForUpdate()
                ->findOrFail($validated['sale_id']);


            /*
            |--------------------------------------------------------------------------
            | Calculate Already Paid
            |--------------------------------------------------------------------------
            */

            $paidAmount = $sale->payments()
                ->sum('amount');


            /*
            |--------------------------------------------------------------------------
            | Calculate Remaining
            |--------------------------------------------------------------------------
            */

            $remainingAmount =
                (float) $sale->total_amount - (float) $paidAmount;


            /*
            |--------------------------------------------------------------------------
            | Check Remaining
            |--------------------------------------------------------------------------
            */

            if ($remainingAmount <= 0) {
                abort(
                    422,
                    'This sale has already been fully paid.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Check Payment Amount
            |--------------------------------------------------------------------------
            */

            if ((float) $validated['amount'] > $remainingAmount) {
                abort(
                    422,
                    'Payment amount cannot be greater than remaining amount.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Create Payment
            |--------------------------------------------------------------------------
            */

            $payment = Payment::create([
                'sale_id' => $sale->id,

                'amount' => $validated['amount'],

                'payment_method' =>
                    $validated['payment_method'],

                'reference_number' =>
                    $validated['reference_number'] ?? null,

                'note' =>
                    $validated['note'] ?? null,
            ]);


            return $payment;
        });


        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $payment->load([
            'sale',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Calculate Payment Summary
        |--------------------------------------------------------------------------
        */

        $paidAmount = $payment->sale
            ->payments()
            ->sum('amount');

        $remainingAmount =
            (float) $payment->sale->total_amount
            - (float) $paidAmount;


        return response()->json([
            'status' => 'success',

            'message' => 'Payment created successfully',

            'data' => [
                'payment' => $payment,

                'summary' => [
                    'sale_total' =>
                        (float) $payment->sale->total_amount,

                    'paid_amount' =>
                        (float) $paidAmount,

                    'remaining_amount' =>
                        max(0, $remainingAmount),

                    'payment_status' =>
                        $remainingAmount <= 0
                            ? 'paid'
                            : 'partial',
                ],
            ],
        ], 201);
    }

    /**
     * Display one payment.
     */
    public function show(string $id)
    {
        $payment = Payment::with([
            'sale',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $payment,
        ]);
    }

    /**
     * Update payment.
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount' => [
                'sometimes',
                'numeric',
                'min:0.01',
            ],

            'payment_method' => [
                'sometimes',
                'in:cash,aba,acleda,wing,chip_mong,bank_transfer,cash_on_delivery,card',
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


        /*
        |--------------------------------------------------------------------------
        | Check New Amount
        |--------------------------------------------------------------------------
        */

        if (isset($validated['amount'])) {

            $sale = $payment->sale;

            $otherPayments = $sale->payments()
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $remainingForThisPayment =
                (float) $sale->total_amount
                - (float) $otherPayments;

            if (
                (float) $validated['amount']
                > $remainingForThisPayment
            ) {
                abort(
                    422,
                    'Payment amount cannot be greater than remaining amount.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $payment->update($validated);


        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $payment->load([
            'sale',
        ]);


        return response()->json([
            'status' => 'success',

            'message' => 'Payment updated successfully',

            'data' => $payment,
        ]);
    }

    /**
     * Delete payment.
     */
    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully',
        ]);
    }
}