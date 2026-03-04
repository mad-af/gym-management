<script setup lang="ts">
defineProps<{
  collapsed?: boolean;
}>();
</script>

<template>
  <div class="flex items-center gap-3">
    <!-- Logo Icon -->
    <img src="/images/logo/logo.webp" alt="Logo Tanah Bumbu" class="h-10 w-auto object-contain" />

    <!-- Logo Slogan -->
    <img v-if="!collapsed" src="/images/logo/logo-slogan.webp" alt="Tanah Bumbu BerAKSI"
      class="h-8 w-auto object-contain" />
  </div>
</template>
