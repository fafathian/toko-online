<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->label('Nama Pembeli')
                    ->required()
                    // Mengambil nama user melalui relasi di Model Order
                    ->formatStateUsing(fn($record) => $record->user?->name)
                    ->disabled(), // Pastikan di-disable agar tidak bisa diubah
                TextInput::make('total_price')
                    ->label('Total Harga')
                    ->prefix('Rp')
                    ->required()
                    // 1. Tampilkan dengan format ribuan (.) dan dua nol desimal (,)
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2, ',', '.') : '0,00')

                    // 2. Sebelum disimpan ke database, buang semua karakter non-angka
                    // Ini memastikan 120.000,00 kembali jadi 120000 di database
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^0-9]/', '', strstr($state, ',', true) ?: $state))

                    // 3. Tambahkan masking di sisi browser agar user tidak bisa input huruf
                    ->extraInputAttributes([
                        'onkeyup' => "
            let val = this.value.replace(/\D/g, '');
            val = new Intl.NumberFormat('id-ID').format(val);
            this.value = val;
        "
                    ]),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('shipping_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('tracking_number'),
            ]);
    }
}
