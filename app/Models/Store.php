<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'address',
        'province_id',
        'regency_id',
        'district_id',
        'postal_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(ProductReview::class, Product::class);
    }

    // Menghitung rata-rata bintang toko secara otomatis
    public function getAverageRatingAttribute()
    {
        // PERHATIKAN: Tambahkan 'product_reviews.' di depan 'rating'
        return round($this->reviews()->avg('product_reviews.rating') ?? 0, 1);
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }
}
