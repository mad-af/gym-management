<template>
  <div>
    <OperationActionButton :icon="PackageIcon" title="Stock Movement" description="Input stok masuk dan keluar."
      iconBgClass="bg-purple-50 text-purple-600 dark:bg-purple-500/10" glowClass="bg-purple-400"
      @click="isOpen = true" />
    <Drawer :isOpen="isOpen" title="Stock Movement" @close="closeDrawer">
      <div class="space-y-6">
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Produk</label>
          <Combobox v-model="form.product_id" :options="productOptions" labelKey="name" valueKey="id"
            placeholder="Pilih produk..." :loading="productLoading" remote @search="onProductSearch"
            @load-more="onProductLoadMore" />
          <p v-if="errors.product_id" class="mt-1 text-sm text-error-500">{{ errors.product_id }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Tipe</label>
          <SelectInput v-model="form.type" :options="typeOptions" placeholder="Pilih tipe" />
          <p v-if="errors.type" class="mt-1 text-sm text-error-500">{{ errors.type }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
          <input type="number" min="0" v-model.number="form.quantity"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
          <p v-if="errors.quantity" class="mt-1 text-sm text-error-500">{{ errors.quantity }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Harga Pokok (opsional)</label>
          <input type="number" min="0" v-model.number="form.cost_price"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
          <p v-if="errors.cost_price" class="mt-1 text-sm text-error-500">{{ errors.cost_price }}</p>
        </div>

        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
          <input type="text" v-model="form.description"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
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
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { PackageIcon } from '@/icons';

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
  product_id: '' as string | null | '',
  type: '' as string | null | '',
  quantity: 0,
  cost_price: null as number | null,
  description: '' as string,
});
const errors = ref<Record<string, string>>({});

const productOptions = ref<any[]>([]);
const productLoading = ref(false);
const productSearch = ref('');
const productPage = ref(1);
const productHasMore = ref(true);
const typeOptions = [
  { value: 'IN', label: 'Masuk' },
  { value: 'OUT', label: 'Keluar' },
  { value: 'ADJUSTMENT', label: 'Penyesuaian' },
];

const fetchProductOptions = async (reset = false) => {
  if (reset) {
    productPage.value = 1;
    productOptions.value = [];
    productHasMore.value = true;
  }
  if (!productHasMore.value && !reset) return;

  productLoading.value = true;
  try {
    const { data } = await axios.get('/api/products/selection', {
      params: {
        page: productPage.value,
        per_page: 20,
        search: productSearch.value,
        is_active: 1,
      },
    });
    const payload = data?.data;
    const arr = payload?.data || [];
    if (reset) {
      productOptions.value = arr;
    } else {
      productOptions.value = [...productOptions.value, ...arr];
    }
    productHasMore.value = !!payload?.next_page_url;
    productPage.value++;
  } catch (e) {
    console.error(e);
  } finally {
    productLoading.value = false;
  }
};

const onProductSearch = (query: string) => {
  productSearch.value = query;
  fetchProductOptions(true);
};
const onProductLoadMore = () => {
  fetchProductOptions(false);
};

const closeDrawer = () => {
  isOpen.value = false;
  form.value = { product_id: '', type: '', quantity: 0, cost_price: null, description: '' };
  errors.value = {};
  processing.value = false;
};

const submit = async () => {
  processing.value = true;
  errors.value = {};
  try {
    const payload: any = {
      product_id: form.value.product_id,
      type: form.value.type,
      quantity: form.value.quantity,
      cost_price: form.value.cost_price,
      description: form.value.description || null,
    };
    await axios.post('/api/stock-movements', payload);
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
      alert('Gagal menyimpan pergerakan stok.');
    }
  } finally {
    processing.value = false;
  }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();
onMounted(() => fetchProductOptions(true));
</script>
