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
        Schema::table('order_payments', function (Blueprint $table) {

            $table->text('qr')
                ->nullable()
                ->after('payment_provider');

            $table->string('md5')
                ->nullable()
                ->index()
                ->after('qr');

            $table->string('transaction_hash')
                ->nullable()
                ->index()
                ->after('md5');

            $table->text('deeplink')
                ->nullable()
                ->after('transaction_hash');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('deeplink');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropColumn([
                'qr',
                'md5',
                'transaction_hash',
                'deeplink',
                'paid_at',
            ]);
        });
    }
};