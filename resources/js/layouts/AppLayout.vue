<template>
    <ThemeProvider>
        <SidebarProvider>
            <AdminLayout>
                <slot />
            </AdminLayout>
        </SidebarProvider>
    </ThemeProvider>
</template>

<script setup lang="ts">
import AdminLayout from '@/components/layout/AdminLayout.vue';
import SidebarProvider from '@/components/layout/SidebarProvider.vue';
import ThemeProvider from '@/components/layout/ThemeProvider.vue';
</script>
