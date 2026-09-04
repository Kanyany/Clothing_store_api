<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('sale_id')
                ->after('id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2)
                ->after('sale_id');

            $table->enum('payment_method', [
                'cash',
                'card',
                'qr',
            ])->after('amount');

            $table->string('reference_number')
                ->nullable()
                ->after('payment_method');

            $table->text('note')
                ->nullable()
                ->after('reference_number');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);

            $table->dropColumn([
                'sale_id',
                'amount',
                'payment_method',
                'reference_number',
                'note',
            ]);
        });
    }
};