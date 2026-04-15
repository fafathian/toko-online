<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Kategori')->schema([
                    TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->live(onBlur: true) // Memicu update saat user selesai ngetik
                        ->afterStateUpdated(fn($operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                    TextInput::make('slug')
                        ->disabled()
                        ->dehydrated() // Pastikan tetap tersimpan di DB meski disabled
                        ->required()
                        ->unique(Category::class, 'slug', ignoreRecord: true),

                    TextInput::make('icon')
                        ->label('Icon (Opsional)')
                        ->placeholder('fa-solid fa-shirt')
                        ->maxLength(255),
                ])->columns(2)
            ]);
    }
}
