<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>

    <Head title="Verifikasi Email - Z-STORE" />

    <div
        class="min-h-screen bg-[#121212] flex flex-col justify-center items-center relative overflow-hidden font-sans py-12">
        <div
            class="absolute top-[30%] left-[-10%] w-[300px] h-[300px] bg-indigo-600/20 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="w-full sm:max-w-md px-6 z-10">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-black text-white tracking-tighter">Z-STORE.</h1>
            </div>

            <div class="bg-[#1c1c1e] px-8 py-10 rounded-3xl border border-white/5 shadow-2xl">

                <div class="w-16 h-16 bg-indigo-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>

                <p class="text-sm text-neutral-400 leading-relaxed text-center mb-6">
                    Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan
                    mengklik tautan yang baru saja kami kirimkan melalui email? Jika Anda tidak menerima email tersebut,
                    kami akan dengan senang hati mengirimkan tautan yang lain.
                </p>

                <div v-if="verificationLinkSent"
                    class="mb-6 font-medium text-sm text-green-400 bg-green-400/10 p-4 rounded-xl border border-green-400/20 text-center">
                    Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-center">
                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-600/20 disabled:opacity-50 flex justify-center items-center gap-2">
                        <span v-if="form.processing">Mengirim...</span>
                        <span v-else>Kirim Ulang Email Verifikasi</span>
                    </button>

                    <Link :href="route('logout')" method="post" as="button"
                        class="inline-block mt-4 text-sm text-neutral-500 hover:text-white underline transition-colors">
                        Keluar (Logout)
                    </Link>
                </form>
            </div>
        </div>
    </div>
</template>