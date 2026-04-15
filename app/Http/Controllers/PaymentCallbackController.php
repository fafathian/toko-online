<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentCallbackController extends Controller
{
    public function receive(Request $request)
    {
        // Ambil data dari request body (Midtrans mengirim JSON)
        $orderId = $request->input('order_id');
        $status  = $request->input('transaction_status');

        Log::info("Webhook Masuk - Invoice: $orderId, Status: $status");

        try {
            // 1. Cari order. Gunakan where dan pastikan kolomnya benar.
            // Gunakan firstOrFail supaya jika tidak ketemu langsung masuk ke catch block
            $order = Order::where('invoice_number', $orderId)->first();

            if (!$order) {
                Log::error("Gagal Update: Invoice $orderId tidak ditemukan di database!");
                return response()->json(['message' => 'Order not found'], 404);
            }

            DB::transaction(function () use ($order, $status) {

                if ($status == 'settlement' || $status == 'capture') {

                    // UPDATE TABEL ORDERS (UTAMA)
                    // Pastikan payment_status dan shipping_status ada di $fillable di model Order.php
                    $order->update([
                        'payment_status'  => 'paid',
                        'shipping_status' => 'processing'
                    ]);

                    // UPDATE TABEL ORDER_STORES
                    $order->orderStores()->update([
                        'shipping_status' => 'processing'
                    ]);

                    // UPDATE STOK DAN SOLD COUNT
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->decrement('stock', $item->quantity);
                            $item->product->increment('sold_count', $item->quantity);
                        }
                    }

                    Log::info("Tabel orders & order_stores berhasil diupdate untuk: " . $order->invoice_number);
                } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
                    $order->update(['payment_status' => 'failed']);
                }
            });

            return response()->json(['message' => 'Success']);
        } catch (\Exception $e) {
            Log::error('Callback Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }
}
