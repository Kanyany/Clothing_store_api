<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function conversation(Request $request): JsonResponse
    {
        $conversation = $this->getConversation($request);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $conversation = $this->getConversation($request);

        $messages = $conversation->messages()
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'mode' => $conversation->mode,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(
        Request $request,
        ChatbotService $bot
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = $this->getConversation($request);

        $result = DB::transaction(function () use (
            $conversation,
            $request,
            $validated,
            $bot
        ) {
            $conversation = ChatConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            $customerMessage = $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'sender_type' => 'customer',
                'message' => $validated['message'],
            ]);

            $conversation->update([
                'last_message_at' => now(),
            ]);

            if ($conversation->mode !== 'bot') {
                return [
                    'customer_message' => $customerMessage,
                    'bot_message' => null,
                    'mode' => $conversation->mode,
                ];
            }

            $reply = $bot->reply($validated['message']);

            if ($reply['type'] === 'handoff') {
                $conversation->update([
                    'mode' => 'waiting_admin',
                ]);
            }

            $botText = $reply['message'];

            // Product details are displayed as Flutter cards,
            // not as a long plain-text message.
            if ($reply['type'] === 'products') {
                $botText = 'Choose a category below to browse our products.';
            }

            $botMessage = $conversation->messages()->create([
                'sender_id' => null,
                'sender_type' => 'bot',
                'message' => $botText,
            ]);

            return [
                'customer_message' => $customerMessage,
                'bot_message' => $botMessage,
                'mode' => $conversation->fresh()->mode,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ], 201);
    }

    public function requestAdmin(Request $request): JsonResponse
    {
        $conversation = $this->getConversation($request);

        DB::transaction(function () use ($conversation) {
            $conversation = ChatConversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            if ($conversation->mode === 'bot') {
                $conversation->update([
                    'mode' => 'waiting_admin',
                    'last_message_at' => now(),
                ]);

                $conversation->messages()->create([
                    'sender_type' => 'bot',
                    'sender_id' => null,
                    'message' => 'Your request has been sent to our support team.',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Support request received.',
            'conversation' => $conversation->fresh(),
        ]);
    }

    private function getConversation(Request $request): ChatConversation
    {
        return ChatConversation::firstOrCreate(
            [
                'customer_id' => $request->user()->id,
                'status' => 'open',
            ],
            [
                'mode' => 'bot',
            ]
        );
    }
}
