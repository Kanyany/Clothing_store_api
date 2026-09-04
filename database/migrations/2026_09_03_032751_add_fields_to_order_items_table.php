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
        Schema::table('order_items', function (Blueprint $table) {

            $table->foreignId('order_id')
                ->after('id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->after('order_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity')
                ->default(1)
                ->after('product_variant_id');

            $table->decimal('unit_price', 12, 2)
                ->default(0)
                ->after('quantity');

            $table->decimal('subtotal', 12, 2)
                ->default(0)
                ->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->dropForeign(['order_id']);
            $table->dropForeign(['product_variant_id']);

            $table->dropColumn([
                'order_id',
                'product_variant_id',
                'quantity',
                'unit_price',
                'subtotal',
            ]);
        });
    }
};