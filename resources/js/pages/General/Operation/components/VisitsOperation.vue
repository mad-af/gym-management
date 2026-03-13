<template>
  <div>
    <OperationActionButton :icon="DoorOpenIcon" title="Visits" description="Check in kunjungan harian."
      iconBgClass="bg-brand-50 text-brand-600 dark:bg-brand-500/10" glowClass="bg-brand-400" @click="isOpen = true" />
    <Drawer :isOpen="isOpen" title="Visits / Check In" @close="closeDrawer">
      <div class="space-y-6">
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Customer</label>
          <SelectInput v-model="form.customer_id" :options="customerOptions" placeholder="Pilih customer" />
          <p v-if="errors.customer_id" class="mt-1 text-sm text-error-500">{{ errors.customer_id }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Metode Check In</label>
          <SelectInput v-model="form.checkin_method" :options="checkinMethodOptions" placeholder="Pilih metode" />
          <p v-if="errors.checkin_method" class="mt-1 text-sm text-error-500">{{ errors.checkin_method }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tipe Kunjungan</label>
          <SelectInput v-model="form.visit_type" :options="visitTypeOptions" placeholder="Pilih tipe" />
          <p v-if="errors.visit_type" class="mt-1 text-sm text-error-500">{{ errors.visit_type }}</p>
        </div>

        <div v-if="form.visit_type === 'DAILY'" class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Harga Harian</label>
          <input type="number" min="0" v-model.number="form.price"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
          <p v-if="errors.price" class="mt-1 text-sm text-error-500">{{ errors.price }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">QR Code (opsional)</label>
          <input type="text" v-model="form.qr_code"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
            placeholder="Scan/masukkan QR jika tanpa customer" />
          <p v-if="errors.qr_code" class="mt-1 text-sm text-error-500">{{ errors.qr_code }}</p>
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
import { DoorOpenIcon } from '@/icons';

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
  customer_id: '' as string | null | '',
  checkin_method: '' as string | null | '',
  visit_type: '' as string | null | '',
  price: null as number | null,
  qr_code: '' as string,
});
const errors = ref<Record<string, string>>({});

const customerOptions = ref<{ value: string; label: string }[]>([]);
const checkinMethodOptions = [
  { value: 'QR_CODE', label: 'QR Code' },
  { value: 'CARD', label: 'Kartu' },
  { value: 'MANUAL', label: 'Manual' },
];
const visitTypeOptions = [
  { value: 'MEMBERSHIP', label: 'Membership' },
  { value: 'DAILY', label: 'Harian' },
];

const fetchCustomers = async () => {
  const { data } = await axios.get('/api/customers/selection', { params: { per_page: 50 } });
  const arr = data?.data?.data || data?.data || [];
  customerOptions.value = arr.map((it: any) => ({ value: it.id, label: it.name }));
};

const closeDrawer = () => {
  isOpen.value = false;
  form.value = { customer_id: '', checkin_method: '', visit_type: '', price: null, qr_code: '' };
  errors.value = {};
  processing.value = false;
};

const submit = async () => {
  processing.value = true;
  errors.value = {};
  try {
    const payload: any = {
      customer_id: form.value.customer_id || null,
      checkin_method: form.value.checkin_method,
      visit_type: form.value.visit_type,
      price: form.value.visit_type === 'DAILY' ? form.value.price : null,
      qr_code: form.value.qr_code || null,
    };
    await axios.post('/api/visits', payload);
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
      alert('Gagal menyimpan kunjungan.');
    }
  } finally {
    processing.value = false;
  }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();
onMounted(fetchCustomers);
</script>
