<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Masuk - Z-STORE" />

    <div class="min-h-screen bg-[#121212] flex flex-col justify-center items-center relative overflow-hidden font-sans">

        <div
            class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-indigo-600/20 blur-[120px] rounded-full pointer-events-none">
        </div>
        <div
            class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-600/10 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="w-full sm:max-w-md px-6 py-8 z-10">
            <div class="text-center mb-10">
                <Link href="/" class="text-4xl font-black text-white tracking-tighter">Z-STORE.</Link>
                <p class="text-neutral-400 mt-2 text-sm">Selamat datang kembali, silakan masuk ke akun Anda.</p>
            </div>

            <div v-if="status"
                class="mb-6 font-medium text-sm text-green-400 bg-green-400/10 p-4 rounded-xl border border-green-400/20">
                {{ status }}
            </div>

            <div class="bg-[#1c1c1e] px-8 py-10 rounded-3xl border border-white/5 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-6">

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Email</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="nama@email.com">
                        <div v-if="form.errors.email" class="text-red-400 text-xs mt-2">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-neutral-300">Password</label>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-xs font-medium text-indigo-400 hover:text-white transition-colors">
                                Lupa password?
                            </Link>
                        </div>
                        <input v-model="form.password" type="password" required autocomplete="current-password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="••••••••">
                        <div v-if="form.errors.password" class="text-red-400 text-xs mt-2">{{ form.errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.remember"
                                class="w-5 h-5 rounded border-white/10 bg-[#242426] text-indigo-600 focus:ring-indigo-500/50 focus:ring-offset-[#1c1c1e] transition-all">
                            <span class="ml-3 text-sm text-neutral-400">Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 disabled:opacity-50 mt-4">
                        Masuk
                    </button>
                </form>

                <div class="mt-8 text-center text-sm text-neutral-500">
                    Belum punya akun?
                    <Link :href="route('register')"
                        class="text-indigo-400 hover:text-white font-bold transition-colors">Daftar sekarang</Link>
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