<template>
  <div>
    <OperationActionButton :icon="BanknoteIcon" title="Sales" description="Input transaksi penjualan."
      iconBgClass="bg-blue-50 text-blue-600 dark:bg-blue-500/10" glowClass="bg-blue-400" @click="isOpen = true" />
    <Drawer :isOpen="isOpen" title="Sales" @close="isOpen = false">
      <div class="text-sm text-gray-600 dark:text-gray-300">
        Form input transaksi penjualan.
      </div>
    </Drawer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { BanknoteIcon } from '@/icons';

const isOpen = ref(false);
</script>
