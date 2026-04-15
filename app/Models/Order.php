<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'store_id',
        'user_id',
        'total_price',
        'shipping_cost',
        'payment_status',
        'snap_token',
        'shipping_status',
        'tracking_number',
        'shipping_address',
        'courier',
        'received_at',
        'is_reviewed',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderStores()
    {
        return $this->hasMany(OrderStore::class);
    }
}
