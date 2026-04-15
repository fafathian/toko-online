<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';

const props = defineProps({
    products: Object,
    stores: Array,
    categories: Array, // TAMBAHAN: Menerima data kategori dari backend
    filters: Object
});

// State untuk form pencarian
const search = ref(props.filters.search || '');
const selectedStore = ref(props.filters.store || '');
const selectedSort = ref(props.filters.sort);
const selectedCategory = ref(props.filters.category || ''); // TAMBAHAN: State Kategori

// Menangkap cart count untuk Navbar
const page = usePage();
const cartCount = computed(() => page.props.cartCount);

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

// Fungsi pencarian
const fetchProducts = () => {
    router.get('/catalog', {
        search: search.value,
        store: selectedStore.value,
        sort: selectedSort.value,
        category: selectedCategory.value // TAMBAHAN: Kirim slug kategori ke server
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Menggunakan Watcher (Termasuk selectedCategory)
watch([selectedStore, selectedSort, selectedCategory], () => {
    fetchProducts();
});

const showUserMenu = ref(false);
</script>

<template>

    <Head title="Katalog Global - Z-STORE" />

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

        <div class="w-full bg-gradient-to-b from-[#1c1c1e] to-[#121212] pt-12 pb-8 border-b border-white/5">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
                <h1 class="text-4xl md:text-5xl font-black text-white mb-6 tracking-tight">Temukan Gaya Terbaikmu.</h1>

                <form @submit.prevent="fetchProducts" class="max-w-2xl mx-auto relative flex items-center">
                    <div class="absolute left-4 text-neutral-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input v-model="search" type="text" placeholder="Cari sepatu, kemeja, tas..."
                        class="w-full bg-[#242426] border border-white/10 rounded-full py-4 pl-14 pr-32 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all text-lg">
                    <button type="submit"
                        class="absolute right-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-6 rounded-full transition-colors">Cari</button>
                </form>
            </div>
        </div>

        <main class="max-w-6xl mx-auto px-4 sm:px-6 mt-6">

            <div v-if="categories && categories.length > 0"
                class="mb-6 overflow-x-auto pb-2 flex space-x-3 [&::-webkit-scrollbar]:hidden">
                <button @click="selectedCategory = ''"
                    :class="selectedCategory === '' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-[#1c1c1e] text-neutral-400 border-white/10 hover:text-white hover:border-white/30'"
                    class="whitespace-nowrap px-4 py-2 rounded-full border text-sm font-medium transition-colors">
                    Semua Kategori
                </button>

                <button v-for="category in categories" :key="category.slug" @click="selectedCategory = category.slug"
                    :class="selectedCategory === category.slug ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-[#1c1c1e] text-neutral-400 border-white/10 hover:text-white hover:border-white/30'"
                    class="whitespace-nowrap px-4 py-2 rounded-full border text-sm font-medium transition-colors flex items-center space-x-2">
                    <i v-if="category.icon" :class="category.icon"></i>
                    <span>{{ category.name }}</span>
                </button>
            </div>

            <div
                class="flex flex-col md:flex-row justify-between items-center bg-[#1c1c1e] p-4 rounded-2xl border border-white/5 mb-8 gap-4 shadow-lg">
                <div class="text-neutral-400 text-sm font-medium">
                    Menampilkan <strong class="text-white">{{ products.total }}</strong> produk
                </div>

                <div class="flex items-center space-x-3 w-full md:w-auto">
                    <select v-model="selectedStore"
                        class="flex-1 md:w-48 bg-[#242426] border border-white/10 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 outline-none cursor-pointer">
                        <option value="">Semua Toko</option>
                        <option v-for="store in stores" :key="store.slug" :value="store.slug">{{ store.name }}</option>
                    </select>

                    <select v-model="selectedSort"
                        class="flex-1 md:w-48 bg-[#242426] border border-white/10 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 outline-none cursor-pointer">
                        <option value="latest">Paling Baru</option>
                        <option value="termurah">Harga: Termurah</option>
                        <option value="termahal">Harga: Termahal</option>
                    </select>
                </div>
            </div>

            <div v-if="products.data.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <Link :href="`/products/${product.slug}`" v-for="product in products.data" :key="product.id"
                    class="bg-[#1c1c1e] rounded-2xl border border-white/5 overflow-hidden hover:border-indigo-500/50 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 group flex flex-col">

                    <div class="aspect-[4/5] bg-[#242426] relative overflow-hidden">
                        <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                        <div
                            class="absolute top-2 left-2 bg-black/60 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded border border-white/10 uppercase tracking-wider">
                            {{ product.condition }}
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <div
                                class="text-[10px] md:text-[11px] font-bold text-indigo-400 uppercase tracking-wider line-clamp-1">
                                {{ product.store.name }}
                            </div>
                            <div v-if="product.category"
                                class="text-[9px] md:text-[10px] bg-white/5 border border-white/10 text-neutral-400 px-1.5 py-0.5 rounded uppercase font-semibold">
                                {{ product.category.name }}
                            </div>
                        </div>

                        <h3
                            class="text-sm md:text-base font-medium text-neutral-200 line-clamp-2 leading-tight mb-2 group-hover:text-indigo-300 transition-colors">
                            {{ product.name }}
                        </h3>

                        <div class="mt-auto pt-2 flex flex-col space-y-1.5">
                            <p class="font-bold text-lg text-white">Rp {{ product.price.toLocaleString('id-ID') }}</p>
                            <div class="flex items-center text-xs text-neutral-500 space-x-2">
                                <span v-if="product.rating > 0" class="flex items-center text-yellow-500">★ {{
                                    product.rating }}</span>
                                <span v-if="product.rating > 0 && product.sold_count > 0">•</span>
                                <span v-if="product.sold_count > 0">{{ product.sold_count }} terjual</span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-32 bg-[#1c1c1e] rounded-3xl border border-white/5">
                <div class="text-6xl mb-4">🕵️‍♂️</div>
                <h2 class="text-2xl font-bold text-white mb-2">Produk Tidak Ditemukan</h2>
                <p class="text-neutral-400">Coba gunakan kata kunci pencarian yang lain atau hapus filter.</p>
                <button
                    @click="search = ''; selectedStore = ''; selectedCategory = ''; selectedSort = 'latest'; fetchProducts()"
                    class="mt-6 px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors">
                    Reset Filter
                </button>
            </div>

            <div v-if="products.last_page > 1" class="flex justify-center mt-12 gap-2">
                <Link v-for="link in products.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                    class="px-4 py-2 rounded-lg border text-sm transition-colors"
                    :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white font-bold' : 'bg-[#1c1c1e] border-white/10 text-neutral-400 hover:text-white hover:border-white/30'"
                    :preserve-scroll="true" />
            </div>

        </main>
    </div>
</template>