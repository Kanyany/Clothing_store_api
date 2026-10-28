<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'unisex',
                'kids',
            ])->default('unisex');

            $table->string('brand')->nullable();

            // Default / main product image
            $table->string('image')->nullable();

            // One image for each product color. Shared by all sizes of that color.
            $table->json('color_images')->nullable();

            // Product detail gallery images shown in Flutter.
            $table->json('detail_images')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
