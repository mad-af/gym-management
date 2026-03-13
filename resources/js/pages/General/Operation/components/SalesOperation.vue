<template>
  <div>
    <OperationActionButton :icon="BanknoteIcon" title="Sales" description="Input transaksi penjualan."
      iconBgClass="bg-blue-50 text-blue-600 dark:bg-blue-500/10" glowClass="bg-blue-400" @click="isOpen = true" />
    <Drawer :isOpen="isOpen" title="Sales" @close="closeDrawer">
      <div class="space-y-6">
        <div class="space-y-2">
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Customer (opsional)</label>
          <Combobox v-model="form.customer_id" :options="customerOptions" labelKey="name" valueKey="id"
            placeholder="Pilih customer..." :loading="customerLoading" remote actionText="Tambah customer"
            @action="openCreateCustomerModal" @search="onCustomerSearch" @load-more="onCustomerLoadMore" />
          <p v-if="errors.customer_id" class="mt-1 text-sm text-error-500">{{ errors.customer_id }}</p>
        </div>
        <div class="space-y-4">
          <div
            class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-transparent divide-y divide-gray-200 dark:divide-gray-800">
            <div v-for="(item, idx) in form.items" :key="idx" class="space-y-4 p-4">
              <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">Item {{ idx + 1 }}</p>
                <Button v-if="form.items.length > 1" variant="outline" :onClick="() => removeItem(idx)">
                  Hapus
                </Button>
              </div>

              <div class="space-y-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Produk</label>
                <Combobox v-model="item.product_id" :options="getProductOptionsForItem(item)" labelKey="name"
                  valueKey="id" placeholder="Pilih produk..." :loading="productLoading" remote @search="onProductSearch"
                  @load-more="onProductLoadMore" @change="(v) => onProductChange(item, v)" />
                <p v-if="errors[`items.${idx}.product_id`]" class="mt-1 text-sm text-error-500">
                  {{ errors[`items.${idx}.product_id`] }}
                </p>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                  <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
                  <input type="number" min="1" :max="getMaxQuantity(item)" v-model.number="item.quantity"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                  <p v-if="getItemStock(item) !== null" class="text-xs text-gray-500 dark:text-gray-400">
                    Stok tersedia: {{ getItemStock(item) }}
                  </p>
                  <p v-if="errors[`items.${idx}.quantity`]" class="mt-1 text-sm text-error-500">
                    {{ errors[`items.${idx}.quantity`] }}
                  </p>
                </div>
                <div class="space-y-2">
                  <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Subtotal</label>
                  <div
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200">
                    {{ formatCurrencyId(getItemSubtotal(item)) }}
                  </div>
                  <p v-if="getItemUnitPrice(item) !== null" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatCurrencyId(getItemUnitPrice(item)) }} × {{ item.quantity || 0 }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Button variant="outline" :onClick="addItem">Tambah Item</Button>
            <div class="text-right">
              <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
              <p class="text-base font-semibold text-gray-900 dark:text-white">
                {{ formatCurrencyId(totalAmount) }}
              </p>
            </div>
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

    <Teleport to="body">
      <Modal v-if="isCreateCustomerOpen" :fullScreenBackdrop="true" @close="closeCreateCustomerModal">
        <template #body>
          <div class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
            <h4 class="mb-7 text-title-sm font-semibold text-gray-800 dark:text-white/90">
              Tambah Customer
            </h4>
            <button @click="closeCreateCustomerModal"
              class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:top-6 sm:right-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
              type="button">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <div class="space-y-6">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                  Nama <span class="text-error-500">*</span>
                </label>
                <input type="text" v-model="createCustomerForm.name"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                  placeholder="Nama customer" />
                <p v-if="createCustomerErrors.name" class="mt-1 text-sm text-error-500">{{ createCustomerErrors.name }}
                </p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                  Nomor Telepon
                </label>
                <input type="text" v-model="createCustomerForm.phone"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                  placeholder="Nomor telepon" />
                <p v-if="createCustomerErrors.phone" class="mt-1 text-sm text-error-500">{{ createCustomerErrors.phone
                }}</p>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                  Email
                </label>
                <input type="email" v-model="createCustomerForm.email"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                  placeholder="Email" />
                <p v-if="createCustomerErrors.email" class="mt-1 text-sm text-error-500">{{ createCustomerErrors.email
                }}</p>
              </div>
            </div>

            <div class="mt-8 flex w-full items-center justify-end gap-3">
              <Button size="sm" variant="outline" :onClick="closeCreateCustomerModal">
                Batal
              </Button>
              <Button size="sm" variant="primary" :onClick="submitCreateCustomer" :disabled="createCustomerProcessing">
                {{ createCustomerProcessing ? 'Menyimpan...' : 'Simpan' }}
              </Button>
            </div>
          </div>
        </template>
      </Modal>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Modal from '@/components/ui/Modal.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { BanknoteIcon } from '@/icons';

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
  customer_id: '' as string | null | '',
  items: [{ product_id: '' as string | null | '', quantity: 1, product: null as any | null }],
});
const errors = ref<Record<string, string>>({});

const customerOptions = ref<any[]>([]);
const customerLoading = ref(false);
const customerSearch = ref('');
const customerPage = ref(1);
const customerHasMore = ref(true);

const isCreateCustomerOpen = ref(false);
const createCustomerProcessing = ref(false);
const createCustomerForm = ref({
  name: '',
  phone: '',
  email: '',
});
const createCustomerErrors = ref<Record<string, string>>({});

const productOptions = ref<any[]>([]);
const productLoading = ref(false);
const productSearch = ref('');
const productPage = ref(1);
const productHasMore = ref(true);

const fetchCustomerOptions = async (reset = false) => {
  if (reset) {
    customerPage.value = 1;
    customerOptions.value = [];
    customerHasMore.value = true;
  }
  if (!customerHasMore.value && !reset) return;
  customerLoading.value = true;
  try {
    const { data } = await axios.get('/api/customers/selection', {
      params: { per_page: 20, page: customerPage.value, search: customerSearch.value || undefined },
    });
    const payload = data?.data;
    const arr = payload?.data || payload || [];
    customerOptions.value = reset ? arr : [...customerOptions.value, ...arr];
    customerHasMore.value = !!payload?.next_page_url;
    customerPage.value++;
  } finally {
    customerLoading.value = false;
  }
};
const onCustomerSearch = (query: string) => {
  customerSearch.value = query;
  fetchCustomerOptions(true);
};
const onCustomerLoadMore = () => {
  fetchCustomerOptions(false);
};

const openCreateCustomerModal = () => {
  isCreateCustomerOpen.value = true;
  createCustomerForm.value = { name: '', phone: '', email: '' };
  createCustomerErrors.value = {};
  createCustomerProcessing.value = false;
};
const closeCreateCustomerModal = () => {
  isCreateCustomerOpen.value = false;
};
const submitCreateCustomer = async () => {
  createCustomerProcessing.value = true;
  createCustomerErrors.value = {};
  try {
    const { data } = await axios.post('/api/customers', {
      name: createCustomerForm.value.name,
      phone: createCustomerForm.value.phone || undefined,
      email: createCustomerForm.value.email || undefined,
    });
    const c = data?.data || data;
    customerOptions.value = [{ id: c.id, name: c.name }, ...customerOptions.value.filter((it: any) => it.id !== c.id)];
    form.value.customer_id = c.id;
    closeCreateCustomerModal();
  } catch (e: any) {
    if (e.response?.status === 422 && e.response.data?.errors) {
      const ers = e.response.data.errors;
      createCustomerErrors.value = Object.keys(ers).reduce((acc: any, k) => {
        acc[k] = Array.isArray(ers[k]) ? ers[k][0] : String(ers[k]);
        return acc;
      }, {});
    } else {
      console.error(e);
      alert('Gagal membuat customer.');
    }
  } finally {
    createCustomerProcessing.value = false;
  }
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
  form.value.items.push({ product_id: '', quantity: 1, product: null });
};
const removeItem = (idx: number) => {
  form.value.items.splice(idx, 1);
};

const onProductChange = (item: any, productId: any) => {
  if (!productId) {
    item.product = null;
    return;
  }
  const prod = productOptions.value.find((p: any) => p.id === productId) || null;
  item.product = prod;
};

const getItemUnitPrice = (item: any): number | null => {
  const prod = item?.product || productOptions.value.find((p: any) => p.id === item?.product_id);
  const price = prod?.price ?? null;
  if (price === null || price === undefined) return null;
  const n = typeof price === 'number' ? price : Number(price);
  return Number.isNaN(n) ? null : n;
};

const getItemStock = (item: any): number | null => {
  const prod = item?.product || productOptions.value.find((p: any) => p.id === item?.product_id);
  const stock = prod?.stock ?? null;
  if (stock === null || stock === undefined) return null;
  const n = typeof stock === 'number' ? stock : Number(stock);
  return Number.isNaN(n) ? null : n;
};

const getMaxQuantity = (item: any): number | undefined => {
  const stock = getItemStock(item);
  if (stock === null) return undefined;
  return stock;
};

const getItemSubtotal = (item: any): number => {
  const unit = getItemUnitPrice(item) ?? 0;
  const qty = typeof item?.quantity === 'number' ? item.quantity : Number(item?.quantity || 0);
  return unit * (Number.isNaN(qty) ? 0 : qty);
};

const getProductOptionsForItem = (item: any) => {
  const chosen = new Set(
    form.value.items
      .map((it: any) => it.product_id)
      .filter((id: any) => id && id !== item.product_id),
  );
  return productOptions.value.filter((p: any) => !chosen.has(p.id) || p.id === item.product_id);
};

const closeDrawer = () => {
  isOpen.value = false;
  form.value = { customer_id: '', items: [{ product_id: '', quantity: 1, product: null }] };
  errors.value = {};
  processing.value = false;
  isCreateCustomerOpen.value = false;
};

const formatCurrencyId = (value: unknown): string => {
  if (value === null || value === undefined || value === '') {
    return 'Rp 0';
  }
  const numberValue = typeof value === 'number' ? value : Number(value);
  if (Number.isNaN(numberValue)) {
    return String(value);
  }
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  })
    .format(numberValue)
    .replace('Rp', 'Rp ');
};

const totalAmount = computed(() => {
  return form.value.items.reduce((sum, it) => sum + getItemSubtotal(it), 0);
});

const submit = async () => {
  processing.value = true;
  errors.value = {};
  try {
    const localErrors: Record<string, string> = {};
    const seenProducts = new Set<string>();
    form.value.items.forEach((it, idx) => {
      if (!it.product_id) {
        localErrors[`items.${idx}.product_id`] = 'Produk wajib dipilih.';
      } else {
        const pid = String(it.product_id);
        if (seenProducts.has(pid)) {
          localErrors[`items.${idx}.product_id`] = 'Produk sudah dipilih di item lain.';
        } else {
          seenProducts.add(pid);
        }
      }
      const qty = typeof it.quantity === 'number' ? it.quantity : Number(it.quantity);
      if (!qty || Number.isNaN(qty) || qty < 1) {
        localErrors[`items.${idx}.quantity`] = 'Jumlah minimal 1.';
      } else {
        const stock = getItemStock(it);
        if (stock !== null && qty > stock) {
          localErrors[`items.${idx}.quantity`] = `Jumlah melebihi stok (${stock}).`;
        }
      }
    });
    if (Object.keys(localErrors).length > 0) {
      errors.value = localErrors;
      return;
    }

    const payload: any = {
      customer_id: form.value.customer_id || null,
      items: form.value.items.map((it) => ({
        product_id: it.product_id,
        quantity: it.quantity,
        price: getItemUnitPrice(it),
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
  fetchCustomerOptions(true);
  fetchProductOptions(true);
});
</script>
