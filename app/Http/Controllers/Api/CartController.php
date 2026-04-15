<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{

    public function index()
    {
        // Ambil data keranjang milik user yang login, sertakan data produk dan tokonya
        $cartItems = Cart::with(['product.store'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems
        ]);
    }
    public function store(Request $request)
    {
        // Pastikan pembeli sudah login
        if (!Auth::check()) {
            return redirect()->route('login'); // Arahkan ke halaman login jika belum
        }

        // Cek apakah produk sudah ada di keranjang user tersebut
        $cartItem = Cart::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            // Jika sudah ada, cukup tambahkan jumlahnya
            $cartItem->increment('quantity', $request->quantity ?? 1);
        } else {
            // Jika belum ada, buat baris baru
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1
            ]);
        }


        return back()->with('success', 'Produk berhasil masuk keranjang!');
    }

    public function update(Request $request, Cart $cart)
    {
        // Pastikan yang diubah adalah keranjang miliknya sendiri
        if ($cart->user_id === Auth::id()) {
            $request->validate(['quantity' => 'required|integer|min:1']);
            $cart->update(['quantity' => $request->quantity]);
        }
        return back();
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user_id === Auth::id()) {
            $cart->delete();
        }
        return back();
    }
}
