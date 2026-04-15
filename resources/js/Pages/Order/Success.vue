<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    order: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0);
};
</script>

<template>

    <Head title="Pembayaran Berhasil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Konfirmasi Pembayaran</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-2xl rounded-3xl border border-gray-100">

                    <div class="bg-gradient-to-r from-green-400 to-emerald-500 p-8 text-center text-white">
                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/20 mb-4 shadow-inner">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-black mb-1">Berhasil!</h2>
                        <p class="text-green-50 text-sm">Pesanan Anda telah kami terima.</p>
                    </div>

                    <div class="p-8">
                        <div class="text-center mb-6">
                            <p class="text-gray-400 text-xs uppercase tracking-widest font-bold">Nomor Invoice</p>
                            <h3 class="text-xl font-bold text-gray-900">#{{ order?.invoice_number }}</h3>
                        </div>
                        <div class="mt-2">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase">
                                Status: {{ order?.shipping_status === 'pending' ? 'Menunggu Proses' : 'Diproses' }}
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-6 mb-6 border border-dashed border-gray-300">
                            <h4
                                class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 text-center sm:text-left">
                                Rincian Pembayaran</h4>

                            <div v-for="item in order?.items" :key="item.id"
                                class="flex justify-between items-center mb-3">
                                <div class="flex flex-col text-left">
                                    <span class="text-sm font-semibold text-gray-800">{{ item.product?.name || 'Produk'
                                    }}</span>
                                    <span class="text-xs text-gray-500">{{ item.quantity }} x {{
                                        formatCurrency(item.unit_price
                                            || item.price) }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ formatCurrency((item.unit_price ||
                                    item.price)
                                    * item.quantity) }}</span>
                            </div>

                            <div
                                class="flex justify-between items-center mb-3 pt-2 border-t border-gray-200 border-solid">
                                <span class="text-sm text-gray-600 italic">Ongkos Kirim</span>
                                <span class="text-sm font-bold text-gray-900">{{ formatCurrency(20000) }}</span>
                            </div>

                            <div
                                class="border-t-2 border-gray-300 mt-4 pt-4 flex justify-between items-center font-bold">
                                <span class="text-gray-900">Total Bayar</span>
                                <span class="text-2xl text-indigo-600 font-black">{{ formatCurrency(order?.total_price)
                                }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Link href="/orders"
                                class="flex justify-center items-center px-6 py-3.5 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition duration-300 shadow-lg">
                                Riwayat Pesanan
                            </Link>
                            <Link href="/"
                                class="flex justify-center items-center px-6 py-3.5 bg-white border-2 border-gray-200 hover:border-indigo-600 text-gray-700 hover:text-indigo-600 text-sm font-bold rounded-xl transition duration-300">
                                Lanjut Belanja
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>