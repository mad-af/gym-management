<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Daily Revenue" />
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Hari Ini</p>
                <p class="mt-1 text-2xl font-semibold">{{ summary.today }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Minggu Ini</p>
                <p class="mt-1 text-2xl font-semibold">{{ summary.thisWeek }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Bulan Ini</p>
                <p class="mt-1 text-2xl font-semibold">{{ summary.thisMonth }}</p>
            </div>
        </div>
        <div class="mt-6 rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
            <p class="text-sm text-gray-600 dark:text-gray-400">Grafik pendapatan harian akan ditempatkan di sini.</p>
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';

const summary = ref({ today: 0, thisWeek: 0, thisMonth: 0 });
</script>
