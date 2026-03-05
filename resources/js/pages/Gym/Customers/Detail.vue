<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Customer Detail #${customerId}`" />
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                <p class="mt-1 font-semibold">{{ customer?.name || '-' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Telepon</p>
                <p class="mt-1 font-semibold">{{ customer?.phone || '-' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                <p class="mt-1 font-semibold">{{ customer?.email || '-' }}</p>
            </div>
        </div>
        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <h3 class="text-lg font-semibold mb-2">Membership History</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Daftar riwayat membership akan ditampilkan.</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <h3 class="text-lg font-semibold mb-2">Visit History</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Daftar kunjungan akan ditampilkan.</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <h3 class="text-lg font-semibold mb-2">Sales History</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Daftar penjualan akan ditampilkan.</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';

const props = defineProps<{ customerId: string }>();
const customerId = props.customerId;
const customer = ref<any>(null);

const fetchDetail = async () => {
    const { data } = await axios.get(`/api/customers/${customerId}`);
    customer.value = data.data;
};

onMounted(fetchDetail);
</script>
