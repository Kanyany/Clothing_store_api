<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChatController extends Controller
{
    // List customer conversations
    public function index(): JsonResponse
    {
        $conversations = ChatConversation::query()
            ->with('customer:id,name,email')
            ->withCount('messages')
            ->orderByRaw(
                'CASE WHEN mode = ? THEN 0 ELSE 1 END',
                ['waiting_admin']
            )
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    // View messages in one conversation
    
    public function show(ChatConversation $conversation): JsonResponse
    {
        // Mark unread customer messages as read when admin opens the chat.
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $conversation->load([
            'customer:id,name,email',
            'assignedAdmin:id,name',
        ]);

        $messages = $conversation->messages()
            ->orderBy('id')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }


    // Assign conversation to the logged-in admin
    public function takeOver(
        Request $request,
        ChatConversation $conversation
    ): JsonResponse {
        $result = DB::transaction(function () use (
            $request,
            $conversation
        ) {
            $chat = ChatConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            if ($chat->status !== 'open') {
                return [
                    'error' => 'This conversation is closed.',
                    'status' => 409,
                ];
            }

            if (
                $chat->assigned_admin_id !== null &&
                (int) $chat->assigned_admin_id !==
                (int) $request->user()->id
            ) {
                return [
                    'error' => 'Another admin owns this conversation.',
                    'status' => 409,
                ];
            }

            $chat->update([
                'assigned_admin_id' => $request->user()->id,
                'mode' => 'admin',
            ]);

            return ['conversation' => $chat->fresh()];
        });

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'conversation' => $result['conversation'],
        ]);
    }

    // Send an admin reply
    public function reply(
        Request $request,
        ChatConversation $conversation
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $result = DB::transaction(function () use (
            $request,
            $conversation,
            $validated
        ) {
            $chat = ChatConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            if (
                $chat->status !== 'open' ||
                $chat->mode !== 'admin' ||
                (int) $chat->assigned_admin_id !==
                (int) $request->user()->id
            ) {
                return [
                    'error' => 'Take over this open conversation first.',
                    'status' => 409,
                ];
            }

            $message = $chat->messages()->create([
                'sender_id' => $request->user()->id,
                'sender_type' => 'admin',
                'message' => $validated['message'],
            ]);

            $chat->update([
                'last_message_at' => now(),
            ]);

            return ['message' => $message];
        });

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ], 201);
    }

    // Return conversation to chatbot
    public function returnToBot(
        Request $request,
        ChatConversation $conversation
    ): JsonResponse {
        $result = DB::transaction(function () use (
            $request,
            $conversation
        ) {
            $chat = ChatConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            if (
                $chat->status !== 'open' ||
                $chat->mode !== 'admin' ||
                (int) $chat->assigned_admin_id !==
                (int) $request->user()->id
            ) {
                return [
                    'error' => 'You must own this open conversation.',
                    'status' => 409,
                ];
            }

            $chat->update([
                'mode' => 'bot',
                'assigned_admin_id' => null,
            ]);

            return ['conversation' => $chat->fresh()];
        });

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'conversation' => $result['conversation'],
        ]);
    }

    // Close conversation
    public function close(
        Request $request,
        ChatConversation $conversation
    ): JsonResponse {
        $result = DB::transaction(function () use (
            $request,
            $conversation
        ) {
            $chat = ChatConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            if (
                $chat->assigned_admin_id !== null &&
                (int) $chat->assigned_admin_id !==
                (int) $request->user()->id
            ) {
                return [
                    'error' => 'Another admin owns this conversation.',
                    'status' => 409,
                ];
            }

            $chat->update([
                'status' => 'closed',
            ]);

            return ['conversation' => $chat->fresh()];
        });

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'conversation' => $result['conversation'],
        ]);
    }
}
