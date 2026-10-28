<?php

namespace App\Services;

use App\Models\ChatbotFaq;
use App\Models\Product;
use Illuminate\Support\Str;

class ChatbotService
{
    public function reply(string $message): array
    {
        $message = trim($message);

        if ($message === '') {
            return $this->text('Please enter your question.');
        }

        // 1. Request human support.
        if ($this->containsAny($message, [
            'talk to admin',
            'speak to admin',
            'human',
            'agent',
            'staff',
            'support team',
            'បុគ្គលិក',
            'អ្នកលក់',
        ])) {
            return [
                'type' => 'handoff',
                'message' => 'I have sent your request to our support team. Please wait for an admin to reply.',
            ];
        }

        // 2. Handle normal and elongated greetings (Hi, hiii, heyyyy, etc.).
        if ($this->isGreeting($message)) {
            return $this->text(
                'Hi! 👋 Welcome to ALWAY WIN. I am your shopping assistant. You can browse products, track your orders, or ask me about shipping and delivery. How can I help you today?'
            );
        }

        // 3. FAQ answers from the database.
        $faq = $this->findFaq($message);

        if ($faq !== null) {
            return [
                'type' => 'faq',
                'message' => $faq->answer,
            ];
        }

        // 4. Order tracking. The Flutter Track My Order panel loads the real orders
        // from the authenticated /api/orders endpoint.
        if ($this->containsAny($message, [
            'track my order',
            'track my orders',
            'track order',
            'order status',
            'my order',
            'my orders',
            'តាមដានការបញ្ជាទិញ',
            'ការបញ្ជាទិញរបស់ខ្ញុំ',
        ])) {
            return $this->text(
                'I can help you check your orders. Your order list is shown below; select Track my order to refresh the latest status.'
            );
        }

        // 5. Shipping and delivery. Do not invent prices or delivery times.
        if ($this->containsAny($message, [
            'shipping',
            'delivery',
            'deliver',
            'delivery fee',
            'delivery time',
            'delivery area',
            'ដឹកជញ្ជូន',
        ])) {
            return $this->text(
                'For accurate delivery fees, delivery areas, and estimated delivery times, please contact our support team. You can also choose Track my order to check the current status of an existing order.'
            );
        }

        // 6. Returns and refunds remain supported if a customer asks directly.
        if ($this->containsAny($message, [
            'return',
            'refund',
            'exchange',
            'ប្ដូរទំនិញ',
            'សងប្រាក់',
        ])) {
            return $this->text(
                'For returns, exchanges, or refunds, please contact our support team with your order details. An admin can explain the available options.'
            );
        }

        // 7. General product discovery.
        if ($this->containsAny($message, [
            'find a product',
            'find products',
            'show products',
            'show me products',
            'browse products',
            'what products',
            'ទំនិញ',
            'ផលិតផល',
        ])) {
            return $this->searchProducts();
        }

        // 8. Search products by product name.
        $result = $this->searchProducts($message);

        if ($result !== null) {
            return $result;
        }

        // 9. Fallback.
        return [
            'type' => 'fallback',
            'message' => 'I could not find a matching answer. You can say Hi, browse products, track your orders, ask about shipping and delivery, or request an admin.',
        ];
    }

    private function isGreeting(string $message): bool
    {
        $normalized = Str::lower(trim($message));
        $normalized = preg_replace('/[!.,?]+$/u', '', $normalized) ?? $normalized;
        // Compress 3+ repeated characters: "hiii" becomes "hii".
        $normalized = preg_replace('/(.)\\1{2,}/u', '$1$1', $normalized) ?? $normalized;

        return in_array($normalized, [
            'hi', 'hii', 'hello', 'helo', 'hey', 'heyy',
            'good morning', 'good afternoon', 'good evening',
        ], true);
    }

    private function searchProducts(?string $keyword = null): ?array
    {
        $query = Product::query()
            ->where('status', true)
            ->with(['variants.inventory']);

        if ($keyword !== null && trim($keyword) !== '') {
            $query->where(
                'name',
                'like',
                '%' . $this->escapeLike(trim($keyword)) . '%'
            );
        }

        $products = $query
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return $keyword === null
                ? $this->text('There are no available products at the moment.')
                : null;
        }

        return [
            'type' => 'products',
            'message' => $keyword === null
                ? 'Here are some products available at ALWAY WIN:'
                : 'Here are the matching products:',
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'variants' => $product->variants
                        ->filter(fn ($variant) => (bool) $variant->status)
                        ->map(function ($variant) {
                            return [
                                'size' => $variant->size,
                                'color' => $variant->color,
                                'price' => $variant->selling_price,
                                'stock' => $variant->inventory?->quantity ?? 0,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })->all(),
        ];
    }

    private function findFaq(string $message): ?ChatbotFaq
    {
        $faqs = ChatbotFaq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($faqs as $faq) {
            if (Str::lower($faq->question) === Str::lower($message)) {
                return $faq;
            }

            foreach (($faq->keywords ?? []) as $keyword) {
                if (
                    is_string($keyword) &&
                    trim($keyword) !== '' &&
                    Str::contains(
                        Str::lower($message),
                        Str::lower($keyword)
                    )
                ) {
                    return $faq;
                }
            }
        }

        return null;
    }

    private function containsAny(string $message, array $keywords): bool
    {
        $message = Str::lower($message);

        foreach ($keywords as $keyword) {
            if (Str::contains($message, Str::lower($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }

    private function text(string $message): array
    {
        return [
            'type' => 'text',
            'message' => $message,
        ];
    }
}
