<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'order_store_id',
        'rating',
        'comment'
    ];

    // Relasi: Review ini milik 1 Produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi: Review ini ditulis oleh 1 User (Pembeli)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Review ini berdasarkan 1 Order transaksi
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderStore()
    {
        return $this->belongsTo(OrderStore::class);
    }
}
