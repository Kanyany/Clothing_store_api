<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_provider')
                ->nullable()
                ->after('payment_method');
        });

        DB::statement("
            ALTER TABLE payments
            MODIFY payment_method ENUM('cash', 'bank', 'cod')
            NOT NULL DEFAULT 'cash'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE payments
            MODIFY payment_method ENUM('cash', 'card', 'qr')
            NOT NULL DEFAULT 'cash'
        ");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_provider');
        });
    }
};