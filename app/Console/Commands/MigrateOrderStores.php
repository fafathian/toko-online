<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderStore;

#[Signature('app:migrate-order-stores')]
#[Description('Command description')]
class MigrateOrderStores extends Command
{
    /**
     * Execute the console command.
     */
    // app/Console/Commands/MigrateOrderStores.php

    public function handle()
    {
        // Gunakan DELETE biasa, bukan truncate
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\OrderItem::query()->update(['order_store_id' => null]);
        \App\Models\OrderStore::query()->delete();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $orders = \App\Models\Order::with('items.product')->get();

        foreach ($orders as $order) {
            $byStore = $order->items->groupBy(fn($i) => $i->product->store_id);

            foreach ($byStore as $storeId => $items) {
                $os = \App\Models\OrderStore::create([
                    'order_id'        => $order->id,
                    'store_id'        => $storeId,
                    'shipping_status' => $order->shipping_status,
                    'courier'         => $order->courier,
                    'tracking_number' => $order->tracking_number,
                    'received_at'     => $order->received_at,
                ]);

                foreach ($items as $item) {
                    $item->update(['order_store_id' => $os->id]);
                }

                $this->info("✓ Order {$order->id} → Store {$storeId} (OrderStore ID: {$os->id})");
            }
        }

        $this->info('Selesai!');
    }
}
