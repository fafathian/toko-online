<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';

defineProps({
    latestProducts: Array
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
</script>

<template>

    <Head title="Z-STORE - Destinasi Belanja Modern" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 font-sans pb-20">

        <nav class="absolute top-0 w-full p-4 md:p-6 flex justify-between items-center z-50 bg-transparent">
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

        <header class="relative h-[80vh] w-full bg-[#1c1c1e] overflow-hidden flex items-center justify-center">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-[#121212] to-black opacity-80 z-0">
            </div>
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-600/20 blur-[100px] rounded-full z-0"></div>

            <div class="relative z-10 text-center px-4 max-w-4xl">
                <h1 class="text-6xl md:text-8xl font-black text-white tracking-tighter leading-tight mb-6">
                    Elevate Your <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500">Style.</span>
                </h1>
                <p class="text-xl md:text-2xl text-neutral-400 mb-10 font-light">
                    Koleksi premium dari toko-toko terkurasi. Desain masa kini untuk gaya hidup tanpa batas.
                </p>
                <div class="flex justify-center space-x-4">
                    <Link href="/catalog"
                        class="px-8 py-4 bg-white text-black font-bold rounded-full hover:bg-neutral-200 hover:scale-105 transition-all shadow-xl text-lg">
                        Eksplorasi Katalog Global
                    </Link>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 mt-20">
            <div class="flex justify-between items-end mb-10 border-b border-white/10 pb-4">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Kedatangan Terbaru</h2>
                    <p class="text-neutral-500">Koleksi terhangat yang baru saja rilis minggu ini.</p>
                </div>
                <Link href="/catalog" class="text-indigo-400 hover:text-white font-medium flex items-center space-x-2">
                    <span>Lihat Semua</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <Link :href="`/products/${product.slug}`" v-for="product in latestProducts" :key="product.id"
                    class="group">
                    <div
                        class="aspect-square bg-[#1c1c1e] rounded-2xl mb-4 overflow-hidden relative border border-white/5 group-hover:border-indigo-500/50 transition-colors">
                        <img v-if="product.image_path" :src="`/storage/${product.image_path}`"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                    </div>
                    <div>
                        <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-1">{{
                            product.store.name }}</div>
                        <h3 class="text-white font-medium truncate mb-1">{{ product.name }}</h3>
                        <p class="text-neutral-400 font-light">Rp {{ product.price.toLocaleString('id-ID') }}</p>
                    </div>
                </Link>
            </div>
        </main>
    </div>
</template>