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

            // Kita suruh Laravel mengecek dulu. Jika kolomnya belum ada, baru dibuatkan.
            // Ini mencegah error jika proses sebelumnya sempat berjalan setengah.

            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable();
            }

            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0);
            }

            if (!Schema::hasColumn('orders', 'courier')) {
                $table->string('courier')->nullable();
            }

            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable();
            }

            if (!Schema::hasColumn('orders', 'snap_token')) {
                $table->string('snap_token')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'courier', 'tracking_number', 'snap_token', 'shipping_address']);
        });
    }
};
