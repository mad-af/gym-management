<template>
  <div>
    <OperationActionButton
      :icon="PackageIcon"
      title="Stock Movement"
      description="Input stok masuk dan keluar."
      iconBgClass="bg-purple-50 text-purple-600 dark:bg-purple-500/10"
      glowClass="bg-purple-400"
      @click="isOpen = true"
    />
    <Drawer :isOpen="isOpen" title="Stock Movement" @close="isOpen = false">
      <div class="text-sm text-gray-600 dark:text-gray-300">
        Form input pergerakan stok.
      </div>
    </Drawer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { PackageIcon } from '@/icons';

const isOpen = ref(false);
</script>
