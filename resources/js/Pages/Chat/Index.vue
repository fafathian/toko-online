<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Sesuaikan jika namamu AppLayout.vue

defineProps({
    rooms: Array
});

// Fungsi untuk mendapatkan nama lawan bicara berdasarkan role
const getChatName = (room) => {
    if (room.role === 'buyer') {
        // Jika kita pembeli, tampilkan nama toko
        return room.store?.name || 'Toko Tidak Diketahui';
    } else {
        // Jika kita penjual, tampilkan nama pembeli
        return room.buyer?.name || 'Pelanggan';
    }
};

// Fungsi untuk mengambil huruf pertama dari nama lawan bicara
const getChatInitial = (room) => {
    const name = getChatName(room);
    return name ? name.charAt(0).toUpperCase() : '?';
};

// Fungsi untuk mendapatkan teks pesan terakhir
const getLatestMessage = (room) => {
    return room.latest_message?.message || 'Mulai obrolan baru...';
};

// Fungsi format waktu sederhana
const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>

    <Head title="Pesan - Z-STORE" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kotak Masuk</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Daftar Obrolan</h3>
                        </div>

                        <div class="divide-y divide-gray-200">
                            <div v-if="!rooms || rooms.length === 0" class="py-8 text-center text-gray-500">
                                Belum ada obrolan saat ini.
                            </div>

                            <Link v-for="room in rooms" :key="room.id" :href="`/chat/${room.id}`"
                                class="flex items-center justify-between py-4 px-2 hover:bg-gray-50 transition duration-150 ease-in-out rounded-lg cursor-pointer">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 relative">
                                        <div
                                            class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold">
                                            {{ getChatInitial(room) }}
                                        </div>
                                        <div v-if="room.unread_count > 0"
                                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                                            {{ room.unread_count }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate flex items-center gap-2">
                                            {{ getChatName(room) }}
                                            <span v-if="room.role === 'seller'"
                                                class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-semibold">
                                                Toko Saya
                                            </span>
                                        </p>
                                        <p class="text-sm text-gray-500 truncate"
                                            :class="{ 'font-bold text-gray-800': room.unread_count > 0 }">
                                            {{ getLatestMessage(room) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-xs text-gray-400">
                                        {{ formatTime(room.latest_message?.created_at || room.updated_at) }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>