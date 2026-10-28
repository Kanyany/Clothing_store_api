<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('purchase_id')
                ->after('id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->after('purchase_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->integer('quantity')
                ->after('product_variant_id');

            $table->decimal('cost_price', 12, 2)
                ->after('quantity');

            $table->decimal('subtotal', 12, 2)
                ->after('cost_price');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropForeign(['product_variant_id']);

            $table->dropColumn([
                'purchase_id',
                'product_variant_id',
                'quantity',
                'cost_price',
                'subtotal',
            ]);
        });
    }
};