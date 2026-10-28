<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // parent_id is already created in create_categories_table.
        // Nothing to add here.
    }

    public function down(): void
    {
        // parent_id belongs to the original categories table migration.
        // Do not remove it here.
    }
};