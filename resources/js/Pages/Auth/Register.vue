<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Daftar Akun - Z-STORE" />

    <div
        class="min-h-screen bg-[#121212] flex flex-col justify-center items-center relative overflow-hidden font-sans py-12">

        <div
            class="absolute top-[20%] right-[-10%] w-[500px] h-[500px] bg-indigo-600/10 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="w-full sm:max-w-md px-6 z-10">
            <div class="text-center mb-8">
                <Link href="/" class="text-4xl font-black text-white tracking-tighter">Z-STORE.</Link>
                <p class="text-neutral-400 mt-2 text-sm">Bergabunglah dan mulai petualangan belanjamu.</p>
            </div>

            <div class="bg-[#1c1c1e] px-8 py-10 rounded-3xl border border-white/5 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Nama Lengkap</label>
                        <input v-model="form.name" type="text" required autofocus autocomplete="name"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="Zulfa Fathian Yahya">
                        <div v-if="form.errors.name" class="text-red-400 text-xs mt-2">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Email</label>
                        <input v-model="form.email" type="email" required autocomplete="username"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="nama@email.com">
                        <div v-if="form.errors.email" class="text-red-400 text-xs mt-2">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Password</label>
                        <input v-model="form.password" type="password" required autocomplete="new-password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="Minimal 8 karakter">
                        <div v-if="form.errors.password" class="text-red-400 text-xs mt-2">{{ form.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Konfirmasi Password</label>
                        <input v-model="form.password_confirmation" type="password" required autocomplete="new-password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="Ulangi password">
                        <div v-if="form.errors.password_confirmation" class="text-red-400 text-xs mt-2">{{
                            form.errors.password_confirmation }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 disabled:opacity-50 mt-6">
                        Buat Akun
                    </button>
                </form>

                <div class="mt-8 text-center text-sm text-neutral-500">
                    Sudah punya akun?
                    <Link :href="route('login')" class="text-indigo-400 hover:text-white font-bold transition-colors">
                        Masuk di sini</Link>
                </div>
            </div>

            <div class="mt-8 text-center">
                <Link href="/"
                    class="text-sm text-neutral-500 hover:text-white transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Beranda
                </Link>
            </div>
        </div>
    </div>
</template>