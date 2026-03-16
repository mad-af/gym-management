<template>
    <Link href="/dashboard" class="lg:hidden">
        <ApplicationLogo />
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/components/common/ApplicationLogo.vue';
</script>
