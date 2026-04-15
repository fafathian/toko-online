<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});


const user = usePage().props.auth.user;
const cartCount = computed(() => usePage().props.cartCount);

// 1. Form Profil
const profileForm = useForm({
    name: user.name,
    email: user.email,
});

// 2. Form Password
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordForm.clearErrors('current_password');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

// 3. Form Hapus Akun & Modal
const confirmingUserDeletion = ref(false);
const deleteForm = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => deleteForm.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    deleteForm.clearErrors();
    deleteForm.reset();
};

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

    <Head title="Pengaturan Profil - Z-STORE" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 font-sans pb-24">

        <nav
            class="sticky top-0 w-full p-4 md:p-6 flex justify-between items-center z-40 bg-[#121212]/90 backdrop-blur-md border-b border-white/10 shadow-sm">
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

        <main class="max-w-4xl mx-auto px-4 sm:px-6 mt-10 space-y-8">

            <header class="mb-8">
                <h1 class="text-3xl font-bold text-white">Pengaturan Akun</h1>
                <p class="text-neutral-400 mt-2">Kelola informasi profil dan keamanan akun Anda.</p>
            </header>

            <section class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-white/5 shadow-xl">
                <h2 class="text-xl font-semibold text-white mb-1">Informasi Profil</h2>
                <p class="text-sm text-neutral-400 mb-6">Perbarui nama dan alamat email akun Anda.</p>

                <form @submit.prevent="profileForm.patch(route('profile.update'))" class="space-y-6 max-w-xl">
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Nama Lengkap</label>
                        <input v-model="profileForm.name" type="text"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                            required autofocus autocomplete="name" />
                        <div v-if="profileForm.errors.name" class="text-red-400 text-sm mt-2">{{ profileForm.errors.name
                            }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Alamat Email</label>
                        <input v-model="profileForm.email" type="email"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                            required autocomplete="username" />
                        <div v-if="profileForm.errors.email" class="text-red-400 text-sm mt-2">{{
                            profileForm.errors.email }}</div>
                    </div>

                    <div class="flex items-center space-x-4 pt-2">
                        <button type="submit" :disabled="profileForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-6 rounded-xl transition-colors disabled:opacity-50">
                            Simpan Perubahan
                        </button>
                        <transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-if="profileForm.recentlySuccessful" class="text-sm text-green-400 font-medium">Berhasil
                                disimpan.</p>
                        </transition>
                    </div>
                </form>
            </section>

            <section class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-white/5 shadow-xl">
                <h2 class="text-xl font-semibold text-white mb-1">Perbarui Password</h2>
                <p class="text-sm text-neutral-400 mb-6">Pastikan akun Anda menggunakan password panjang dan acak agar
                    tetap aman.</p>

                <form @submit.prevent="updatePassword" class="space-y-6 max-w-xl">
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Password Saat Ini</label>
                        <input v-model="passwordForm.current_password" type="password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                            required autocomplete="current-password" />
                        <div v-if="passwordForm.errors.current_password" class="text-red-400 text-sm mt-2">{{
                            passwordForm.errors.current_password }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Password Baru</label>
                        <input v-model="passwordForm.password" type="password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                            required autocomplete="new-password" />
                        <div v-if="passwordForm.errors.password" class="text-red-400 text-sm mt-2">{{
                            passwordForm.errors.password }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Konfirmasi Password Baru</label>
                        <input v-model="passwordForm.password_confirmation" type="password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                            required autocomplete="new-password" />
                        <div v-if="passwordForm.errors.password_confirmation" class="text-red-400 text-sm mt-2">{{
                            passwordForm.errors.password_confirmation }}</div>
                    </div>

                    <div class="flex items-center space-x-4 pt-2">
                        <button type="submit" :disabled="passwordForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-6 rounded-xl transition-colors disabled:opacity-50">
                            Perbarui Password
                        </button>
                        <transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-400 font-medium">
                                Password berhasil diubah.</p>
                        </transition>
                    </div>
                </form>
            </section>

            <section class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-red-500/20 shadow-xl">
                <h2 class="text-xl font-semibold text-red-400 mb-1">Hapus Akun</h2>
                <p class="text-sm text-neutral-400 mb-6 max-w-xl">Sekali akun Anda dihapus, semua sumber daya dan
                    datanya akan dihapus secara permanen. Harap unduh data atau informasi yang ingin Anda simpan sebelum
                    menghapus akun.</p>

                <button @click="confirmUserDeletion"
                    class="bg-red-500/10 hover:bg-red-500/20 border border-red-500/50 text-red-400 font-semibold py-3 px-6 rounded-xl transition-colors">
                    Hapus Akun Permanen
                </button>
            </section>

        </main>

        <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="closeModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>

            <div
                class="relative bg-[#1c1c1e] border border-white/10 rounded-2xl p-6 md:p-8 w-full max-w-lg shadow-2xl z-10 transform transition-all">
                <h2 class="text-2xl font-bold text-white mb-4">Apakah Anda yakin?</h2>
                <p class="text-neutral-400 mb-6">Sekali akun dihapus, semua data akan hilang permanen. Silakan masukkan
                    password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun ini secara permanen.</p>

                <div class="mb-6">
                    <input v-model="deleteForm.password" type="password" placeholder="Masukkan Password Anda"
                        class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 transition-all"
                        @keyup.enter="deleteUser" />
                    <div v-if="deleteForm.errors.password" class="text-red-400 text-sm mt-2">{{
                        deleteForm.errors.password }}</div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="closeModal"
                        class="px-6 py-3 text-neutral-300 bg-white/5 hover:bg-white/10 rounded-xl transition-colors font-medium">
                        Batal
                    </button>
                    <button @click="deleteUser" :disabled="deleteForm.processing"
                        class="px-6 py-3 bg-red-600 hover:bg-red-500 text-white rounded-xl transition-colors font-medium disabled:opacity-50">
                        Hapus Akun
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>