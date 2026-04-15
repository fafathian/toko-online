<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BiteshipService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.key');
        $this->baseUrl = 'https://api.biteship.com/v1';
    }

    public function getTracking($courierName, $trackingNumber)
    {
        $cacheKey = "tracking_{$courierName}_{$trackingNumber}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($courierName, $trackingNumber) {

            // Mapping lebih lengkap — handle semua format dari DB
            $courierMap = [
                'jne'       => 'jne',
                'sicepat'   => 'sicepat',
                'sicepa'    => 'sicepat',  // typo safety
                'j&t'       => 'j&t',
                'jnt'       => 'j&t',
                'anteraja'  => 'anteraja',
                'ninja'     => 'ninja',
                'pos'       => 'pos',
                'tiki'      => 'tiki',
                'lion'      => 'lion',
                'spx'       => 'spx',
                'shopee'    => 'spx',
            ];

            // Ambil kata pertama, lowercase — "JNE REG" → "jne", "SICEPAT HALU" → "sicepat"
            $firstWord = strtolower(explode(' ', trim($courierName))[0]);

            // Handle J&T khusus karena ada karakter &
            if (str_contains(strtolower($courierName), 'j&t') || str_contains(strtolower($courierName), 'jnt')) {
                $firstWord = 'j&t';
            }

            $mappedCourier = $courierMap[$firstWord] ?? $firstWord;

            \Log::info('Biteship Tracking Request', [
                'original_courier' => $courierName,
                'mapped_courier'   => $mappedCourier,
                'tracking_number'  => $trackingNumber,
            ]);

            // SPX tidak support API Biteship — redirect langsung
            if ($mappedCourier === 'spx') {
                return ['success' => false, 'is_spx' => true];
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->get("{$this->baseUrl}/trackings/{$trackingNumber}/couriers/{$mappedCourier}");

            $data = $response->json();

            \Log::info('Biteship Raw Response', [
                'http_status' => $response->status(),
                'body'        => $data,
            ]);

            if ($response->successful() && ($data['success'] ?? false)) {
                return [
                    'success' => true,
                    'history' => collect($data['history'] ?? [])->map(fn($h) => [
                        'note'       => $h['note'] ?? '-',
                        'updated_at' => $h['updated_at'] ?? '-',
                    ])->toArray(),
                    'status' => $data['status'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $data['error'] ?? 'Data tracking tidak ditemukan.',
            ];
        });
    }

    public function getRates($originPostalCode, $destinationPostalCode, $couriers, $items)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}/rates/couriers", [
            'origin_postal_code'      => $originPostalCode,
            'destination_postal_code' => $destinationPostalCode,
            'couriers'                => $couriers, // misal: 'jne,sicepat,jnt'
            'items'                   => $items, // Array berisi nama barang, berat, dll
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        // Tampilkan error jika API gagal (untuk debugging)
        throw new \Exception('Biteship Error: ' . $response->body());
    }

    // Data mock untuk demo/testing
    private function getMockTracking($courier, $waybill): array
    {
        return [
            'success' => true,
            'history' => [
                [
                    'note'       => 'PAKET TELAH DITERIMA DI [JAKARTA SELATAN]',
                    'updated_at' => now()->subDays(3)->toIso8601String(),
                ],
                [
                    'note'       => 'PAKET SEDANG DALAM PROSES SORTIR DI HUB [JAKARTA]',
                    'updated_at' => now()->subDays(2)->toIso8601String(),
                ],
                [
                    'note'       => 'PAKET DITERUSKAN KE KOTA TUJUAN',
                    'updated_at' => now()->subDays(1)->toIso8601String(),
                ],
                [
                    'note'       => 'PAKET SEDANG DALAM PROSES PENGIRIMAN KE ALAMAT ANDA',
                    'updated_at' => now()->subHours(4)->toIso8601String(),
                ],
            ],
            'status'  => 'on_process',
            'is_mock' => true, // flag untuk UI jika perlu
        ];
    }
}
