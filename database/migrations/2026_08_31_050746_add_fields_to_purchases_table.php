<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('supplier_name')->nullable()->after('id');
            $table->date('purchase_date')->after('supplier_name');
            $table->decimal('total_amount', 12, 2)->default(0)->after('purchase_date');
            $table->enum('status', [
                'draft',
                'received',
                'cancelled',
            ])->default('draft')->after('total_amount');
            $table->text('note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_name',
                'purchase_date',
                'total_amount',
                'status',
                'note',
            ]);
        });
    }
};