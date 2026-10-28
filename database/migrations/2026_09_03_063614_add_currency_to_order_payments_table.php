<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            // currency already exists in the original order_payments table.

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
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cash',
                'aba',
                'acleda',
                'wing',
                'chip_mong',
                'bank_transfer',
                'cash_on_delivery',
                'card',
            ])->change();
        });
    }
};