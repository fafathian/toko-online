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
        Schema::create('order_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('shipping_status')->default('processing');
            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('order_store_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_stores')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_store_id']);
            $table->dropColumn('order_store_id');
        });
        Schema::dropIfExists('order_stores');
    }
};
