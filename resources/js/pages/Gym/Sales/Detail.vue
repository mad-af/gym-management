<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="`Sale Detail #${saleId}`" />
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pelanggan</p>
                <p class="mt-1 font-semibold">{{ sale?.customer?.name || '-' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                <p class="mt-1 font-semibold">{{ sale?.total_amount ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                <p class="mt-1 font-semibold">{{ sale?.created_at ?? '-' }}</p>
            </div>
        </div>
        <div class="mt-6 rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
            <h3 class="text-lg font-semibold mb-4">Items</h3>
            <div class="max-w-full overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="border border-gray-100 px-4 py-3 text-left dark:border-gray-800">Produk</th>
                            <th class="border border-gray-100 px-4 py-3 text-left dark:border-gray-800">Qty</th>
                            <th class="border border-gray-100 px-4 py-3 text-left dark:border-gray-800">Harga</th>
                            <th class="border border-gray-100 px-4 py-3 text-left dark:border-gray-800">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="it in sale?.items || []" :key="it.id">
                            <td class="border border-gray-100 px-4 py-3 dark:border-gray-800">{{ it.product?.name || '-' }}</td>
                            <td class="border border-gray-100 px-4 py-3 dark:border-gray-800">{{ it.quantity }}</td>
                            <td class="border border-gray-100 px-4 py-3 dark:border-gray-800">{{ it.price }}</td>
                            <td class="border border-gray-100 px-4 py-3 dark:border-gray-800">{{ it.subtotal }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';

const props = defineProps<{ saleId: string }>();
const saleId = props.saleId;
const sale = ref<any>(null);

const fetchDetail = async () => {
    const { data } = await axios.get(`/api/sales/${saleId}`);
    sale.value = data.data;
};

onMounted(fetchDetail);
</script>
