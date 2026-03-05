<template>
    <AdminLayout>
        <PageBreadcrumb pageTitle="Membership Report" />
        <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-white/3">
            <p class="text-sm text-gray-600 dark:text-gray-400">Laporan membership akan ditempatkan di sini.</p>
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
</script>
