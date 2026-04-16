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

        // 2. Buat Tabel Regencies
        Schema::create('regencies', function (Blueprint $table) {
            $table->char('id', 4)->primary();
            $table->char('province_id', 2)->index('regencies_province_id_index');
            $table->string('name', 255);

            $table->foreign('province_id', 'regencies_province_id_foreign')
                  ->references('id')->on('provinces')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        // 3. Buat Tabel Districts (Kecamatan + Postal Code)
        Schema::create('districts', function (Blueprint $table) {
            $table->char('id', 7)->primary();
            $table->char('regency_id', 4)->index('districts_id_index');
            $table->string('name', 255);
            
            // Kolom kustom Anda ada di sini
            $table->string('postal_code', 5)->nullable();

            $table->foreign('regency_id', 'districts_regency_id_foreign')
                  ->references('id')->on('regencies')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        // 4. Buat Tabel Villages
        Schema::create('villages', function (Blueprint $table) {
            $table->char('id', 10)->primary();
            $table->char('district_id', 7)->index('villages_district_id_index');
            $table->string('name', 255);

            $table->foreign('district_id', 'villages_district_id_foreign')
                  ->references('id')->on('districts')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
    }
};
