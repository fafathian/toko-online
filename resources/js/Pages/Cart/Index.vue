<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';

const props = defineProps({
    cartItems: Array
});

const page = usePage();
const cartCount = computed(() => page.props.cartCount);

const showUserMenu = ref(false);

const unreadChatCount = ref(page.props.unreadChatCount || 0);

watch(
    () => page.props.unreadChatCount,
    (newCount) => {
        unreadChatCount.value = newCount || 0;
    }
);

onMounted(() => {
    if (page.props.auth && page.props.auth.user) {
        // Coba tambahkan TITIK (.) di depan nama Event-nya
        window.Echo.private(`user.${page.props.auth.user.id}`)
            .listen('.MessageSent', (e) => {
                unreadChatCount.value++;
            });
    }
});

// Fungsi untuk menambah/mengurangi jumlah
const updateQuantity = (cartId, currentQty, change) => {
    // CEK KONDISI KRITIS: Jika jumlah saat ini 1 dan ditekan minus
    if (currentQty === 1 && change === -1) {
        // Alihkan (bypass) ke fungsi hapus yang sudah punya pop-up warning
        removeItem(cartId);
        return;
    }

    const newQty = currentQty + change;

    // Pengaman tambahan agar data tidak tembus 0 ke database
    if (newQty < 1) return;

    router.patch(`/cart/${cartId}`, { quantity: newQty }, { preserveScroll: true });
};

// Fungsi menghapus barang
const removeItem = (cartId) => {
    if (confirm('Hapus barang ini dari keranjang?')) {
        router.delete(`/cart/${cartId}`, { preserveScroll: true });
    }
};

// Menghitung Total Harga (Real-time di sisi Frontend)
const cartTotal = computed(() => {
    return props.cartItems.reduce((total, item) => total + (item.product.price * item.quantity), 0);
});
</script>

<template>

    <Head title="Keranjang Belanja - Z-STORE" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 font-sans pb-24">

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
                        class="absolute right-0 mt-3 w-56 bg-[#1c1c1e] rounded-xl border border-white/10 shadow-2xl py-2 z-50 origin-top-right transform transition-all">
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
                            class="block w-full text-left px-4 py-2 mt-2 text-sm text-indigo-400 font-medium bg-indigo-500/10 hover:bg-indigo-500/20 transition-colors">
                            Kelola Toko Anda
                        </a>

                        <div class="border-t border-white/5 mt-2 pt-2">
                            <Link href="/logout" method="post" as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-red-400 font-medium hover:bg-red-500/10 transition-colors">
                                Keluar (Logout)
                            </Link>
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
                        class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-[#121212]">
                        {{ unreadChatCount }}
                    </span>
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

        <main class="max-w-6xl mx-auto px-4 sm:px-6 mt-8">
            <h1 class="text-3xl font-bold text-white mb-8">Keranjang Belanja</h1>

            <div v-if="cartItems.length > 0" class="flex flex-col lg:flex-row gap-8">

                <div class="w-full lg:w-2/3 space-y-4">
                    <div v-for="item in cartItems" :key="item.id"
                        class="bg-[#1c1c1e] p-4 md:p-6 rounded-2xl border border-white/5 flex gap-4 md:gap-6 relative group">

                        <button @click="removeItem(item.id)"
                            class="absolute top-4 right-4 text-neutral-500 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>

                        <div
                            class="w-24 h-24 md:w-32 md:h-32 bg-[#242426] rounded-xl overflow-hidden flex-shrink-0 border border-white/5">
                            <img v-if="item.product.image_path" :src="`/storage/${item.product.image_path}`"
                                class="w-full h-full object-cover" />
                        </div>

                        <div class="flex flex-col justify-between flex-1 py-1">
                            <div>
                                <Link :href="`/store/${item.product.store.slug}`"
                                    class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-1 block hover:underline">
                                    {{ item.product.store.name }}
                                </Link>
                                <Link :href="`/products/${item.product.slug}`"
                                    class="text-lg md:text-xl font-medium text-white line-clamp-2 hover:text-indigo-300">
                                    {{ item.product.name }}
                                </Link>
                            </div>

                            <div class="flex items-end justify-between mt-4">
                                <div class="text-xl font-bold text-indigo-400">
                                    Rp {{ item.product.price.toLocaleString('id-ID') }}
                                </div>

                                <div
                                    class="flex items-center border border-white/20 rounded-lg bg-[#242426] overflow-hidden">
                                    <button @click="updateQuantity(item.id, item.quantity, -1)"
                                        class="px-3 py-1.5 text-neutral-400 hover:text-white hover:bg-white/10 transition-colors">-</button>
                                    <span
                                        class="px-4 py-1.5 text-sm font-semibold text-white border-x border-white/20">{{
                                            item.quantity }}</span>
                                    <button @click="updateQuantity(item.id, item.quantity, 1)"
                                        class="px-3 py-1.5 text-neutral-400 hover:text-white hover:bg-white/10 transition-colors">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/3">
                    <div class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-white/5 sticky top-24 shadow-2xl">
                        <h2 class="text-xl font-bold text-white mb-6">Ringkasan Belanja</h2>

                        <div class="space-y-4 text-sm text-neutral-400 border-b border-white/10 pb-6 mb-6">
                            <div class="flex justify-between">
                                <span>Total Harga ({{cartItems.reduce((acc, item) => acc + item.quantity, 0)}}
                                    barang)</span>
                                <span class="text-white font-medium">Rp {{ cartTotal.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Diskon</span>
                                <span class="text-white font-medium">-</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mb-8">
                            <span class="text-lg font-medium text-white">Total Tagihan</span>
                            <span class="text-2xl font-bold text-indigo-400">Rp {{ cartTotal.toLocaleString('id-ID')
                            }}</span>
                        </div>

                        <Link href="/checkout"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-600/30 transition-all text-lg flex justify-center items-center space-x-2">
                            <span>Lanjut ke Pembayaran</span>
                        </Link>
                    </div>
                </div>

            </div>

            <div v-else class="text-center py-32 bg-[#1c1c1e] rounded-3xl border border-white/5">
                <div class="w-24 h-24 bg-[#242426] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 text-neutral-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Keranjangmu masih kosong</h2>
                <p class="text-neutral-400 mb-8">Yuk, temukan barang-barang menarik di katalog kami!</p>
                <Link href="/"
                    class="inline-block px-8 py-3 bg-white text-black font-bold rounded-xl hover:bg-neutral-200 transition-colors">
                    Mulai Belanja
                </Link>
            </div>

        </main>
    </div>
</template>