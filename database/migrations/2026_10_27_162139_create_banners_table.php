
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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Banner content
            $table->string('title');
            $table->string('subtitle')->nullable();

            // Uploaded image path in public storage
            $table->string('image');

            // Optional destination when a customer taps the banner
            $table->string('link_type')->nullable();
            $table->unsignedBigInteger('link_id')->nullable();

            // Display controls
            $table->boolean('status')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
