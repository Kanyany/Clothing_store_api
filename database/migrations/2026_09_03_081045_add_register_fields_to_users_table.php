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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');

            $table->enum('gender', [
                'male',
                'female',
                'other',
            ])->nullable()->after('last_name');

            $table->string('phone')->nullable()->after('email');

            $table->string('city_province')
                ->nullable()
                ->after('phone');

            $table->boolean('terms_accepted')
                ->default(false)
                ->after('city_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'gender',
                'phone',
                'city_province',
                'terms_accepted',
            ]);
        });
    }
};