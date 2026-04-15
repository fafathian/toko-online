<?php

namespace App\Filament\Seller\Resources\Orders;

use App\Filament\Seller\Resources\Orders\Pages\CreateOrder;
use App\Filament\Seller\Resources\Orders\Pages\EditOrder;
use App\Filament\Seller\Resources\Orders\Pages\ListOrders;
use App\Filament\Seller\Resources\Orders\Schemas\OrderForm;
use App\Filament\Seller\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\OrderStore;
use App\Models\Store;
use App\Models\OrderItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;


// 2. Komponen Kolom Tabel
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\ImageColumn;
use Filament\Infolists\Components\TextEntry;

// 3. Komponen Aksi Tabel (Wajib menggunakan awalan Tables)
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class OrderResource extends Resource
{
    protected static ?string $model = OrderStore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'orderan ku';

    protected static ?string $navigationLabel  = 'Pesanan Masuk';
    protected static ?string $pluralModelLabel = 'Pesanan Masuk';

    public static function getEloquentQuery(): Builder
    {
        $userId = Auth::id();

        // Ambil SEMUA store milik user ini
        $storeIds = Store::where('user_id', $userId)->pluck('id');

        if ($storeIds->isEmpty()) {
            return OrderStore::query()->where('id', 0);
        }

        return OrderStore::query()
            ->with(['order.user', 'items', 'store'])
            ->whereIn('store_id', $storeIds) // ← whereIn bukan where
            ->latest();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Detail Pengiriman')
                ->schema([
                    Select::make('shipping_status')
                        ->label('Status Pesanan')
                        ->options([
                            'processing' => 'Sedang Diproses (Dikemas)',
                            'shipped'    => 'Sedang Dikirim',
                            'completed'  => 'Selesai',
                            'cancelled'  => 'Dibatalkan',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    TextInput::make('tracking_number')
                        ->label('Nomor Resi Pengiriman')
                        ->placeholder('Masukkan nomor resi')
                        ->required(fn(Get $get): bool => $get('shipping_status') === 'shipped')
                        ->maxLength(255),
                ])->columns(2),

            Section::make('Informasi Pembeli')
                ->schema([
                    // Kurir sekarang bisa disimpan ke order_stores
                    TextInput::make('courier')
                        ->label('Kurir Pengiriman')
                        ->placeholder('Misal: JNE, SiCepat, J&T')
                        ->required(),
                    // HAPUS disabled() dan dehydrated(false) agar bisa disave!

                    TextInput::make('buyer_name')
                        ->label('Nama Pembeli')
                        ->disabled()
                        ->dehydrated(false) // Nama pembeli biarkan tidak disave ke order_stores (karena ini milik user)
                        ->afterStateHydrated(function ($component, OrderStore $record) {
                            $component->state($record->order?->user?->name ?? '-');
                        }),

                    Textarea::make('shipping_address_display')
                        ->label('Alamat Tujuan')
                        ->disabled()
                        ->dehydrated(false) // Alamat juga biarkan saja sebagai info
                        ->rows(3)
                        ->afterStateHydrated(function ($component, OrderStore $record) {
                            $component->state($record->order?->shipping_address ?? '-');
                        }),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('order.user.name')
                    ->label('Nama Pembeli')
                    ->searchable(),

                TextColumn::make('items_total')
                    ->label('Total Tagihan Toko Ini')
                    ->getStateUsing(function (OrderStore $record) {
                        // Coba dari relasi items dulu
                        $total = \App\Models\OrderItem::where('order_store_id', $record->id)
                            ->selectRaw('SUM(unit_price * quantity) as total')
                            ->value('total');

                        // Fallback: kalau masih 0, hitung dari produk store ini di order ini
                        if (!$total) {
                            $total = \App\Models\OrderItem::where('order_id', $record->order_id)
                                ->whereHas('product', fn($q) => $q->where('store_id', $record->store_id))
                                ->selectRaw('SUM(unit_price * quantity) as total')
                                ->value('total');
                        }

                        return  'Rp ' . number_format($total ?? 0, 2, ',', '.');
                    }),

                TextColumn::make('shipping_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'completed'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                        default      => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('received_at')
                    ->label('Diterima Pada')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('Belum diterima')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('completed')
                    ->label('Sudah Diterima')
                    ->query(fn($query) => $query->whereNotNull('received_at')),

                // Filter per toko
                \Filament\Tables\Filters\SelectFilter::make('store_id')
                    ->label('Filter Toko')
                    ->options(function () {
                        return Store::where('user_id', Auth::id())
                            ->pluck('name', 'id');
                    })
                    ->attribute('store_id'),
            ])
            ->actions([
                Action::make('inputResi')
                    ->label('Input Resi')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('info')
                    ->form([
                        TextInput::make('tracking_number')
                            ->label('Nomor Resi')
                            ->required()
                            ->placeholder('Contoh: JNE123456789'),

                        TextInput::make('courier')
                            ->label('Nama Kurir')
                            // Tetap ambil data default dari order_store
                            ->default(fn(OrderStore $record) => $record->courier ?? '-')
                            ->required(),
                    ])
                    ->action(function (OrderStore $record, array $data): void {
                        $courier = $data['courier'];
                        $resi = $data['tracking_number'];

                        // 1. UPDATE KHUSUS TOKO (OrderStore): Update Resi, Kurir, dan Status
                        $record->update([
                            'tracking_number' => $resi,
                            'courier'         => $courier,
                            'shipping_status' => 'shipped',
                        ]);

                        // 2. UPDATE INDUK (Order): HANYA ubah status jika semua toko sudah kirim
                        $order = $record->order;
                        $orderStores = $order->orderStores;

                        // Cek apakah semua toko di pesanan ini sudah berstatus 'shipped' atau 'completed'
                        $allShipped = $orderStores->every(
                            fn($os) =>
                            in_array($os->shipping_status, ['shipped', 'completed'])
                        );

                        if ($allShipped) {
                            // Kita HANYA mengupdate status. 
                            // Kolom 'courier' dan 'tracking_number' di tabel orders dibiarkan apa adanya ("Multi-Kurir").
                            $order->update([
                                'shipping_status' => 'shipped',
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Resi Berhasil Diinput')
                            ->success()
                            ->send();
                    })
                    ->visible(
                        fn(OrderStore $record) =>
                        $record->order->payment_status === 'paid' &&
                            empty($record->tracking_number)
                    ),

                Action::make('printShippingLabel')
                    ->label('Cetak Resi')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->url(
                        // KUNCI PERUBAHAN: Ganti rutenya agar mengarah ke spesifik OrderStore
                        fn(OrderStore $record): string =>
                        route('orders.print-resi', $record->id)
                    )
                    ->openUrlInNewTab()
                    ->visible(fn(OrderStore $record) => !empty($record->tracking_number)),

                EditAction::make(),
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
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
