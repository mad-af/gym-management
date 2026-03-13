<template>
  <div>
    <OperationActionButton :icon="BanknoteIcon" title="Sales" description="Input transaksi penjualan."
      iconBgClass="bg-blue-50 text-blue-600 dark:bg-blue-500/10" glowClass="bg-blue-400" @click="isOpen = true" />
    <Drawer :isOpen="isOpen" title="Sales" @close="closeDrawer">
      <div class="space-y-6">
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Customer (opsional)</label>
          <SelectInput v-model="form.customer_id" :options="customerOptions" placeholder="Pilih customer" />
          <p v-if="errors.customer_id" class="mt-1 text-sm text-error-500">{{ errors.customer_id }}</p>
        </div>
        <div class="space-y-4">
          <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-1 gap-4 sm:grid-cols-12">
            <div class="sm:col-span-6 space-y-2">
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Produk</label>
              <Combobox v-model="item.product_id" :options="productOptions" labelKey="name" valueKey="id"
                placeholder="Pilih produk..." :loading="productLoading" remote @search="onProductSearch"
                @load-more="onProductLoadMore" />
              <p v-if="errors[`items.${idx}.product_id`]" class="mt-1 text-sm text-error-500">
                {{ errors[`items.${idx}.product_id`] }}
              </p>
            </div>
            <div class="sm:col-span-3 space-y-2">
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
              <input type="number" min="1" v-model.number="item.quantity"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
              <p v-if="errors[`items.${idx}.quantity`]" class="mt-1 text-sm text-error-500">
                {{ errors[`items.${idx}.quantity`] }}
              </p>
            </div>
            <div class="sm:col-span-3 space-y-2">
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Harga (opsional)</label>
              <input type="number" min="0" v-model.number="item.price"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
            </div>
          </div>
          <div class="flex justify-between">
            <Button variant="outline" :onClick="addItem">Tambah Item</Button>
            <div class="text-sm text-gray-600 dark:text-gray-300">Total item: {{ form.items.length }}</div>
          </div>
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
import { BanknoteIcon } from '@/icons';

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
  customer_id: '' as string | null | '',
  items: [{ product_id: '' as string | null | '', quantity: 1, price: null as number | null }],
});
const errors = ref<Record<string, string>>({});
const customerOptions = ref<{ value: string; label: string }[]>([]);
const productOptions = ref<any[]>([]);
const productLoading = ref(false);
const productSearch = ref('');
const productPage = ref(1);
const productHasMore = ref(true);

const fetchCustomers = async () => {
  const { data } = await axios.get('/api/customers/selection', { params: { per_page: 50 } });
  const arr = data?.data?.data || data?.data || [];
  customerOptions.value = arr.map((it: any) => ({ value: it.id, label: it.name }));
};
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

const addItem = () => {
  form.value.items.push({ product_id: '', quantity: 1, price: null });
};

const closeDrawer = () => {
  isOpen.value = false;
  form.value = { customer_id: '', items: [{ product_id: '', quantity: 1, price: null }] };
  errors.value = {};
  processing.value = false;
};

const submit = async () => {
  processing.value = true;
  errors.value = {};
  try {
    const payload: any = {
      customer_id: form.value.customer_id || null,
      items: form.value.items.map((it) => ({
        product_id: it.product_id,
        quantity: it.quantity,
        price: it.price,
      })),
    };
    await axios.post('/api/sales', payload);
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
      alert('Gagal menyimpan penjualan.');
    }
  } finally {
    processing.value = false;
  }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();
onMounted(() => {
  fetchCustomers();
  fetchProductOptions(true);
});
</script>
