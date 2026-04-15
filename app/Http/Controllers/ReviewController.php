<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStore;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi sekarang mencari order_store_id
        $request->validate([
            'order_store_id'     => 'required|exists:order_stores,id',
            'items'              => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.rating'     => 'required|integer|min:1|max:5',
            'items.*.comment'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                ProductReview::create([
                    // KUNCI PERBAIKAN: Gunakan order_store_id untuk relasinya
                    'order_store_id' => $request->order_store_id,
                    'user_id'        => auth()->id(),
                    'product_id'     => $item['product_id'],
                    'rating'         => $item['rating'],
                    'comment'        => $item['comment'],
                ]);
            }

            // 2. Tandai HANYA pesanan dari toko ini yang is_reviewed = true
            OrderStore::where('id', $request->order_store_id)->update(['is_reviewed' => true]);
        });

        return back()->with('success', 'Terima kasih! Ulasan Anda untuk toko ini telah disimpan.');
    }
}
