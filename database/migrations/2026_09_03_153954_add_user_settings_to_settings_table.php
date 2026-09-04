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
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->unique()
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->boolean('dark_mode')
                ->default(false)
                ->after('user_id');

            $table->boolean('notifications')
                ->default(true)
                ->after('dark_mode');

            $table->string('language')
                ->default('Khmer')
                ->after('notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'dark_mode',
                'notifications',
                'language',
            ]);
        });
    }
};