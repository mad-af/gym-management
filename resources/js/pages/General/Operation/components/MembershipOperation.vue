<template>
  <div>
    <OperationActionButton
      :icon="UserCircleIcon"
      title="Membership"
      description="Transaksi membership."
      iconBgClass="bg-success-50 text-success-700 dark:bg-success-500/10"
      glowClass="bg-success-400"
      @click="isOpen = true"
    />
    <Drawer :isOpen="isOpen" title="Membership" @close="isOpen = false">
      <div class="text-sm text-gray-600 dark:text-gray-300">
        Form transaksi membership.
      </div>
    </Drawer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { UserCircleIcon } from '@/icons';

const isOpen = ref(false);
</script>
