<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    // Jika tabel kamu tidak pakai timestamps (created_at/updated_at), set false
    public $timestamps = false;

    protected $fillable = [
        'regency_id',
        'name',
        'postal_code', // Tambahkan ini
    ];

    /**
     * Relasi ke Kabupaten/Kota
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
