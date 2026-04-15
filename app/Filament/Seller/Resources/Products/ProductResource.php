<?php

namespace App\Filament\Seller\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;

// --- PERBAIKAN NAMESPACE FILAMENT 5 ---

// 1. Komponen Schema (Bukan lagi Forms)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;

// 2. Komponen Kolom Tabel
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

// 3. Komponen Aksi Tabel (Wajib menggunakan awalan Tables)
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'produk Saya';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)->schema([ // Membagi layar jadi 2 kolom agar rapi
                    TextInput::make('name')
                        ->label('Nama Produk')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                    TextInput::make('slug')
                        ->label('URL Produk (Slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('price')
                        ->label('Harga')
                        ->required()
                        ->numeric()
                        ->prefix('Rp') // Menambahkan prefix Rupiah di form
                        ->minValue(0),

                    TextInput::make('stock')
                        ->label('Stok Barang')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    Select::make('condition')
                        ->label('Kondisi Barang')
                        ->options([
                            'Baru' => 'Baru',
                            'Bekas' => 'Bekas',
                        ])
                        ->default('Baru')
                        ->required(),

                    TextInput::make('sold_count')
                        ->label('Total Terjual')
                        ->numeric()
                        ->default(0)
                        ->disabled() // <--- KUNCI: Mengunci input agar tidak bisa diedit
                        ->dehydrated(false), // <--- Mencegah Filament menimpa data ini saat disave

                    TextInput::make('rating')
                        ->label('Rating Bintang')
                        ->numeric()
                        ->default(0.0)
                        ->disabled() // <--- KUNCI: Mengunci input
                        ->dehydrated(false),

                    TextInput::make('weight')
                        ->required()
                        ->numeric()
                        ->suffix('gram')
                        ->helperText('Contoh: 1000 untuk 1kg'),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->label('Kategori Produk')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

                RichEditor::make('description')
                    ->label('Deskripsi Produk')
                    ->columnSpanFull(), // Membuat deskripsi mengambil lebar penuh layar

                FileUpload::make('image_path')
                    ->label('Foto Produk')
                    ->image()
                    ->directory('products') // Foto akan otomatis disimpan di folder /storage/app/public/products
                    ->maxSize(2048) // Maksimal 2MB (Bagus untuk test case validasi error nantinya!)
                    ->columnSpanFull(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->square(), // Menampilkan foto dengan bentuk kotak

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable() // Agar muncul kotak pencarian
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id') // Otomatis format ke Rp. 10.000,00
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('average_rating')
                    ->label('Rating')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-star'),
            ])
            ->filters([
                // Bisa ditambahkan filter nanti
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
