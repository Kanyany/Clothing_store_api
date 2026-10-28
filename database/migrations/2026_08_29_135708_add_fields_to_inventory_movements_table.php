<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                ->after('id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->enum('type', [
                'in',
                'out',
                'adjustment',
            ])->after('product_variant_id');

            $table->integer('quantity')->after('type');

            $table->string('reference_type')->nullable()->after('quantity');

            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');

            $table->text('note')->nullable()->after('reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);

            $table->dropColumn([
                'product_variant_id',
                'type',
                'quantity',
                'reference_type',
                'reference_id',
                'note',
            ]);
        });
    }
};