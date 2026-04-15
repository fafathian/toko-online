<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    // POIN PENTING: Beritahu Laravel kalau tabelnya bernama 'regencies'
    protected $table = 'regencies';

    // Daftar kolom yang ada di file indonesia.sql kamu
    // Biasanya: id, province_id, name
    protected $fillable = ['id', 'province_id', 'name'];

    // Relasi ke Provinsi (Pastikan nama tabel provinsinya juga sesuai)
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }


}
