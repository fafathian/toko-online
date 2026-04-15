<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image_path',
        'condition',
        'sold_count',
        'rating',
        'category_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    // Rating rata-rata per produk
    public function getAverageRatingAttribute()
    {
        // PERHATIKAN: Tambahkan 'product_reviews.' di sini juga
        return round($this->reviews()->avg('product_reviews.rating') ?? 0, 1);
    }
}
