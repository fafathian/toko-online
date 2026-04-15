<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil produk yang stoknya ada, dan eager-load relasi toko & kategori
        $query = Product::with([
            'store:id,name,slug',
            'category:id,name,slug,icon'
        ])->where('stock', '>', 0);

        // 2. Logika Filter Pencarian Kata Kunci
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 3. Logika Filter Toko
        if ($request->store) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('slug', $request->store);
            });
        }

        // 4. Logika Filter Kategori (Dari tombol Pill yang baru dibuat)
        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // 5. Logika Filter Urutan (Sort)
        if ($request->sort == 'termurah') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'termahal') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest(); // Default: Paling Baru
        }

        // 6. Paginate data dengan membawa query string filter ke halaman selanjutnya
        $products = $query->paginate(12)->withQueryString();

        // 7. Penting: Append rating rata-rata agar ikut terkirim ke Vue
        $products->getCollection()->transform(function ($product) {
            $product->append(['average_rating']);
            return $product;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar Katalog Produk',
            'data'    => $products
        ]);
    }

    public function show($slug)
    {
        // Mencari produk spesifik berdasarkan slug URL-nya
        $product = Product::with('store:id,name,slug')->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail Produk',
            'data'    => $product
        ]);
    }
}
