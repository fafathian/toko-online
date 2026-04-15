<script setup>
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({ orders: Array });

// ─── TRACKING ───────────────────────────────────────────
const trackingData = ref(null);
const isLoadingTracking = ref(false);
const isTrackingModalOpen = ref(false);
const selectedOrderStore = ref(null);

const openTracking = async (orderStore, invoiceNumber) => {
    selectedOrderStore.value = { ...orderStore, invoice_number: invoiceNumber };
    isTrackingModalOpen.value = true;
    isLoadingTracking.value = true;
    trackingData.value = null;

    if (orderStore.courier?.toUpperCase().includes('SPX')) {
        isTrackingModalOpen.value = false;
        window.open(`https://spx.co.id/detail/${orderStore.tracking_number}`, '_blank');
        isLoadingTracking.value = false;
        return;
    }

    try {
        const response = await axios.get(`/api/order-stores/${orderStore.id}/tracking`);
        trackingData.value = response.data;
    } catch (error) {
        console.error("Tracking error:", error);
        trackingData.value = { success: false };
    } finally {
        isLoadingTracking.value = false;
    }
};

// ─── KONFIRMASI DITERIMA PER TOKO ─────────────────────────
const isConfirmModalOpen = ref(false);
const storeToConfirm = ref(null);
const invoiceToConfirm = ref('');
const isConfirming = ref(false);

// PERBAIKAN 1: Tangkap parameter invoiceNumber
const confirmStoreReceived = (orderStore, invoiceNumber) => {
    storeToConfirm.value = orderStore;
    invoiceToConfirm.value = invoiceNumber;
    isConfirmModalOpen.value = true;
};

const submitReceived = async () => {
    if (!storeToConfirm.value) return;
    isConfirming.value = true;

    try {
        const response = await axios.post(`/orders/store/${storeToConfirm.value.id}/receive`);
        isConfirmModalOpen.value = false;
        router.reload({ only: ['orders'] });
        alert(response.data.message);
    } catch (error) {
        alert(error.response?.data?.message || 'Terjadi kesalahan saat konfirmasi.');
    } finally {
        isConfirming.value = false;
    }
};

// ─── HELPERS ─────────────────────────────────────────────
const page = usePage();
const cartCount = computed(() => page.props.cartCount);
const showUserMenu = ref(false);

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

const translateStatus = (status) => {
    const translations = {
        'pending': 'Belum Dibayar',
        'processing': 'Sedang Diproses',
        'shipped': 'Sedang Dikirim',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
    };
    return translations[status] || status;
};

const unreadChatCount = ref(page.props.unreadChatCount || 0);

watch(
    () => page.props.unreadChatCount,
    (newCount) => {
        unreadChatCount.value = newCount || 0;
    }
);

onMounted(() => {
    if (page.props.auth && page.props.auth.user) {
        window.Echo.private(`user.${page.props.auth.user.id}`)
            .listen('.MessageSent', (e) => {
                unreadChatCount.value++;
            });
    }
});


// ─── PAYMENT ─────────────────────────────────────────────
const payNow = (order) => {
    window.snap.pay(order.snap_token, {
        onSuccess: () => { window.location.href = `/order/success/${order.id}`; },
        onPending: () => { alert("Silakan selesaikan pembayaran sesuai instruksi."); window.location.reload(); },
        onError: () => { alert("Maaf, terjadi kesalahan pada pembayaran."); },
        onClose: () => { console.log('Customer closed the popup without finishing the payment'); }
    });
};

// ─── CHAT ───────────────────────────────────────────────
const startChat = (order) => {
    const storeId = order.items?.[0]?.product?.store_id;

    if (!storeId) {
        console.error("Store ID tidak ditemukan pada pesanan ini.");
        return;
    }

    router.post('/chat/room/create', {
        store_id: storeId,
        order_id: order.id,
    });
};

// ─── REVIEW ─────────────────────────────────────────────
const showReviewModal = ref(false);
const orderToReview = ref(null);

const reviewForm = useForm({
    order_store_id: '',
    items: []
});

// PERBAIKAN 2: Tangkap order utama, lalu filter barangnya berdasarkan toko
const openReviewModal = (orderStore, order) => {
    orderToReview.value = orderStore;
    reviewForm.order_store_id = orderStore.id;

    // Filter barang dari pesanan utama HANYA yang berasal dari toko ini
    const itemsFromThisStore = order.items.filter(item => item.product.store_id === orderStore.store_id);

    reviewForm.items = itemsFromThisStore.map(item => ({
        product_id: item.product_id,
        product_name: item.product.name,
        rating: 5,
        comment: ''
    }));

    showReviewModal.value = true;
};

const submitReview = () => {
    reviewForm.post('/reviews', {
        preserveScroll: true,
        onSuccess: () => {
            showReviewModal.value = false;
        }
    });
};
</script>

<template>

    <Head title="Pesanan Saya - Z-STORE" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 pb-20">

        <nav
            class="sticky top-0 w-full p-4 md:p-6 flex justify-between items-center z-50 bg-[#121212]/90 backdrop-blur-md border-b border-white/10 shadow-sm">
            <Link href="/" class="text-2xl font-black tracking-tighter text-white">Z-STORE.</Link>
            <div class="flex items-center space-x-4 md:space-x-6 z-50">
                <Link href="/catalog" class="text-sm font-medium text-white hover:text-indigo-400 transition-colors">
                    Katalog Global</Link>
                <Link v-if="!$page.props.auth.user" href="/login"
                    class="text-sm font-medium text-neutral-400 hover:text-white transition-colors">Login / Daftar
                </Link>
                <div v-else class="relative">
                    <button @click="showUserMenu = !showUserMenu"
                        class="flex items-center space-x-2 text-sm font-medium text-white hover:text-indigo-400 transition-colors focus:outline-none">
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-md">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <span class="hidden md:block">{{ $page.props.auth.user.name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300"
                            :class="{ 'rotate-180': showUserMenu }" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="showUserMenu" @click="showUserMenu = false" class="fixed inset-0 z-40"></div>
                    <div v-if="showUserMenu"
                        class="absolute right-0 mt-3 w-56 bg-[#1c1c1e] rounded-xl border border-white/10 shadow-2xl py-2 z-50">
                        <div class="px-4 py-3 border-b border-white/5 mb-2">
                            <p class="text-xs text-neutral-400">Masuk sebagai</p>
                            <p class="text-sm font-bold text-white truncate">{{ $page.props.auth.user.email }}</p>
                        </div>
                        <Link href="/profile"
                            class="block px-4 py-2 text-sm text-neutral-300 hover:bg-white/5 hover:text-white transition-colors">
                            Pengaturan Profil</Link>
                        <Link href="/orders"
                            class="block px-4 py-2 text-sm text-neutral-300 hover:bg-white/5 hover:text-white transition-colors">
                            Daftar Pesanan</Link>
                        <a v-if="$page.props.hasStore" href="/seller"
                            class="block w-full text-left px-4 py-2 mt-2 text-sm text-indigo-400 font-medium bg-indigo-500/10 hover:bg-indigo-500/20 transition-colors">Kelola
                            Toko Anda</a>
                        <div class="border-t border-white/5 mt-2 pt-2">
                            <Link href="/logout" method="post" as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-red-400 font-medium hover:bg-red-500/10 transition-colors">
                                Keluar (Logout)</Link>
                        </div>
                    </div>
                </div>
                <Link href="/chat" class="relative text-white hover:text-indigo-400 transition ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    <span v-if="unreadChatCount > 0"
                        class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-[#121212]">{{
                        unreadChatCount }}</span>
                </Link>
                <Link href="/cart" class="relative text-white hover:text-indigo-400 transition ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <span v-if="cartCount > 0"
                        class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-[#121212]">{{
                        cartCount }}</span>
                </Link>
            </div>
        </nav>

        <main class="max-w-5xl mx-auto px-4 mt-10">
            <h1 class="text-3xl font-bold text-white mb-8">Riwayat Pesanan</h1>

            <div v-if="orders.length > 0" class="space-y-6">
                <div v-for="order in orders" :key="order.id"
                    class="bg-[#1c1c1e] border border-white/5 rounded-2xl overflow-hidden shadow-xl transition-all hover:border-white/10">

                    <div
                        class="p-4 md:p-6 border-b border-white/5 flex flex-wrap justify-between items-center gap-4 bg-[#242426]/30">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm">
                                <p class="text-neutral-500 mb-0.5">No. Invoice</p>
                                <p class="text-white font-mono font-bold">{{ order.invoice_number }}</p>
                            </div>
                            <div class="w-px h-8 bg-white/10"></div>
                            <div class="text-sm">
                                <p class="text-neutral-500 mb-0.5">Tanggal Pembelian</p>
                                <p class="text-white font-medium">{{ new
                                    Date(order.created_at).toLocaleDateString('id-ID', {
                                        day: 'numeric', month: 'long',
                                    year: 'numeric' }) }}</p>
                            </div>
                        </div>
                        <span :class="getStatusClass(order.shipping_status)"
                            class="px-4 py-1.5 rounded-full text-xs font-bold border uppercase tracking-wider">
                            {{ translateStatus(order.shipping_status) }}
                        </span>
                    </div>

                    <div v-if="order.order_stores?.length > 0">
                        <div v-for="orderStore in order.order_stores" :key="orderStore.id"
                            class="px-6 py-3 border-b border-white/5 bg-indigo-900/10 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-5 h-5 text-indigo-400 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                <div class="text-sm">
                                    <span class="text-xs font-bold text-indigo-400 uppercase mr-2">{{
                                        orderStore.store?.name }}</span>
                                    <span class="text-neutral-400">Kurir: <strong class="text-indigo-300">{{
                                            orderStore.courier || 'Menunggu Penjual' }}</strong></span>
                                    <span v-if="orderStore.tracking_number" class="ml-3 text-neutral-400">
                                        Resi: <strong class="text-white font-mono">{{ orderStore.tracking_number
                                            }}</strong>
                                    </span>
                                    <span :class="getStatusClass(orderStore.shipping_status)"
                                        class="ml-3 px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase">
                                        {{ translateStatus(orderStore.shipping_status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 ml-auto">
                                <button v-if="orderStore.tracking_number"
                                    @click="openTracking(orderStore, order.invoice_number)"
                                    class="flex-shrink-0 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white text-xs font-bold py-1.5 px-4 rounded-lg transition border border-indigo-500/30">
                                    Lacak
                                </button>

                                <button v-if="orderStore.shipping_status === 'shipped'"
                                    @click="confirmStoreReceived(orderStore, order.invoice_number)"
                                    class="flex-shrink-0 bg-green-600 hover:bg-green-500 text-white text-xs font-bold py-1.5 px-4 rounded-lg transition shadow-md shadow-green-500/20">
                                    ✓ Diterima
                                </button>

                                <button v-if="orderStore.shipping_status === 'completed' && !orderStore.is_reviewed"
                                    @click="openReviewModal(orderStore, order)"
                                    class="bg-yellow-500 hover:bg-yellow-400 text-black text-xs font-bold py-1.5 px-4 rounded-lg transition shadow-md shadow-yellow-500/20 flex items-center gap-1">
                                    ⭐ Ulas
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.items.length > 0" class="p-6 flex items-center gap-6">
                        <div
                            class="w-20 h-20 bg-[#242426] rounded-xl overflow-hidden flex-shrink-0 border border-white/5">
                            <img v-if="order.items[0].product.image_path"
                                :src="`/storage/${order.items[0].product.image_path}`"
                                class="w-full h-full object-cover" />
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">{{
                                order.items[0].product.store.name }}</div>
                            <h3 class="text-white font-bold text-lg leading-tight">{{ order.items[0].product.name }}
                            </h3>
                            <p class="text-sm text-neutral-500 mt-1">{{ order.items[0].quantity }} barang x Rp {{
                                Number(order.items[0].unit_price).toLocaleString('id-ID') }}</p>
                            <p v-if="order.items.length > 1"
                                class="text-xs font-medium text-neutral-400 mt-3 bg-white/5 inline-block px-2 py-1 rounded">
                                + {{ order.items.length - 1 }} produk lainnya di pesanan ini
                            </p>
                        </div>
                        <div class="text-right hidden md:block border-l border-white/10 pl-6">
                            <p class="text-sm text-neutral-500 mb-1">Total Tagihan</p>
                            <p class="text-2xl font-black text-indigo-400">Rp {{
                                Number(order.total_price).toLocaleString('id-ID') }}</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-[#242426]/50 flex justify-between items-center">
                        <p class="text-sm text-neutral-400 md:hidden">Total: <strong class="text-indigo-400">Rp {{
                            Number(order.total_price).toLocaleString('id-ID') }}</strong></p>
                        <div class="flex items-center gap-4 ml-auto">
                            <Link :href="`/orders/${order.id}`"
                                class="text-sm font-semibold text-neutral-400 hover:text-white transition">
                                Lihat Detail Invoice
                            </Link>

                            <button v-if="order.payment_status === 'pending'" @click="payNow(order)"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl">
                                Bayar Sekarang
                            </button>
                            <button v-if="order.shipping_status === 'shipped'" @click="startChat(order)"
                                class="bg-[#2c2c2e] hover:bg-white/10 text-neutral-300 hover:text-white text-sm font-semibold py-2.5 px-5 rounded-xl transition flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                </svg>
                                Chat Penjual
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 bg-[#1c1c1e] rounded-3xl border border-white/5 mt-8">
                <div class="w-24 h-24 bg-[#242426] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-10 h-10 text-neutral-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75M8.25 21h3" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Belum Ada Transaksi</h2>
                <p class="text-neutral-500 text-sm mb-6">Riwayat pesanan Anda masih kosong. Yuk, temukan barang
                    impianmu!</p>
                <Link href="/catalog"
                    class="inline-block bg-white text-black font-bold py-3 px-8 rounded-xl hover:bg-neutral-200 transition">
                    Mulai Belanja</Link>
            </div>
        </main>
    </div>

    <div v-if="isTrackingModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="isTrackingModalOpen = false"></div>
        <div
            class="relative bg-[#1c1c1e] w-full max-w-lg rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-white/5 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-white">Lacak Pengiriman</h3>
                    <p class="text-xs text-neutral-500">{{ selectedOrderStore?.invoice_number }} — {{
                        selectedOrderStore?.store?.name }}</p>
                    <p v-if="selectedOrderStore?.tracking_number" class="text-xs text-indigo-400 font-mono mt-0.5">{{
                        selectedOrderStore.tracking_number }}</p>
                </div>
                <button @click="isTrackingModalOpen = false" class="p-2 hover:bg-white/5 rounded-full">✕</button>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div v-if="isLoadingTracking" class="py-10 text-center animate-pulse text-neutral-500">Mencari posisi
                    paket...</div>
                <div v-else-if="trackingData?.success" class="space-y-6">
                    <div v-for="(history, index) in trackingData.history" :key="index" class="relative pl-8">
                        <div v-if="index !== trackingData.history.length - 1"
                            class="absolute left-[11px] top-5 w-[1px] h-full bg-white/10"></div>
                        <div :class="index === 0 ? 'bg-indigo-500 shadow-lg shadow-indigo-500/50' : 'bg-neutral-700'"
                            class="absolute left-0 top-1.5 w-6 h-6 rounded-full border-4 border-[#1c1c1e]"></div>
                        <div>
                            <p :class="index === 0 ? 'text-white font-bold' : 'text-neutral-400'" class="text-sm">{{
                                history.note }}</p>
                            <p class="text-[10px] text-neutral-500 uppercase mt-1">{{ history.updated_at }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-10 text-neutral-500 italic">Data tracking belum tersedia atau resi
                    tidak valid.</div>
            </div>
        </div>
    </div>

    <div v-if="isConfirmModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="isConfirmModalOpen = false"></div>
        <div
            class="relative bg-[#1c1c1e] w-full max-w-md rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-white/5">
                <div class="w-14 h-14 bg-green-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-7 h-7 text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white text-center">Konfirmasi Pesanan Diterima</h3>
                <p class="text-sm text-neutral-400 text-center mt-1">
                    Invoice <span class="text-white font-mono font-bold">{{ invoiceToConfirm }}</span>
                </p>
            </div>
            <div class="p-6">
                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4 mb-6">
                    <p class="text-sm text-yellow-400 text-center">
                        Pastikan Anda sudah menerima paket dari toko <strong>{{ storeToConfirm?.store?.name }}</strong>
                        dalam kondisi baik. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button @click="isConfirmModalOpen = false" :disabled="isConfirming"
                        class="flex-1 py-3 rounded-xl border border-white/10 text-neutral-400 hover:text-white hover:bg-white/5 text-sm font-bold transition">Batal</button>
                    <button @click="submitReceived" :disabled="isConfirming"
                        class="flex-1 py-3 rounded-xl bg-green-600 hover:bg-green-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold transition shadow-lg shadow-green-500/20 flex items-center justify-center gap-2">
                        <svg v-if="isConfirming" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4z" />
                        </svg>
                        {{ isConfirming ? 'Memproses...' : 'Ya, Sudah Diterima' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showReviewModal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div
            class="bg-[#1c1c1e] rounded-3xl border border-white/10 w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-white/10 flex justify-between items-center bg-[#242426]">
                <h2 class="text-xl font-bold text-white">Beri Ulasan Produk</h2>
                <button @click="showReviewModal = false" class="text-neutral-400 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto space-y-8 flex-1">
                <div v-for="(item, index) in reviewForm.items" :key="index"
                    class="bg-[#242426]/50 p-4 rounded-2xl border border-white/5">
                    <p class="font-semibold text-white mb-3">{{ item.product_name }}</p>
                    <div class="flex gap-2 mb-4">
                        <button v-for="star in 5" :key="star" type="button" @click="item.rating = star"
                            class="focus:outline-none transition-transform hover:scale-110">
                            <svg class="w-8 h-8 transition-colors"
                                :class="item.rating >= star ? 'text-yellow-400 fill-current' : 'text-neutral-600 fill-current'"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                            </svg>
                        </button>
                    </div>
                    <textarea v-model="item.comment" rows="3" placeholder="Ceritakan kepuasanmu terhadap produk ini..."
                        class="w-full bg-[#121212] border border-white/10 rounded-xl p-3 text-white placeholder-neutral-500 focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-white/10 bg-[#242426] flex justify-end gap-3">
                <button @click="showReviewModal = false" type="button"
                    class="px-6 py-2.5 rounded-xl text-neutral-300 font-semibold hover:bg-white/5 transition">Batal</button>
                <button @click="submitReview" :disabled="reviewForm.processing"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition flex items-center gap-2 disabled:opacity-50">
                    <span v-if="reviewForm.processing">Mengirim...</span>
                    <span v-else>Kirim Ulasan</span>
                </button>
            </div>
        </div>
    </div>
</template>