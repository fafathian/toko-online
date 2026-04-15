<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>

    <Head title="Lupa Password - Z-STORE" />

    <div
        class="min-h-screen bg-[#121212] flex flex-col justify-center items-center relative overflow-hidden font-sans py-12">

        <div
            class="absolute top-[10%] left-[-10%] w-[400px] h-[400px] bg-indigo-600/20 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="w-full sm:max-w-md px-6 z-10">
            <div class="text-center mb-8">
                <Link href="/" class="text-4xl font-black text-white tracking-tighter">Z-STORE.</Link>
                <p class="text-neutral-400 mt-4 text-sm leading-relaxed px-4">
                    Lupa password Anda? Tidak masalah. Beritahu kami alamat email Anda dan kami akan mengirimkan tautan
                    untuk mengatur ulang password.
                </p>
            </div>

            <div v-if="status"
                class="mb-6 font-medium text-sm text-green-400 bg-green-400/10 p-4 rounded-xl border border-green-400/20 text-center shadow-lg">
                {{ status }}
            </div>

            <div class="bg-[#1c1c1e] px-8 py-10 rounded-3xl border border-white/5 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-6">

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Alamat Email Terdaftar</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="nama@email.com">
                        <div v-if="form.errors.email" class="text-red-400 text-xs mt-2">{{ form.errors.email }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 disabled:opacity-50 mt-2 flex justify-center items-center gap-2">
                        <span v-if="form.processing">Mengirim...</span>
                        <span v-else>Kirim Link Reset Password</span>
                    </button>
                </form>

                <div class="mt-8 text-center text-sm text-neutral-500">
                    Teringat password Anda?
                    <Link :href="route('login')" class="text-indigo-400 hover:text-white font-bold transition-colors">
                        Kembali ke Login</Link>
                </div>
            </div>

        </div>
    </div>
</template>