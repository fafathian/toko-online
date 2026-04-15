<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStore;
use App\Services\BiteshipService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
            'items.product.store',  // ← array terpisah, bukan 1 string
            'orderStores.store',    // ← ini juga terpisah
        ])
            ->where('user_id', auth()->id())
            ->whereIn('payment_status', ['pending', 'paid', 'shipped', 'completed', 'failed'])
            ->latest()
            ->get();

        return Inertia::render('Order/Index', [
            'orders' => $orders,
        ]);
    }

    public function show($id, BiteshipService $biteship) // Inject service ke sini
    {
        $order = Order::with(['items.product.store'])->findOrFail($id);

        // Security Check (Sudah oke!)
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $trackingData = null;

        // QA Logic: Hanya panggil API jika sudah ada nomor resi & kurir
        // Kita gunakan try-catch agar jika API Biteship error, halaman detail order tetap bisa terbuka
        if ($order->tracking_number && $order->courier) {
            try {
                $trackingData = $biteship->getTracking($order->courier, $order->tracking_number);
            } catch (\Exception $e) {
                // Log error untuk kebutuhan debugging QA
                \Log::error("Gagal mengambil tracking Biteship: " . $e->getMessage());
                $trackingData = ['error' => 'Gagal memuat data pelacakan.'];
            }
        }

        return Inertia::render('Order/Show', [
            'order' => $order,
            'tracking' => $trackingData // Data ini akan masuk ke Props di Vue
        ]);
    }

    public function success($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        // Pastikan hanya pemilik order yang bisa lihat
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Order/Success', [
            'order' => $order
        ]);
    }

    public function printResi(OrderStore $orderStore)
    {
        // 1. Pastikan semua data siap (Benar-benar Load agar tidak lambat saat generate)
        $orderStore->load(['order.user', 'store', 'items.product']);

        // 2. Load view yang sudah kita buat di folder views/pdf
        $pdf = Pdf::loadView('pdf.shipping-label', compact('orderStore'));

        /**
         * 3. SET UKURAN KERTAS (PENTING!)
         * [0, 0, 283.46, 425.20] = Kurang lebih 10cm x 15cm (Ukuran standar label kurir/A6)
         * Ini membuat resi langsung PAS saat dicetak ke printer Thermal.
         */
        $pdf->setPaper([0, 0, 283.46, 425.20], 'portrait');

        // 4. Stream agar langsung terbuka di tab baru browser
        // KUNCI PERBAIKAN: Ambil invoice_number dari tabel order
        $namaFile = 'Resi-' . ($orderStore->order->invoice_number ?? 'Unknown') . '.pdf';

        return $pdf->stream($namaFile);
    }

    public function markStoreAsReceived(OrderStore $orderStore)
    {
        // 1. Ambil data pesanan utama untuk cek keamanan (harus milik user yang login)
        $order = $orderStore->order;

        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Cek status khusus toko ini
        if ($orderStore->shipping_status === 'completed') {
            return response()->json(['message' => 'Pesanan dari toko ini sudah dikonfirmasi.'], 422);
        }

        // 3. Update status HANYA untuk toko ini
        $orderStore->update([
            'shipping_status' => 'completed',
            'received_at'     => now(),
        ]);

        // 4. KUNCI MULTI-VENDOR: Cek apakah masih ada toko lain yang belum selesai?
        $masihAdaYangBelumSelesai = $order->orderStores()
            ->where('shipping_status', '!=', 'completed')
            ->exists();

        // Jika semua toko sudah 'completed', baru kita update status pesanan utamanya
        if (!$masihAdaYangBelumSelesai) {
            $order->update([
                'shipping_status' => 'completed',
                'received_at'     => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan dari toko ' . ($orderStore->store->name ?? '') . ' berhasil diterima!'
        ]);
    }
}
