<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the real order status transition timestamps.
     *
     * These are nullable because existing/historical orders do not have
     * reliable historical transition times and must not be backfilled
     * from created_at or updated_at.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')
                    ->nullable()
                    ->after('status');
            }

            if (! Schema::hasColumn('orders', 'processing_at')) {
                $table->timestamp('processing_at')
                    ->nullable()
                    ->after('confirmed_at');
            }

            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')
                    ->nullable()
                    ->after('processing_at');
            }

            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')
                    ->nullable()
                    ->after('shipped_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'confirmed_at',
                'processing_at',
                'shipped_at',
                'delivered_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
