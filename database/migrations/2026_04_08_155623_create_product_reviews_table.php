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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Siapa yang mereview
            $table->foreignId('order_id')->constrained()->cascadeOnDelete(); // Bukti transaksi
            $table->tinyInteger('rating'); // Bintang 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Mencegah 1 user kasih 2 review di 1 produk pada pesanan yang sama
            $table->unique(['user_id', 'product_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
