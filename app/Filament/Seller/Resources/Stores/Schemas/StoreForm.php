<?php

namespace App\Filament\Seller\Resources\Stores\Schemas;

use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\City;  // Sesuaikan dengan nama model kota/kabupatenmu
use App\Models\District; // Sesuaikan dengan nama model kecamatanmu
use App\Models\Province; // Sesuaikan dengan nama model provinsimu
use App\Models\Store;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar Toko')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Toko')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Store::class, 'slug', ignoreRecord: true),

                        Textarea::make('description')
                            ->label('Deskripsi Toko')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Pengaturan Pengiriman (Origin Biteship)')
                    ->description('Data ini wajib diisi agar sistem bisa menghitung ongkos kirim ke pembeli.')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat Lengkap Toko (Jalan, RT/RW, Patokan)')
                            ->required()
                            ->columnSpanFull(),

                        Select::make('province_id')
                            ->label('Provinsi')
                            ->options(Province::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            // PERUBAHAN DI SINI: Hapus tulisan 'Set'
                            ->afterStateUpdated(function ($set) {
                                $set('regency_id', null);
                                $set('district_id', null);
                            })
                            ->required(),

                        Select::make('regency_id')
                            ->label('Kota/Kabupaten')
                            // PERUBAHAN DI SINI: Hapus tulisan 'Get'
                            ->options(
                                fn($get): \Illuminate\Support\Collection => City::query()
                                    ->where('province_id', $get('province_id'))
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            // PERUBAHAN DI SINI: Hapus tulisan 'Set'
                            ->afterStateUpdated(fn($set) => $set('district_id', null))
                            ->required(),

                        Select::make('district_id')
                            ->label('Kecamatan')
                            // PERUBAHAN DI SINI: Hapus tulisan 'Get'
                            ->options(
                                fn($get): \Illuminate\Support\Collection => District::query()
                                    ->where('regency_id', $get('regency_id'))
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('postal_code')
                            ->label('Kode Pos')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
