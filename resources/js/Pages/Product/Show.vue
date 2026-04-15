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
    product: Object
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

    <Head :title="product.name" />

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

            <div class="flex items-center space-x-2 text-sm text-neutral-500 mb-6">
                <Link href="/" class="hover:text-indigo-400">Home</Link>
                <span>/</span>
                <span class="hover:text-indigo-400 cursor-pointer">{{ product.store.name }}</span>
                <span>/</span>
                <span class="text-neutral-300 truncate w-48 md:w-auto">{{ product.name }}</span>
            </div>

            <div
                class="bg-[#1c1c1e] rounded-2xl border border-white/5 p-4 md:p-8 flex flex-col md:flex-row gap-8 lg:gap-12 shadow-xl">

                <div class="w-full md:w-2/5 flex-shrink-0">
                    <div
                        class="relative w-full aspect-square rounded-xl overflow-hidden bg-[#242426] border border-white/5 group">
                        <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div v-else class="absolute inset-0 flex items-center justify-center text-neutral-600">
                            Tidak ada foto
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-3/5 flex flex-col justify-start">

                    <h1 class="text-2xl md:text-3xl font-semibold text-white leading-tight mb-2">
                        {{ product.name }}
                    </h1>

                    <div class="flex items-center space-x-4 text-sm text-neutral-400 mb-6">

                        <div v-if="product.rating > 0" class="flex items-center text-yellow-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-4 h-4">
                                <path fill-rule="evenodd"
                                    d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="ml-1 text-white font-medium">{{ product.rating }}</span>
                        </div>

                        <div v-if="product.rating > 0 && product.sold_count > 0" class="w-px h-4 bg-white/20"></div>

                        <span v-if="product.sold_count > 0" class="text-neutral-300">{{ product.sold_count }}
                            Terjual</span>
                        <span v-else class="text-neutral-500 italic">Belum ada penilaian</span>

                    </div>

                    <div class="bg-[#242426] rounded-xl p-5 mb-8 border border-white/5">
                        <div class="text-sm text-neutral-400 mb-1 line-through">Rp {{ (product.price *
                            1.2).toLocaleString('id-ID') }}</div>
                        <div class="flex items-end space-x-3">
                            <p class="text-4xl font-bold text-indigo-400">
                                Rp {{ product.price.toLocaleString('id-ID') }}
                            </p>
                            <span class="bg-indigo-500/20 text-indigo-300 text-xs font-bold px-2 py-1 rounded">Diskon
                                20%</span>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8 text-sm">
                        <div class="flex items-start">
                            <span class="w-24 text-neutral-500">Pengiriman</span>
                            <div class="flex items-center text-neutral-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-indigo-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                <span>Gratis Ongkir ke <strong>Depok</strong></span>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="w-24 text-neutral-500">Kondisi</span>
                            <span
                                class="text-neutral-300 bg-white/5 px-2 py-0.5 rounded border border-white/10 uppercase text-xs font-bold">{{
                                    product.condition }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-24 text-neutral-500">Stok</span>
                            <span class="text-white font-medium">{{ product.stock }} Tersisa</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 mt-auto pt-6 border-t border-white/10">
                        <button
                            class="flex-1 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/50 font-bold py-3.5 px-6 rounded-lg transition-colors flex items-center justify-center space-x-2"
                            @click="addToCart(product.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Keranjang</span>
                        </button>
                        <button
                            class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 px-6 rounded-lg shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center">
                            Beli Sekarang
                        </button>
                    </div>

                </div>
            </div>

            <div class="mt-6 bg-[#1c1c1e] rounded-2xl border border-white/5 p-6 shadow-xl">

                <div class="flex items-center justify-between pb-6 border-b border-white/10 mb-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-white text-xl font-bold shadow-md">
                            {{ product.store.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ product.store.name }}</h3>
                            <p class="text-sm text-neutral-400">Aktif 5 menit yang lalu</p>
                        </div>
                    </div>
                    <Link :href="`/store/${product.store.slug}`"
                        class="px-4 py-2 text-sm font-semibold text-white border border-white/20 rounded-lg hover:bg-white/5 transition text-center">
                        Kunjungi Toko
                    </Link>
                </div>

                <div>
                    <h2
                        class="text-xl font-bold text-white mb-4 bg-[#242426] inline-block px-4 py-2 rounded-lg border border-white/5">
                        Spesifikasi & Deskripsi</h2>

                    <div class="text-neutral-300 leading-relaxed font-light prose prose-invert max-w-none text-base"
                        v-if="product.description" v-html="product.description">
                    </div>

                    <div v-else class="text-neutral-400 leading-relaxed font-light">
                        Tidak ada deskripsi rinci untuk produk ini. Namun, kami memastikan produk dikirim dalam keadaan
                        terbaik dan melalui Quality Control yang ketat.
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>