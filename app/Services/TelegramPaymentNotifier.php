<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramPaymentNotifier
{
    /**
     * Send a Telegram message.
     *
     * Telegram failures must never interrupt order/payment processing.
     */
    private function send(string $message): bool
    {
        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.admin_chat_id');

        if ($token === '' || $chatId === '') {
            Log::warning(
                'Telegram notification skipped: configuration missing.'
            );

            return false;
        }

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(
                    "https://api.telegram.org/bot{$token}/sendMessage",
                    [
                        'chat_id' => $chatId,
                        'text' => $message,
                        'disable_web_page_preview' => true,
                    ]
                );

            if (
                ! $response->successful()
                || ! $response->json('ok', false)
            ) {
                Log::warning('Telegram notification failed.', [
                    'http_status' => $response->status(),
                    'telegram_description' =>
                        $response->json('description'),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            // Never log the bot token or request URL.
            Log::warning('Telegram notification exception.', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Notify the admin about a newly created order.
     *
     * Call this only from the successful order-creation flow.
     */
    public function newOrder(Order $order): bool
    {
        $order->loadMissing(['user', 'payments']);

        $customerName = $order->user?->name ?: 'Not available';

        $payment = $order->payments
            ->sortByDesc('id')
            ->first();

        $paymentMethod = $payment?->payment_method;

        $paymentMethod = filled($paymentMethod)
            ? strtoupper(str_replace('_', ' ', $paymentMethod))
            : 'NOT RECORDED YET';

        $createdAt = $order->created_at
            ? $order->created_at->format('Y-m-d H:i:s')
            : 'N/A';

        $message =
            "🛍️ NEW ORDER #{$order->id}\n"
            . "Customer: {$customerName}\n"
            . 'Total: $'
            . number_format((float) $order->total, 2)
            . "\n"
            . 'Order status: '
            . strtoupper((string) ($order->status ?? 'pending'))
            . "\n"
            . 'Payment status: '
            . strtoupper(
                (string) ($order->payment_status ?? 'pending')
            )
            . "\n"
            . "Payment method: {$paymentMethod}\n"
            . "Created: {$createdAt}";

        return $this->send($message);
    }

    /**
     * Notify only for a newly recorded, verified Bakong payment
     * when the order is fully paid.
     *
     * The caller must determine whether the payment is newly recorded.
     */
    public function paid(
        Order $order,
        OrderPayment $payment,
        bool $alreadyRecorded = false
    ): bool {
        if ($alreadyRecorded) {
            return false;
        }

        if ((string) $order->payment_status !== 'paid') {
            return false;
        }

        // This notification is reserved for Bakong payments.
        if (strtolower((string) $payment->payment_method) !== 'bakong') {
            Log::notice(
                'Paid Telegram notification skipped: not a Bakong payment.',
                [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                ]
            );

            return false;
        }

        if ((int) $payment->order_id !== (int) $order->id) {
            Log::warning(
                'Paid Telegram notification skipped: order mismatch.',
                [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                ]
            );

            return false;
        }

        $order->loadMissing('user');

        $customerName = $order->user?->name ?: 'Not available';

        $paymentMethod = strtoupper(
            str_replace('_', ' ', (string) $payment->payment_method)
        );

        $currency = strtoupper((string) $payment->currency);

        $amount = number_format((float) $payment->amount, 2);

        $reference = $payment->reference_number ?: 'N/A';

        $message =
            "✅ PAYMENT PAID\n"
            . "Order: #{$order->id}\n"
            . "Customer: {$customerName}\n"
            . "Payment method: {$paymentMethod}\n"
            . "Amount: {$amount} {$currency}\n"
            . "Reference: {$reference}\n"
            . "Payment status: PAID";

        return $this->send($message);
    }

    /**
     * Pending polling attempts must not send Telegram messages.
     * Write details to Laravel logs only.
     */
    public function pending(
        Order $order,
        string $reason,
        ?string $md5 = null
    ): bool {
        Log::info('Bakong payment is still pending.', [
            'order_id' => $order->id,
            'reason' => $reason,
            'md5' => $md5,
        ]);

        return true;
    }

    /**
     * Verification failures must not send Telegram messages.
     * Write details to Laravel logs only.
     */
    public function failed(
        Order $order,
        string $reason,
        ?string $md5 = null
    ): bool {
        Log::warning('Bakong payment verification failed.', [
            'order_id' => $order->id,
            'reason' => $reason,
            'md5' => $md5,
        ]);

        return true;
    }
}
