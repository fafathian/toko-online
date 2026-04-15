<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Konfirmasi Keamanan - Z-STORE" />

    <div
        class="min-h-screen bg-[#121212] flex flex-col justify-center items-center relative overflow-hidden font-sans py-12">
        <div
            class="absolute bottom-[10%] right-[-10%] w-[300px] h-[300px] bg-red-600/10 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="w-full sm:max-w-md px-6 z-10">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black text-white tracking-tighter">Z-STORE.</h1>
            </div>

            <div class="bg-[#1c1c1e] px-8 py-10 rounded-3xl border border-white/5 shadow-2xl">

                <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8 text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>

                <p class="text-sm text-neutral-400 leading-relaxed text-center mb-6">
                    Ini adalah area yang aman dari aplikasi. Harap konfirmasi password Anda sebelum melanjutkan.
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <input v-model="form.password" type="password" required autofocus
                            autocomplete="current-password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 transition-all placeholder-neutral-600 text-center"
                            placeholder="Masukkan Password Anda">
                        <div v-if="form.errors.password" class="text-red-400 text-xs mt-2 text-center">{{
                            form.errors.password }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-red-600/20 disabled:opacity-50">
                        Konfirmasi Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>