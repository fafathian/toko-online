<?php
// app/Models/OrderStore.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStore extends Model
{
    protected $fillable = [
        'order_id',
        'store_id',
        'shipping_status',
        'courier',
        'tracking_number',
        'received_at',
        'is_reviewed',
    ];

    protected $casts = ['received_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relasi ke toko (Untuk ambil nama pengirim)
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    // Relasi ke daftar barang (Untuk melist barang yang dibeli)
    public function items()
    {
        // Pastikan foreign key-nya benar: 'order_store_id'
        return $this->hasMany(OrderItem::class, 'order_store_id');
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function user()
    {
        if ($this->order) {
            return $this->order->user();
        }

        return null;
    }

    protected static function booted()
    {
        // Fungsi ini akan otomatis berjalan SETELAH data order_stores berhasil disave
        static::saved(function ($orderStore) {

            // Mengecek apakah kolom shipping_status atau tracking_number baru saja diubah
            if ($orderStore->isDirty('shipping_status') || $orderStore->isDirty('tracking_number')) {

                // Ambil data Induk (tabel orders)
                $order = $orderStore->order;

                if ($order) {
                    // Update tabel orders agar sama dengan data di order_stores
                    $order->update([
                        'shipping_status' => $orderStore->shipping_status,
                        'tracking_number' => $orderStore->tracking_number,
                    ]);
                }
            }
        });
    }
}
