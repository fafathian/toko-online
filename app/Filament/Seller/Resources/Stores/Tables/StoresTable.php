<?php

namespace App\Filament\Seller\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Jml Produk')
                    ->badge(),

                TextColumn::make('average_rating')
                    ->label('Rating')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-s-star')
                    // Format angka jadi misal: 4.5
                    ->formatStateUsing(fn($state) => $state > 0 ? $state : 'Belum ada'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
