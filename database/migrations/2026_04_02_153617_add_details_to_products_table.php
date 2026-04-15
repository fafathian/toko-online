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
        Schema::table('products', function (Blueprint $table) {
            // Menambah 3 kolom baru setelah kolom 'stock'
            $table->string('condition')->default('Baru')->after('stock'); // Baru / Bekas
            $table->integer('sold_count')->default(0)->after('condition'); // Total terjual
            $table->decimal('rating', 2, 1)->default(0.0)->after('sold_count'); // Rating 0.0 sampai 5.0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['condition', 'sold_count', 'rating']);
        });
    }
};
