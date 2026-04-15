<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';


const addToCart = (productId) => {
    router.post('/cart', {
        product_id: productId,
        quantity: 1
    });
};


defineProps({
    store: Object,
    products: Object
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

const startStoreChat = () => {
    // Kita ambil id dari objek store yang dikirim dari backend
    const storeId = page.props.store?.id;

    if (!storeId) {
        console.error("Gagal: Data store tidak ditemukan di page.props!");
        return;
    }

    // Kirim ke backend
    router.post('/chat/room/create', {
        store_id: storeId,
    });
};
</script>

<template>

    <Head :title="`Toko ${store.name} - Z-STORE`" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 font-sans pb-20">

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

        <main class="max-w-6xl mx-auto px-4 sm:px-6 mt-6">

            <div class="bg-[#1c1c1e] rounded-2xl border border-white/5 overflow-hidden shadow-xl mb-8">
                <div class="h-32 md:h-48 w-full bg-gradient-to-r from-indigo-900 via-purple-900 to-black relative">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
                </div>

                <div class="px-6 pb-6 relative">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 -mt-12 md:-mt-16">

                        <div class="flex items-end space-x-5 z-10">
                            <div
                                class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-[#1c1c1e] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                                {{ store.name.charAt(0) }}
                            </div>
                            <div class="pb-2 md:pb-4">
                                <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center space-x-2">
                                    <span>{{ store.name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-6 h-6 text-blue-400">
                                        <path fill-rule="evenodd"
                                            d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </h1>
                                <p class="text-sm text-neutral-400 mt-1">Aktif 5 Menit Lalu • Kab. Bogor</p>
                            </div>
                        </div>

                        <div class="flex space-x-3 w-full md:w-auto z-10">
                            <button @click="startStoreChat"
                                class="flex-1 md:flex-none px-6 py-2.5 rounded-lg border border-indigo-500 text-indigo-400 hover:bg-indigo-500/10 font-semibold transition flex justify-center items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                                </svg>
                                <span>Chat</span>
                            </button>
                            <button
                                class="flex-1 md:flex-none px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition flex justify-center items-center space-x-2">
                                <span>+ Ikuti Toko</span>
                            </button>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-white/10 text-center md:text-left">
                        <div>
                            <p class="text-neutral-500 text-sm">Produk</p>
                            <p class="text-white font-semibold text-lg">{{ products.total }}</p>
                        </div>
                        <div>
                            <p class="text-neutral-500 text-sm">Pengikut</p>
                            <p class="text-white font-semibold text-lg">1.2K</p>
                        </div>
                        <div>
                            <p class="text-neutral-500 text-sm">Penilaian</p>
                            <p class="text-white font-semibold text-lg">4.9 / 5.0</p>
                        </div>
                        <div>
                            <p class="text-neutral-500 text-sm">Performa Chat</p>
                            <p class="text-white font-semibold text-lg">98%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white uppercase tracking-wider">Semua Produk</h2>
                <div class="text-sm text-neutral-400">Urutkan: <span
                        class="text-indigo-400 cursor-pointer">Terbaru</span></div>
            </div>

            <div v-if="products.data.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <Link :href="`/products/${product.slug}`" v-for="product in products.data" :key="product.id"
                    class="bg-[#1c1c1e] rounded-xl border border-white/5 overflow-hidden hover:border-indigo-500/50 hover:-translate-y-1 transition-all duration-300 group shadow-lg">
                    <div class="aspect-square bg-[#242426] relative overflow-hidden">
                        <img v-if="product.image_path" :src="`/storage/${product.image_path}`" alt="foto"
                            class="w-full h-full object-cover" />
                        <div v-if="product.stock <= 0"
                            class="absolute inset-0 bg-black/60 flex items-center justify-center">
                            <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded">HABIS</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3
                            class="text-sm text-neutral-300 line-clamp-2 leading-tight min-h-[40px] mb-2 group-hover:text-indigo-300 transition-colors">
                            {{ product.name }}
                        </h3>
                        <div class="flex items-center justify-between mt-auto">
                            <p class="font-bold text-indigo-400">Rp {{ product.price.toLocaleString('id-ID') }}</p>
                            <span class="text-[10px] text-neutral-500">{{ product.stock }} stok</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-20 bg-[#1c1c1e] rounded-xl border border-white/5">
                <p class="text-neutral-500 text-lg">Toko ini belum memiliki produk yang aktif.</p>
            </div>

        </main>
    </div>
</template>