<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->enum('currency', [
                'USD',
                'KHR',
            ]);

            $table->enum('payment_method', [
                'cash',
                'aba',
                'acleda',
                'wing',
                'chip_mong',
                'bank_transfer',
                'cash_on_delivery',
                'card',
                'bakong',
            ]);

            $table->string('payment_provider')
                ->nullable();

            $table->string('reference_number')
                ->nullable();

            $table->text('note')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};