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
        Schema::table('orders', function (Blueprint $table) {
            $table->index('invoice_number'); // Sangat penting untuk Midtrans
            $table->index('payment_status');
            $table->index('shipping_status');
        });

        // Index untuk tabel order_stores
        Schema::table('order_stores', function (Blueprint $table) {
            $table->index('store_id'); // Mempercepat dashboard seller
            $table->index('order_id');
            $table->index('shipping_status');
        });

        // Index untuk tabel order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_store_id'); // Mempercepat hitung pendapatan
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
