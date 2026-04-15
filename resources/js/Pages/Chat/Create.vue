<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Jika ada daftar toko/user yang bisa dichat, parsing lewat props
defineProps({
    stores: Array
});

const form = useForm({
    recipient_id: '',
    message: ''
});

const submit = () => {
    form.post('/chat'); // Sesuaikan dengan route storer-mu
};
</script>

<template>

    <Head title="Mulai Obrolan - Z-STORE" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mulai Obrolan Baru</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="recipient" class="block text-sm font-medium text-gray-700">Pilih Tujuan (Toko /
                                User)</label>
                            <select id="recipient" v-model="form.recipient_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                required>
                                <option value="" disabled>Pilih penerima pesan...</option>
                                <option v-for="store in stores" :key="store.id" :value="store.id">
                                    {{ store.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Pesan Pertama</label>
                            <textarea id="message" v-model="form.message" rows="4"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                                placeholder="Halo, apakah produk ini masih tersedia?" required></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <Link href="/chat/index" class="text-sm text-gray-600 hover:text-gray-900">Batal</Link>
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                Kirim Pesan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>