<?php

// app/Models/ChatRoom.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChatMessage;

class ChatRoom extends Model
{
    protected $fillable = ['order_id', 'buyer_id', 'store_id', 'buyer_last_read', 'seller_last_read'];

    protected $casts = [
        'buyer_last_read'  => 'datetime',
        'seller_last_read' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    // Hitung pesan belum dibaca untuk buyer
    public function unreadForBuyer()
    {
        return $this->messages()
            ->where('user_id', '!=', $this->buyer_id)
            ->where('is_read', false)
            ->count();
    }

    // Hitung pesan belum dibaca untuk seller
    public function unreadForSeller()
    {
        return $this->messages()
            ->where('user_id', $this->buyer_id)
            ->where('is_read', false)
            ->count();
    }
}
