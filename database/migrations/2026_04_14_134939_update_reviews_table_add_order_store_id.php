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
        Schema::table('product_reviews', function (Blueprint $table) {
            // Tambahkan order_store_id
            $table->foreignId('order_store_id')->nullable()->constrained()->onDelete('cascade')->after('product_id');

            // Opsional: Jika di tabel lama ada order_id, kamu bisa menghapusnya
            // $table->dropColumn('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_store_id');
        });
    }
};
