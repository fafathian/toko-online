<?php

namespace App\Filament\Seller\Resources\Stores;

use App\Filament\Seller\Resources\Stores\Pages\CreateStore;
use App\Filament\Seller\Resources\Stores\Pages\EditStore;
use App\Filament\Seller\Resources\Stores\Pages\ListStores;
use App\Filament\Seller\Resources\Stores\Schemas\StoreForm;
use App\Filament\Seller\Resources\Stores\Tables\StoresTable;
use App\Models\Store;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\StoreResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'store';
    protected static ?string $navigationLabel = 'Toko Saya';
    protected static ?string $modelLabel = 'Toko';
    protected static ?string $pluralModelLabel = 'Daftar Toko';

    public static function form(Schema $schema): Schema
    {
        return StoreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStores::route('/'),
            'create' => CreateStore::route('/create'),
            'edit' => EditStore::route('/{record}/edit'),
        ];
    }

    // PENTING: Scoping agar Seller hanya bisa melihat tokonya sendiri!
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
