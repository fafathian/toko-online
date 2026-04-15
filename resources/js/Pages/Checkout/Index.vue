<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    checkoutData: Array, // Data yang sudah di-group per toko
    totalBelanja: Number,
    provinces: Array,
});

const selectedProvince = ref('');
const selectedCity = ref('');
const selectedDistrict = ref('');
const cities = ref([]);
const districts = ref([]);

// State Ongkir Multi-Toko
const shippingRates = ref({}); // Menyimpan daftar kurir per store_id
const isCalculating = ref({}); // Menyimpan status loading per store_id

const form = useForm({
    shipping_address: '',
    province_id: '',
    city_id: '',
    district_id: '',
    postal_code: '',
    shippings: {}, // Format: { store_id: { courier: 'JNE', cost: 15000 } }
});

// Hitung total ongkir dari semua toko yang sudah dipilih
const totalShippingCost = computed(() => {
    let total = 0;
    for (const storeId in form.shippings) {
        total += form.shippings[storeId].cost || 0;
    }
    return total;
});

const totalTagihan = computed(() => props.totalBelanja + totalShippingCost.value);

// Cek apakah semua toko sudah dipilih kurirnya
const isAllShippingSelected = computed(() => {
    if (!props.checkoutData) return false;
    const selectedCount = Object.keys(form.shippings).length;
    return selectedCount === props.checkoutData.length;
});

const handleProvinceChange = () => {
    if (selectedProvince.value) {
        form.province_id = selectedProvince.value;
        selectedCity.value = '';
        selectedDistrict.value = '';
        cities.value = [];
        districts.value = [];
        resetShippings();
        axios.get('/api/cities/' + selectedProvince.value).then(res => { cities.value = res.data; });
    }
};

const handleCityChange = () => {
    if (selectedCity.value) {
        form.city_id = selectedCity.value;
        selectedDistrict.value = '';
        districts.value = [];
        resetShippings();
        form.postal_code = '';
        axios.get('/api/districts/' + selectedCity.value).then(res => { districts.value = res.data; });
    }
};

const onDistrictSelect = () => {
    form.district_id = selectedDistrict.value;
    const found = districts.value.find(d => d.id === selectedDistrict.value);

    resetShippings();

    if (found && found.postal_code) {
        form.postal_code = found.postal_code;
        checkAllShippings();
    } else {
        form.postal_code = '';
    }
};

watch(() => form.postal_code, (newVal) => {
    if (newVal && newVal.toString().length >= 5) {
        checkAllShippings();
    } else {
        resetShippings();
    }
});

const resetShippings = () => {
    shippingRates.value = {};
    form.shippings = {};
};

// Hitung ongkir untuk SETIAP toko
const checkAllShippings = () => {
    if (!form.postal_code) return;

    props.checkoutData.forEach(storeData => {
        // Set loading khusus untuk toko ini
        isCalculating.value[storeData.store_id] = true;
        shippingRates.value[storeData.store_id] = [];

        axios.post(route('checkout.calculateShipping'), {
            postal_code: form.postal_code,
            store_id: storeData.store_id
        })
            .then(res => {
                if (res.data.success && res.data.data.length > 0) {
                    shippingRates.value[storeData.store_id] = res.data.data;
                }
            })
            .catch(err => console.error(`Shipping error untuk toko ${storeData.store_id}:`, err))
            .finally(() => {
                isCalculating.value[storeData.store_id] = false;
            });
    });
};

const selectPackage = (storeId, rate) => {
    // Simpan pilihan kurir spesifik ke store_id tersebut
    form.shippings[storeId] = {
        courier: `${rate.courier_name} (${rate.courier_service_name})`,
        cost: Number(rate.price)
    };
};

const submitCheckout = () => {
    if (!isAllShippingSelected.value) {
        alert("Silakan pilih layanan pengiriman untuk SEMUA toko terlebih dahulu!");
        return;
    }

    form.post('/checkout', {
        preserveScroll: true,
        onError: (errors) => console.error("Gagal Checkout:", errors)
    });
};

const page = usePage();
</script>

<template>

    <Head title="Checkout - Z-STORE" />

    <div class="min-h-screen bg-[#121212] text-neutral-200 pb-24">
        <nav
            class="sticky top-0 w-full p-4 md:p-6 flex justify-between items-center z-50 bg-[#121212]/90 backdrop-blur-md border-b border-white/10 shadow-sm">
            <Link href="/" class="text-2xl font-black tracking-tighter text-white">Z-STORE.</Link>
        </nav>

        <main class="max-w-6xl mx-auto px-4 mt-8">
            <h1 class="text-3xl font-bold text-white mb-8">Pengiriman & Pembayaran</h1>

            <form @submit.prevent="submitCheckout" class="flex flex-col lg:flex-row gap-8">
                <div class="w-full lg:w-2/3 space-y-6">
                    <div class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-white/5 shadow-xl">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2 text-indigo-400">
                            Alamat Pengiriman
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-400 mb-2">Alamat Lengkap</label>
                                <textarea v-model="form.shipping_address" rows="3" required
                                    class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500/50 transition-all resize-none"
                                    placeholder="Nama Jalan, No Rumah, RT/RW"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-400 mb-2">Provinsi</label>
                                    <select v-model="selectedProvince" @change="handleProvinceChange" required
                                        class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500/50">
                                        <option value="" disabled>Pilih Provinsi</option>
                                        <option v-for="prov in provinces" :key="prov.id" :value="prov.id">{{ prov.name
                                            }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-neutral-400 mb-2">Kota/Kabupaten</label>
                                    <select v-model="selectedCity" @change="handleCityChange" required
                                        class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500/50">
                                        <option value="" disabled>Pilih Kota</option>
                                        <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-400 mb-2">Kecamatan</label>
                                    <select v-model="selectedDistrict" @change="onDistrictSelect" required
                                        class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500/50">
                                        <option value="" disabled>Pilih Kecamatan</option>
                                        <option v-for="dist in districts" :key="dist.id" :value="dist.id">{{ dist.name
                                            }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-neutral-400 mb-2">Kode Pos Tujuan</label>
                                <input type="number" v-model="form.postal_code" placeholder="Masukkan 5 digit kode pos"
                                    required
                                    class="w-full bg-[#242426] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500/50">
                            </div>
                        </div>
                    </div>

                    <div v-for="storeData in checkoutData" :key="storeData.store_id"
                        class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-white/5 shadow-xl">
                        <div class="flex items-center gap-3 mb-6 border-b border-white/5 pb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h2 class="text-lg font-bold text-white uppercase tracking-wider">{{ storeData.store_name }}
                            </h2>
                        </div>

                        <div class="space-y-4 mb-8">
                            <div v-for="item in storeData.items" :key="item.id"
                                class="flex gap-4 p-4 bg-[#242426]/50 rounded-xl border border-white/5">
                                <div class="w-16 h-16 bg-[#121212] rounded-lg overflow-hidden flex-shrink-0">
                                    <img :src="item.product?.image_path ? `/storage/${item.product.image_path}` : '/images/placeholder.jpg'"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-white text-sm font-medium line-clamp-1">{{ item.product?.name }}
                                    </h3>
                                    <p class="text-xs text-neutral-400 mt-1">{{ item.quantity }} barang x Rp {{
                                        item.product?.price?.toLocaleString('id-ID') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-white text-sm font-bold">Rp {{ (item.product?.price *
                                        item.quantity).toLocaleString('id-ID') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#242426] rounded-xl p-4 border border-white/5">
                            <h3 class="text-sm font-bold text-indigo-400 mb-4">Pilih Pengiriman dari {{
                                storeData.store_name }}</h3>

                            <div v-if="!form.postal_code || form.postal_code.toString().length < 5"
                                class="text-sm text-neutral-500 italic text-center py-4">
                                Masukkan kode pos tujuan untuk melihat tarif pengiriman.
                            </div>

                            <div v-else-if="isCalculating[storeData.store_id]"
                                class="flex justify-center items-center py-6">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-500"></div>
                            </div>

                            <div v-else-if="shippingRates[storeData.store_id]?.length > 0"
                                class="space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="(rate, index) in shippingRates[storeData.store_id]" :key="index"
                                    @click="selectPackage(storeData.store_id, rate)"
                                    class="flex justify-between items-center p-3 rounded-xl border cursor-pointer transition-all"
                                    :class="form.shippings[storeData.store_id]?.courier === `${rate.courier_name} (${rate.courier_service_name})` ? 'border-indigo-500 bg-indigo-500/20' : 'border-white/10 hover:border-white/30'">

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-bold text-white uppercase text-[10px] px-1.5 py-0.5 bg-indigo-600 rounded">{{
                                                rate.courier_code }}</span>
                                            <p class="font-bold text-white text-sm">{{ rate.courier_service_name }}</p>
                                        </div>
                                        <p class="text-xs text-neutral-500 mt-1">Estimasi: {{ rate.duration }}</p>
                                    </div>
                                    <div class="text-indigo-400 font-bold text-sm">
                                        Rp {{ rate.price.toLocaleString('id-ID') }}
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-sm text-red-400 italic text-center py-4">
                                Tidak ada kurir yang tersedia untuk rute ini.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/3 sticky top-28 h-fit">
                    <div class="bg-[#1c1c1e] p-6 md:p-8 rounded-2xl border border-white/5 shadow-2xl space-y-6">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span>
                            Ringkasan Belanja
                        </h2>

                        <div class="space-y-4 text-sm text-neutral-400 border-b border-white/10 pb-6">
                            <div class="flex justify-between">
                                <span>Total Harga Barang</span>
                                <span class="text-white font-medium">Rp {{ totalBelanja.toLocaleString('id-ID')
                                    }}</span>
                            </div>

                            <div class="flex justify-between items-center" v-if="totalShippingCost > 0">
                                <span>Total Biaya Pengiriman</span>
                                <span class="text-indigo-400 font-bold text-base">
                                    Rp {{ totalShippingCost.toLocaleString('id-ID') }}
                                </span>
                            </div>

                            <div v-if="Object.keys(form.shippings).length > 0"
                                class="pl-4 border-l-2 border-white/5 space-y-2 mt-2">
                                <div v-for="(shipping, storeId) in form.shippings" :key="storeId"
                                    class="flex justify-between text-xs">
                                    <span class="truncate pr-2">- Toko ID {{ storeId }}: {{ shipping.courier }}</span>
                                    <span>Rp {{ shipping.cost.toLocaleString('id-ID') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-white">Total Tagihan</span>
                            <span class="text-2xl font-black text-indigo-400">
                                Rp {{ totalTagihan.toLocaleString('id-ID') }}
                            </span>
                        </div>

                        <button type="submit" :disabled="form.processing || !isAllShippingSelected"
                            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-600/30 transition-all text-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                            <span v-if="form.processing">Sedang Memproses...</span>
                            <span v-else-if="!isAllShippingSelected">Pilih Semua Kurir</span>
                            <span v-else>Bayar Sekarang</span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
/* Opsional: Membuat scrollbar custom untuk list kurir agar terlihat rapi */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1c1c1e;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4f46e5;
    border-radius: 10px;
}
</style>