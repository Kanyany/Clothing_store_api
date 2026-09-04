<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status')
                ->default('pending')
                ->after('user_id');

            $table->decimal('subtotal', 12, 2)
                ->default(0)
                ->after('status');

            $table->decimal('discount', 12, 2)
                ->default(0)
                ->after('subtotal');

            $table->decimal('total', 12, 2)
                ->default(0)
                ->after('discount');

            $table->string('payment_status')
                ->default('pending')
                ->after('total');

            $table->text('shipping_address')
                ->nullable()
                ->after('payment_status');

            $table->text('notes')
                ->nullable()
                ->after('shipping_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'user_id',
                'status',
                'subtotal',
                'discount',
                'total',
                'payment_status',
                'shipping_address',
                'notes',
            ]);
        });
    }
};