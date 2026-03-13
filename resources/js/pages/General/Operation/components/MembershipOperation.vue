<template>
  <div>
    <OperationActionButton :icon="UserCircleIcon" title="Membership" description="Transaksi membership."
      iconBgClass="bg-success-50 text-success-700 dark:bg-success-500/10" glowClass="bg-success-400"
      @click="isOpen = true" />
    <Drawer :isOpen="isOpen" title="Membership" @close="closeDrawer">
      <div class="space-y-6">
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Customer</label>
          <SelectInput v-model="form.customer_id" :options="customerOptions" placeholder="Pilih customer" />
          <p v-if="errors.customer_id" class="mt-1 text-sm text-error-500">{{ errors.customer_id }}</p>
        </div>
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Paket Membership</label>
          <SelectInput v-model="form.package_id" :options="packageOptions" placeholder="Pilih paket" />
          <p v-if="errors.package_id" class="mt-1 text-sm text-error-500">{{ errors.package_id }}</p>
        </div>
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Mulai</label>
          <input type="date" v-model="form.start_date"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
          <p v-if="errors.start_date" class="mt-1 text-sm text-error-500">{{ errors.start_date }}</p>
        </div>
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Harga (opsional)</label>
          <input type="number" min="0" v-model.number="form.price"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
          <p v-if="errors.price" class="mt-1 text-sm text-error-500">{{ errors.price }}</p>
        </div>
      </div>
      <template #footer>
        <div class="flex w-full justify-end gap-3">
          <Button variant="outline" :onClick="closeDrawer">Batal</Button>
          <Button variant="primary" :onClick="submit" :disabled="processing">
            {{ processing ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </div>
      </template>
    </Drawer>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, onMounted } from 'vue';
import SelectInput from '@/components/forms/FormElements/SelectInput.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { UserCircleIcon } from '@/icons';

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
  customer_id: '' as string | null | '',
  package_id: '' as string | null | '',
  start_date: '' as string,
  price: null as number | null,
});
const errors = ref<Record<string, string>>({});

const customerOptions = ref<{ value: string; label: string }[]>([]);
const packageOptions = ref<{ value: string; label: string }[]>([]);

const fetchCustomers = async () => {
  const { data } = await axios.get('/api/customers/selection', { params: { per_page: 50 } });
  const arr = data?.data?.data || data?.data || [];
  customerOptions.value = arr.map((it: any) => ({ value: it.id, label: it.name }));
};
const fetchPackages = async () => {
  const { data } = await axios.get('/api/membership-packages/selection', { params: { per_page: 50, is_active: 1 } });
  const arr = data?.data?.data || data?.data || [];
  packageOptions.value = arr.map((it: any) => ({ value: it.id, label: it.name }));
};

const closeDrawer = () => {
  isOpen.value = false;
  form.value = { customer_id: '', package_id: '', start_date: '', price: null };
  errors.value = {};
  processing.value = false;
};

const submit = async () => {
  processing.value = true;
  errors.value = {};
  try {
    const payload: any = {
      customer_id: form.value.customer_id,
      package_id: form.value.package_id,
      start_date: form.value.start_date || null,
      price: form.value.price,
    };
    await axios.post('/api/membership-transactions', payload);
    closeDrawer();
    emit('submitted');
  } catch (e: any) {
    if (e.response?.status === 422 && e.response.data?.errors) {
      const ers = e.response.data.errors;
      errors.value = Object.keys(ers).reduce((acc: any, k) => {
        acc[k] = Array.isArray(ers[k]) ? ers[k][0] : String(ers[k]);
        return acc;
      }, {});
    } else {
      console.error(e);
      alert('Gagal menyimpan transaksi membership.');
    }
  } finally {
    processing.value = false;
  }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();
onMounted(() => {
  fetchCustomers();
  fetchPackages();
});
</script>
