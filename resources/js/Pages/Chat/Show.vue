<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    room: Object,
    messages: Array,
    role: String,
});

const chatMessages = ref([...props.messages]);
const newMessage = ref('');
const messageContainer = ref(null);
const currentUser = usePage().props.auth.user;

const chatPartnerName = computed(() => {
    return props.role === 'buyer'
        ? props.room.store?.name || 'Toko'
        : props.room.buyer?.name || 'Pelanggan';
});

const chatPartnerInitial = computed(() => {
    return chatPartnerName.value.charAt(0).toUpperCase();
});

const scrollToBottom = async () => {
    await nextTick();
    if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
};

const sendMessage = () => {
    if (newMessage.value.trim() === '') return;

    const tempMessage = {
        id: 'temp-' + Date.now(),
        user_id: currentUser.id,
        message: newMessage.value,
        type: 'text',
        created_at: new Date().toISOString()
    };

    chatMessages.value.push(tempMessage);

    const payload = { message: newMessage.value };
    newMessage.value = '';
    scrollToBottom();

    axios.post(`/chat/${props.room.id}/send`, payload)
        .catch(error => {
            console.error("Gagal mengirim pesan:", error);
        });
};

onMounted(() => {
    scrollToBottom();

    if (window.Echo) {
        console.log(`⏳ Mencoba terhubung ke Websocket: chat.${props.room.id}`);

        window.Echo.private(`chat.${props.room.id}`)
            .subscribed(() => {
                console.log("✅ BERHASIL: Terhubung ke Reverb / Room Chat!");
            })
            .error((error) => {
                console.error("❌ GAGAL: Tidak bisa masuk ke Room Chat.", error);
            })
            .listen('.MessageSent', (e) => {
                console.log("📥 PESAN MASUK:", e);

                // PERBAIKAN DI SINI:
                // Gunakan 'e' langsung, bukan 'e.message'
                if (e && e.user_id !== currentUser.id) {
                    chatMessages.value.push(e); // Langsung masukkan objek e
                    scrollToBottom();
                }
            })
            // Hapus yang listen('MessageSent') format 2 agar tidak dobel
            ;
    }
});

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>

    <Head :title="`Chat: ${chatPartnerName} - Z-STORE`" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg flex flex-col h-[75vh] border border-gray-200">

                    <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between rounded-t-lg">
                        <div class="flex items-center space-x-4">

                            <Link href="/chat"
                                class="p-2 bg-white border border-gray-300 rounded-full hover:bg-gray-100 transition shadow-sm"
                                title="Kembali ke Daftar Chat">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-700">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                </svg>
                            </Link>

                            <div
                                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold text-lg shadow-sm">
                                {{ chatPartnerInitial }}
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 flex items-center gap-2">
                                    {{ chatPartnerName }}
                                    <span v-if="role === 'seller'"
                                        class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">
                                        Toko Anda
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500" v-if="room.order_id">
                                    Terkait Pesanan: <span class="font-semibold text-indigo-600">#ORD-{{ room.order_id
                                    }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div ref="messageContainer" class="flex-1 p-4 overflow-y-auto bg-[#F9FAFB] space-y-4">
                        <div v-if="chatMessages.length === 0" class="flex justify-center items-center h-full">
                            <p
                                class="text-gray-400 text-sm bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100">
                                Belum ada pesan. Mulai sapa sekarang!</p>
                        </div>

                        <div v-for="msg in chatMessages" :key="msg.id"
                            :class="['flex w-full', msg.user_id === currentUser.id ? 'justify-end' : 'justify-start']">
                            <div class="flex flex-col space-y-1"
                                :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'">
                                <div :class="[
                                    'max-w-[85%] sm:max-w-md px-4 py-2 shadow-sm relative',
                                    msg.user_id === currentUser.id
                                        ? 'bg-indigo-600 text-white rounded-2xl rounded-br-none'
                                        : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-none'
                                ]">
                                    <p class="text-[15px] leading-relaxed whitespace-pre-wrap break-words">{{
                                        msg.message }}</p>
                                </div>
                                <span class="text-[11px] text-gray-400 px-1">
                                    {{ formatTime(msg.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border-t border-gray-200 bg-white rounded-b-lg">
                        <form @submit.prevent="sendMessage" class="flex items-end space-x-2">
                            <div class="flex-1">
                                <textarea v-model="newMessage" @keydown.enter.exact.prevent="sendMessage" rows="1"
                                    class="w-full resize-none rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-3 text-sm shadow-sm"
                                    placeholder="Ketik pesan... (Tekan Enter untuk kirim)"></textarea>
                            </div>
                            <button type="submit" :disabled="!newMessage.trim()"
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-5 h-5 translate-x-px translate-y-px">
                                    <path
                                        d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>