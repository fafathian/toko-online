<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    // 1. WAJIB: Izinkan Laravel mengisi kolom-kolom ini ke database
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    // 2. Relasi: Satu item keranjang milik satu Pembeli (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 3. Relasi: Satu item keranjang berisi satu Produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
