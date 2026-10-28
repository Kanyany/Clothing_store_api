
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();

            // Customer who owns this conversation
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Admin assigned to handle the conversation
            $table->foreignId('assigned_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Who is currently handling the conversation
            $table->enum('mode', [
                'bot',
                'waiting_admin',
                'admin'
            ])->default('bot');

            // Conversation status
            $table->enum('status', [
                'open',
                'closed'
            ])->default('open');

            // Last message timestamp
            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
