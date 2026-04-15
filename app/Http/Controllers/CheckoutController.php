<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product.store')->where('user_id', Auth::id())->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart');
        }

        // Kelompokkan barang berdasarkan toko
        $groupedCart = [];
        $totalBelanja = 0;

        foreach ($cartItems->groupBy(fn($item) => $item->product->store_id) as $storeId => $items) {
            $store = $items->first()->product->store;
            $subtotalToko = $items->sum(fn($item) => $item->product->price * $item->quantity);
            $beratTotalToko = $items->sum(fn($item) => ($item->product->weight ?? 1000) * $item->quantity);

            $totalBelanja += $subtotalToko;

            $groupedCart[] = [
                'store_id'     => $store->id,
                'store_name'   => $store->name,
                // Pastikan tabel stores punya kolom postal_code. 
                // Jika kosong, kita pakai 16424 (Depok) sebagai default origin sementara.
                'origin_pos'   => $store->postal_code ?? '16424',
                'items'        => $items,
                'total_weight' => $beratTotalToko,
                'subtotal'     => $subtotalToko,
            ];
        }

        $provinces = \App\Models\Province::all();

        return Inertia::render('Checkout/Index', [
            'checkoutData' => $groupedCart, // Lempar data yang sudah dikelompokkan
            'totalBelanja' => $totalBelanja,
            'provinces'    => $provinces
        ]);
    }

    // 2. PROSES PEMBUATAN PESANAN (Ubah Keranjang -> Order)
    public function store(Request $request)
    {
        // Format validasi baru: shippings adalah array dari tiap toko
        $request->validate([
            'shipping_address' => 'required|string|min:10',
            'shippings'        => 'required|array', // Contoh isi: [ 1 => ['courier' => 'jne', 'cost' => 20000], 2 => [...] ]
        ]);

        $cartItems = Cart::with('product.store')->where('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) return redirect('/cart');

        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        // Hitung total ongkir gabungan dari semua toko untuk Midtrans
        $totalShippingCost = 0;
        foreach ($request->shippings as $storeId => $shipping) {
            $totalShippingCost += $shipping['cost'];
        }

        // Buat order utama
        $order = Order::create([
            'user_id'          => Auth::id(),
            'invoice_number'   => 'INV-' . strtoupper(Str::random(10)),
            'total_price'      => $subtotal + $totalShippingCost, // Harga Barang + Total Semua Ongkir
            'shipping_cost'    => $totalShippingCost,
            'shipping_address' => $request->shipping_address,
            'courier'          => 'Multi-Kurir', // Karena bisa beda-beda per toko
            'payment_status'   => 'pending',
            'shipping_status'  => 'pending',
        ]);

        // Buat OrderStore dan pecah kurirnya!
        $cartItems->groupBy(fn($item) => $item->product->store_id)
            ->each(function ($items, $storeId) use ($order, $request) {
                // Ambil data kurir spesifik untuk toko ini
                $shippingData = $request->shippings[$storeId] ?? ['courier' => '-', 'cost' => 0];

                $orderStore = \App\Models\OrderStore::create([
                    'order_id'        => $order->id,
                    'store_id'        => $storeId,
                    'courier'         => $shippingData['courier'], // Simpan kurir JNE di toko A, SiCepat di toko B
                    'shipping_cost'   => $shippingData['cost'],    // Opsional: Jika kamu punya kolom ini di table order_stores
                    'shipping_status' => 'processing',
                ]);

                foreach ($items as $item) {
                    \App\Models\OrderItem::create([
                        'order_id'       => $order->id,
                        'order_store_id' => $orderStore->id,
                        'product_id'     => $item->product_id,
                        'quantity'       => $item->quantity,
                        'unit_price'     => $item->product->price,
                    ]);
                }
            });

        // Midtrans (Kodenya tetap sama seperti milikmu)
        Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized  = env('MIDTRANS_IS_SANITIZED', true);
        Config::$is3ds        = env('MIDTRANS_IS_3DS', true);

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id'     => $order->invoice_number,
                'gross_amount' => $order->total_price,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email'      => Auth::user()->email,
            ],
        ]);

        $order->update(['snap_token' => $snapToken]);
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat!');
    }

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'postal_code' => 'required|numeric',
            'store_id'    => 'required|exists:stores,id' // Wajib tahu ini untuk toko mana
        ]);

        $store = \App\Models\Store::find($request->store_id);
        $originPostalCode = $store->postal_code ?? '16424';

        // AMBIL DATA KERANJANG HANYA UNTUK TOKO INI
        $cartItems = \App\Models\Cart::with('product')
            ->where('user_id', auth()->id())
            ->whereHas('product', fn($q) => $q->where('store_id', $request->store_id))
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Keranjang kosong untuk toko ini'], 422);
        }

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'name'     => $item->product->name,
                'value'    => (int) $item->product->price,
                'weight'   => (int) ($item->product->weight ?? 1000),
                'quantity' => (int) $item->quantity,
            ];
        }

        try {
            // Menggunakan BiteshipService yang sudah kamu buat
            $biteship = new \App\Services\BiteshipService();
            $results = $biteship->getRates(
                $originPostalCode,
                (int) $request->postal_code,
                'jne,sicepat,jnt,pos,tiki',
                $items
            );

            return response()->json([
                'success' => true,
                'data'    => $results['pricing'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function getCities($province_id)
    {
        // Pastikan Model City sudah punya: protected $table = 'regencies';
        $cities = \App\Models\City::where('province_id', $province_id)->get();

        return response()->json($cities);
    }

    public function getDistricts($regency_id)
    {
        // Mengambil data dari tabel districts berdasarkan regency_id (id kota)
        $districts = \DB::table('districts')->where('regency_id', $regency_id)->get();

        return response()->json($districts);
    }
}
