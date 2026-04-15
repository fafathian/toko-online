<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Reset Password - Z-STORE" />

    <div
        class="min-h-screen bg-[#121212] flex flex-col justify-center items-center relative overflow-hidden font-sans py-12">
        <div
            class="absolute top-[20%] right-[-10%] w-[400px] h-[400px] bg-purple-600/20 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="w-full sm:max-w-md px-6 z-10">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black text-white tracking-tighter">Z-STORE.</h1>
                <p class="text-neutral-400 mt-4 text-sm leading-relaxed px-4">
                    Buat password baru untuk akun Anda. Pastikan password ini kuat dan mudah diingat.
                </p>
            </div>

            <div class="bg-[#1c1c1e] px-8 py-10 rounded-3xl border border-white/5 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Email Terdaftar</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username" readonly
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-neutral-400 focus:outline-none opacity-70 cursor-not-allowed">
                        <div v-if="form.errors.email" class="text-red-400 text-xs mt-2">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Password Baru</label>
                        <input v-model="form.password" type="password" required autocomplete="new-password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="Minimal 8 karakter">
                        <div v-if="form.errors.password" class="text-red-400 text-xs mt-2">{{ form.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Konfirmasi Password Baru</label>
                        <input v-model="form.password_confirmation" type="password" required autocomplete="new-password"
                            class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-neutral-600"
                            placeholder="Ulangi password">
                        <div v-if="form.errors.password_confirmation" class="text-red-400 text-xs mt-2">{{
                            form.errors.password_confirmation }}</div>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 disabled:opacity-50 mt-6 flex justify-center items-center gap-2">
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Simpan Password Baru</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>