<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    // Tambahkan ini agar kolom 'name' boleh diisi massal
    protected $fillable = ['id', 'name'];

    // Karena kita pakai ID dari API (bukan auto increment murni), 
    // tambahkan ini jika ID-nya manual
    public $incrementing = false;
}
