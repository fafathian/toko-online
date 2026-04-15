<?php

namespace App\Filament\Seller\Widgets;

use App\Models\OrderStore;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerStatsOverview extends BaseWidget
{
    // Tambahkan caching sederhana agar widget tidak query setiap detik
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Gunakan pluck langsung untuk id toko
        $storeIds = Store::where('user_id', $userId)->pluck('id')->toArray();

        if (empty($storeIds)) {
            return [
                Stat::make('Total Pendapatan', 'Rp 0')->color('success'),
                Stat::make('Pesanan Perlu Dikirim', 0)->color('warning'),
                Stat::make('Produk Aktif', 0)->color('info'),
            ];
        }

        // OPTIMASI 1: Gunakan Join untuk pendapatan (Jauh lebih cepat dari whereHas)
        $pendapatan = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('products.store_id', $storeIds)
            // Mengakomodasi berbagai kemungkinan status sukses dari payment gateway
            ->whereIn('orders.payment_status', ['paid', 'success', 'settlement'])
            ->where('orders.shipping_status', 'completed')
            ->selectRaw('SUM(order_items.unit_price * order_items.quantity) as total')
            ->value('total') ?? 0;

        // OPTIMASI 2: Optimalkan query Perlu Dikirim
        $perluDikirim = OrderStore::join('orders', 'order_stores.order_id', '=', 'orders.id')
            ->whereIn('order_stores.store_id', $storeIds)
            ->where('order_stores.shipping_status', 'processing')
            ->where('orders.payment_status', 'paid')
            ->count();

        // OPTIMASI 3: Produk aktif (sudah cukup cepat, pastikan ada index di store_id)
        $produkAktif = Product::whereIn('store_id', $storeIds)->count();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($pendapatan, 0, ',', '.'))
                ->description('Dari pesanan yang sudah selesai (Diterima)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pesanan Perlu Dikirim', $perluDikirim)
                ->description('Pesanan yang harus segera diproses')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make('Produk Aktif', $produkAktif)
                ->description('Jumlah produk yang Anda miliki')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
        ];
    }
}
