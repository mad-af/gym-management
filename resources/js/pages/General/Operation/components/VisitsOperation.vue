<template>
  <div>
    <OperationActionButton
      :icon="DoorOpenIcon"
      title="Visits"
      description="Check in kunjungan harian."
      iconBgClass="bg-brand-50 text-brand-600 dark:bg-brand-500/10"
      glowClass="bg-brand-400"
      @click="isOpen = true"
    />
    <Drawer :isOpen="isOpen" title="Visits / Check In" @close="isOpen = false">
      <div class="text-sm text-gray-600 dark:text-gray-300">
        Form input kunjungan harian.
      </div>
    </Drawer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { DoorOpenIcon } from '@/icons';

const isOpen = ref(false);
</script>
