<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil produk beserta relasinya
        $query = Product::with(['store:id,name,slug', 'category:id,name,slug,icon'])
            ->where('stock', '>', 0);

        // 2. Filter Pencarian
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 3. Filter Toko (SUDAH DIPERCEPAT)
        if ($request->has('store') && $request->store != '') {
            $store = Store::where('slug', $request->store)->first();
            if ($store) {
                $query->where('store_id', $store->id);
            }
        }

        // 4. Filter Kategori (SUDAH DIPERCEPAT)
        if ($request->has('category') && $request->category != '') {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 5. Filter Urutan (Sort)
        $sort = $request->input('sort', 'latest');
        if ($sort === 'termurah') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'termahal') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        // 6. Paginate Data
        $products = $query->paginate(12)->withQueryString();

        // 7. Kirim ke Vue Inertia
        return Inertia::render('Catalog/Index', [
            'products'   => $products,
            'stores'     => Store::select('id', 'name', 'slug')->get(),
            // Cache DIMATIKAN selama proses development agar tidak error/hilang
            'categories' => Category::select('id', 'name', 'slug', 'icon')->get(),
            'filters' => [
                'search'   => $request->search ?? '',
                'store'    => $request->store ?? '',
                'category' => $request->category ?? '',
                'sort'     => $request->sort ?? 'latest', // KUNCI UTAMANYA DI SINI! Backend memaksa 'latest'
            ]
        ]);
    }

    public function show($slug)
    {
        $product = Product::with(['store:id,name,slug', 'category:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }
}
