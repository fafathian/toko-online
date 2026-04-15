<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Store;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set; // <--- Ini yang baru
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;


class RegisterStore extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Daftar Toko Baru';
    }

    // 2. SIGNATURE BARU: Parameter dan Return Type menggunakan Schema
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nama Toko')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label('URL Toko (Slug)')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'stores', column: 'slug'),

                Textarea::make('description')
                    ->label('Deskripsi Singkat')
                    ->maxLength(500),
            ]);
    }

    protected function handleRegistration(array $data): Store
    {
        // Menyimpan data toko
        $store = Store::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
        ]);

        // FIX: Cek apakah role 'seller' sudah ada. Jika belum, buatkan otomatis.
        $role = Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);

        // Berikan role tersebut ke user yang sedang login
        auth()->user()->assignRole($role);

        return $store;
    }
}
