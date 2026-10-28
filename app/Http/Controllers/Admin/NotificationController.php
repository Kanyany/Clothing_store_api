<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stored = $user->notifications()
            ->latest()
            ->limit(100)
            ->get();

        $chats = ChatConversation::query()
            ->with([
                'customer:id,name',
                'messages' => fn ($query) => $query
                    ->where('sender_type', 'customer')
                    ->latest('id')
                    ->limit(1),
            ])
            ->whereHas('messages', fn ($query) =>
                $query->where('sender_type', 'customer')
            )
            ->orderByDesc('last_message_at')
            ->get();

        $chatNotifications = $chats->map(function ($chat) {
            $message = $chat->messages->first();

            if (!$message) {
                return null;
            }

            $createdAt = $message->created_at
                ?? $chat->last_message_at
                ?? $chat->created_at;

            return [
                'id' => 'chat-' . $chat->id,
                'type' => 'message',
                'icon' => 'message',
                'title' => $message->read_at === null
                    ? 'New Customer Message'
                    : 'Customer Message',
                'text' => ($chat->customer?->name ?? 'Customer')
                    . ': ' . $message->message,
                'time' => $message->created_at?->diffForHumans() ?? '',
                'unread' => $message->read_at === null,
                'chat' => true,
                'url' => route('admin.chat-inbox', [
                    'conversation' => $chat->id,
                ]),
                'created_at' => $createdAt,
                'sort_timestamp' => $createdAt?->timestamp ?? 0,
                'product_variant_id' => null,
            ];
        })->filter()->values();

        $conversationIds = $chats->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $storedNotifications = $stored
            ->reject(function ($item) use ($conversationIds) {
                $data = $item->data;
                $category = $data['category']
                    ?? $data['type']
                    ?? 'system';

                $conversationId = $data['conversation_id'] ?? null;

                return in_array(
                    $category,
                    ['message', 'messages'],
                    true
                ) && $conversationId !== null
                    && in_array(
                        (int) $conversationId,
                        $conversationIds,
                        true
                    );
            })
            ->map(function ($item) {
                $data = $item->data;
                $category = $data['category']
                    ?? $data['type']
                    ?? 'system';

                $type = match ($category) {
                    'order', 'orders' => 'order',
                    'message', 'messages' => 'message',
                    'stock' => 'stock',
                    default => 'system',
                };

                $variantId = isset($data['product_variant_id'])
                    ? (int) $data['product_variant_id']
                    : null;

                $url = $data['url'] ?? '#';

                if (
                    $type === 'stock'
                    && $variantId
                    && Route::has('admin.inventory.index')
                ) {
                    $url = route('admin.inventory.index', [
                        'search' => (string) $variantId,
                        'highlight_variant_id' => (string) $variantId,
                    ]);
                } elseif (
                    $type === 'message'
                    && isset($data['conversation_id'])
                    && Route::has('admin.chat-inbox')
                ) {
                    $url = route('admin.chat-inbox', [
                        'conversation' => $data['conversation_id'],
                    ]);
                }

                return [
                    'id' => (string) $item->id,
                    'type' => $type,
                    'icon' => $data['icon'] ?? $type,
                    'title' => $data['title'] ?? 'Notification',
                    'text' => $data['message'] ?? $data['text'] ?? '',
                    'time' => $item->created_at?->diffForHumans() ?? '',
                    'unread' => $item->read_at === null,
                    'chat' => false,
                    'url' => $url,
                    'created_at' => $item->created_at,
                    'sort_timestamp' => $item->created_at?->timestamp ?? 0,
                    'product_variant_id' => $variantId,
                ];
            });

        $notifications = $storedNotifications
            ->concat($chatNotifications)
            ->sortByDesc('sort_timestamp')
            ->values()
            ->map(function ($notification) {
                unset($notification['sort_timestamp']);

                return $notification;
            });

        $unreadCount = $notifications
            ->where('unread', true)
            ->count();

        if ($request->boolean('ajax')) {
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        return view(
            'admin.notifications.index',
            compact('notifications', 'unreadCount')
        );
    }

    public function readAll(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function read(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}
