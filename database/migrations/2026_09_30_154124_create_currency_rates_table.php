<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();

            $table->string('base_currency', 3)->default('USD');
            $table->string('target_currency', 3)->default('KHR');

            $table->decimal('rate', 12, 4);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'base_currency',
                'target_currency',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};