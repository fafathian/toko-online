<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Models\Order;
use App\Models\OrderStore;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Index', [
        // Hanya ambil 8 produk terbaru sebagai etalase depan
        'latestProducts' => \App\Models\Product::with('store')->where('stock', '>', 0)->latest()->take(8)->get()
    ]);
})->name('home');

Route::get('/order/success/{id}', [OrderController::class, 'success'])->name('order.success');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('products.show');

// Halaman Detail Produk
Route::get('/products/{slug}', function ($slug) {
    $product = \App\Models\Product::with('store:id,name,slug')
        ->where('slug', $slug)
        ->firstOrFail();

    return Inertia::render('Product/Show', [
        'product' => $product
    ]);
});

// Halaman Profil Toko
Route::get('/store/{slug}', function ($slug) {
    $store = \App\Models\Store::where('slug', $slug)->firstOrFail();
    $products = \App\Models\Product::where('store_id', $store->id)
        ->where('stock', '>', 0)
        ->latest()
        ->paginate(12);

    return Inertia::render('Store/Show', [
        'store' => $store,
        'products' => $products
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    // Nanti rute Checkout akan kita letakkan di sini juga
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{orderStore}/print-resi', [OrderController::class, 'printResi'])
        ->name('orders.print-resi')
        ->middleware(['auth']);
    Route::get('/payment/finished', function () {
        $order = Order::where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($order && $order->payment_status === 'paid') {
            return redirect()->route('order.success', $order->id);
        }

        return redirect()->route('order.index')->with('message', 'Pembayaran sedang diproses.');
    })->name('payment.finished');

    Route::post('/checkout/shipping-cost', [CheckoutController::class, 'calculateShipping'])->name('checkout.shipping');
    Route::post('/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])
        ->name('checkout.calculateShipping');
    Route::get('/api/cities/{province_id}', [CheckoutController::class, 'getCities']);
    // Tambahkan di routes/web.php
    Route::get('/api/districts/{regency_id}', [App\Http\Controllers\CheckoutController::class, 'getDistricts']);


    Route::get('/api/order-stores/{orderStore}/tracking', function (
        OrderStore $orderStore,
        BiteshipService $service
    ) {
        if ($orderStore->order->user_id !== auth()->id()) {
            abort(403);
        }
        return $service->getTracking($orderStore->courier, $orderStore->tracking_number);
    })->middleware(['auth']);

    Route::post('/orders/store/{orderStore}/receive', [OrderController::class, 'markStoreAsReceived'])
        ->middleware(['auth'])
        ->name('orders.store.receive');


    //chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{room}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/room/create', [ChatController::class, 'createRoom'])->name('chat.create-room');
    Route::post('/chat/{room}/send', [ChatController::class, 'sendMessage'])->name('chat.send');


    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
});

// Bypass Gambar (Solusi Windows)
Route::get('/storage/{path}', function ($path) {
    $pathPublic = storage_path('app/public/' . $path);
    $pathPrivate = storage_path('app/private/' . $path);
    $pathLocal = storage_path('app/' . $path);

    if (File::exists($pathPublic)) {
        $fullPath = $pathPublic;
    } elseif (File::exists($pathPrivate)) {
        $fullPath = $pathPrivate;
    } elseif (File::exists($pathLocal)) {
        $fullPath = $pathLocal;
    } else {
        abort(404);
    }

    $file = File::get($fullPath);
    $type = File::mimeType($fullPath);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
})->where('path', '.*');


// ==========================================
// 2. RUTE BAWAAN BREEZE (JANGAN DIHAPUS)
// ==========================================

// Halaman Dashboard (Setelah Login)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Manajemen Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Mengimpor rute Login, Register, Logout, dll.
require __DIR__ . '/auth.php';
