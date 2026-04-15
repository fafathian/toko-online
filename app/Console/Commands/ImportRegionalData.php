<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\City;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:regional-data')]
#[Description('Import wilayah via Komerce Domestic Destination Endpoint')]
class ImportRegionalData extends Command
{
    public function handle()
    {
        $apiKey = env('KOMERCE_API_KEY');

        if (!$apiKey) {
            $this->error('KOMERCE_API_KEY belum diset di .env!');
            return;
        }

        $this->info('Mengambil data wilayah dari Komerce (Domestic Destination)...');

        // Sesuai dokumentasi baru: destination/domestic-destination
        $response = Http::withHeaders(['key' => env('KOMERCE_API_KEY')])
            ->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=jakarta');

        if ($response->failed()) {
            $this->error('Gagal akses endpoint: ' . $response->body());
            return;
        }

        $results = $response->json()['data'] ?? [];

        if (empty($results)) {
            $this->error('Data kosong! Cek API Key atau URL.');
            return;
        }

        $this->info('Memproses ' . count($results) . ' data wilayah...');

        foreach ($results as $item) {
            // 1. Simpan/Cari Provinsi berdasarkan Nama
            // Kita simpan ke variabel $province
            $province = Province::updateOrCreate(
                ['name' => $item['province_name']], // Key pencarian
                ['name' => $item['province_name']]  // Data yang diupdate
            );

            // QA Check: Pastikan $province->id tidak null
            if ($province && $province->id) {
                // 2. Simpan Kota dengan province_id yang sudah didapat
                City::updateOrCreate(
                    [
                        'name' => $item['city_name'],
                        'province_id' => $province->id
                    ],
                    [
                        'province_id' => $province->id,
                        'name'        => $item['city_name'],
                        'type'        => $item['city_type'] ?? 'Kota/Kabupaten',
                        'postal_code' => $item['zip_code'] ?? '00000',
                    ]
                );
            } else {
                $this->warn("Skip data: Provinsi {$item['province_name']} gagal disimpan.");
            }
        }
        $this->info('Selesai! Database Z-STORE sekarang sudah punya data wilayah rill.');
    }
}
