<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    order: Object
});

// Fungsi untuk warna status
const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        'processing': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'shipped': 'bg-purple-500/10 text-purple-500 border-purple-500/20',
        'completed': 'bg-green-500/10 text-green-500 border-green-500/20',
        'cancelled': 'bg-red-500/10 text-red-500 border-red-500/20',
    };
    return classes[status] || 'bg-neutral-500/10 text-neutral-500 border-neutral-500/20';
};

// Fungsi penerjemah status ke Bahasa Indonesia
const translateStatus = (status) => {
    const translations = {
        'pending': 'Belum Dibayar',
        'processing': 'Sedang Diproses',
        'shipped': 'Sedang Dikirim',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan'
    };
    return translations[status] || status;
};

// Fitur Print Struk
const printInvoice = () => {
    window.print();
};
</script>

<template>

    <Head :title="`Invoice ${order.invoice_number} - Z-STORE`" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 pb-20 print:bg-white print:text-black font-sans">

        <nav class="p-6 border-b border-white/10 bg-[#121212]/90 sticky top-0 z-50 print:hidden">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link href="/orders" class="p-2 bg-[#242426] hover:bg-white/10 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </Link>
                    <span class="font-medium text-white">Detail Pesanan</span>
                </div>
                <button @click="printInvoice"
                    class="flex items-center gap-2 text-sm font-bold bg-white text-black px-4 py-2 rounded-lg hover:bg-neutral-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.123.918 2.025 2.025 2.025h6.45c1.107 0 2.025-.902 2.025-2.025V6.423Z" />
                    </svg>
                    Cetak Invoice
                </button>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto px-4 mt-8 print:mt-0 print:px-0">
            <div
                class="bg-[#1c1c1e] print:bg-white rounded-3xl border border-white/5 print:border-neutral-300 overflow-hidden shadow-2xl print:shadow-none">

                <div
                    class="p-8 md:p-12 border-b border-white/5 print:border-neutral-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-black text-white print:text-black tracking-tighter mb-2">Z-STORE.</h1>
                        <p class="text-sm text-neutral-500 print:text-neutral-600">Bukti Pembelian Resmi</p>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-sm text-neutral-500 print:text-neutral-600 mb-1">Nomor Invoice</p>
                        <p class="text-xl font-mono font-bold text-indigo-400 print:text-black">{{ order.invoice_number
                        }}</p>
                        <span :class="getStatusClass(order.status)"
                            class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wider print:border-black print:text-black print:bg-transparent">
                            {{ translateStatus(order.status) }}
                        </span>
                    </div>
                </div>

                <div
                    class="p-8 md:p-12 grid grid-cols-1 md:grid-cols-2 gap-10 border-b border-white/5 print:border-neutral-200">
                    <div>
                        <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-4">Diterbitkan Untuk
                        </h2>
                        <p class="text-white print:text-black font-medium text-lg mb-1">{{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-sm text-neutral-400 print:text-neutral-700 leading-relaxed max-w-xs">{{
                            order.shipping_address }}</p>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-4">Informasi Kurir
                        </h2>
                        <div class="space-y-3">
                            <div class="flex justify-between border-b border-white/5 print:border-neutral-200 pb-2">
                                <span class="text-sm text-neutral-400 print:text-neutral-700">Tanggal Beli</span>
                                <span class="text-sm text-white print:text-black font-medium">{{ new
                                    Date(order.created_at).toLocaleDateString('id-ID', {
                                        day: 'numeric', month: 'long',
                                        year: 'numeric'
                                    }) }}</span>
                            </div>
                            <div class="flex justify-between border-b border-white/5 print:border-neutral-200 pb-2">
                                <span class="text-sm text-neutral-400 print:text-neutral-700">Ekspedisi</span>
                                <span class="text-sm text-white print:text-black font-medium">{{ order.courier || 'Belum Diproses' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-white/5 print:border-neutral-200 pb-2">
                                <span class="text-sm text-neutral-400 print:text-neutral-700">No. Resi</span>
                                <span class="text-sm text-indigo-400 print:text-black font-mono font-medium">{{
                                    order.tracking_number || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 md:p-12">
                    <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-6">Rincian Barang</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="border-b border-white/10 print:border-neutral-300 text-sm text-neutral-400 print:text-neutral-600">
                                    <th class="pb-4 font-medium">Produk</th>
                                    <th class="pb-4 font-medium text-center">Jumlah</th>
                                    <th class="pb-4 font-medium text-right">Harga Satuan</th>
                                    <th class="pb-4 font-medium text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr v-for="item in order.items" :key="item.id"
                                    class="border-b border-white/5 print:border-neutral-200">
                                    <td class="py-4">
                                        <p class="font-bold text-white print:text-black">{{ item.product.name }}</p>
                                        <p class="text-xs text-indigo-400 print:text-neutral-600 mt-1 uppercase">{{
                                            item.product.store.name }}</p>
                                    </td>
                                    <td class="py-4 text-center text-neutral-300 print:text-black">{{ item.quantity }}
                                    </td>
                                    <td class="py-4 text-right text-neutral-300 print:text-black">Rp {{
                                        Number(item.unit_price).toLocaleString('id-ID') }}</td>
                                    <td class="py-4 text-right font-bold text-white print:text-black">Rp {{
                                        (item.quantity * item.unit_price).toLocaleString('id-ID') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <div class="w-full md:w-1/2 lg:w-1/3 space-y-3">
                            <div class="flex justify-between text-sm text-neutral-400 print:text-neutral-700">
                                <span>Total Harga Barang</span>
                                <span>Rp {{ (order.total_price - order.shipping_cost).toLocaleString('id-ID') }}</span>
                            </div>
                            <div
                                class="flex justify-between text-sm text-neutral-400 print:text-neutral-700 pb-3 border-b border-white/10 print:border-neutral-300">
                                <span>Ongkos Kirim</span>
                                <span>Rp {{ Number(order.shipping_cost).toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="font-bold text-white print:text-black">Total Tagihan</span>
                                <span class="text-2xl font-black text-indigo-400 print:text-black">Rp {{
                                    Number(order.total_price).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <p class="text-center text-sm text-neutral-500 mt-8 print:hidden">
                Jika Anda memiliki pertanyaan mengenai invoice ini, silakan hubungi layanan pelanggan kami.
            </p>
        </main>
    </div>
</template>