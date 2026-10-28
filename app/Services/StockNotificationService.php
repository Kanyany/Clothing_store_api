<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StockNotificationService
{
    /**
     * Create a stock notification only when the stock
     * changes from one state to another.
     *
     * States:
     * - normal
     * - low_stock
     * - out_of_stock
     */
    public function recordTransition(
        ProductVariant $variant,
        int $previousQuantity,
        int $currentQuantity,
        int $threshold
    ): void {
        $previousState = $this->state(
            $previousQuantity,
            $threshold
        );

        $currentState = $this->state(
            $currentQuantity,
            $threshold
        );

        // No state change: avoid duplicate notifications.
        if ($previousState === $currentState) {
            return;
        }

        // Recovering to normal stock is not a warning.
        if ($currentState === 'normal') {
            return;
        }

        try {
            $variant->loadMissing('product');

            $productName = $variant->product?->name
                ?? 'Product variant #' . $variant->id;

            if ($currentState === 'out_of_stock') {
                $title = 'Product Out of Stock';

                $message = $productName
                    . ' is out of stock. Current quantity: 0.';
            } else {
                $title = 'Low Stock Alert';

                $message = $productName
                    . ' has low stock. Current quantity: '
                    . $currentQuantity
                    . ', threshold: '
                    . $threshold
                    . '.';
            }

            $adminUsers = User::query()
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', ['Admin', 'Staff']);
                })
                ->get();

            foreach ($adminUsers as $adminUser) {
                $adminUser->notifications()->create([
                    'id' => (string) Str::uuid(),
                    'type' => 'stock',
                    'data' => [
                        'category' => 'stock',
                        'type' => 'stock',
                        'icon' => 'stock',
                        'title' => $title,
                        'message' => $message,
                        'product_variant_id' => $variant->id,
                        'quantity' => $currentQuantity,
                        'threshold' => $threshold,
                        'stock_state' => $currentState,
                        'url' => url('/admin/inventory'),
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            // Notification failures must not break stock updates.
            Log::warning('Stock notification creation failed.', [
                'product_variant_id' => $variant->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function state(int $quantity, int $threshold): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }

        if ($quantity <= $threshold) {
            return 'low_stock';
        }

        return 'normal';
    }
}
