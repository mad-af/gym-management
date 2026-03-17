<template>
    <div>
        <OperationActionButton
            :icon="PackageIcon"
            title="Stock Movement"
            description="Input stok masuk dan penyesuaian."
            iconBgClass="bg-purple-50 text-purple-600 dark:bg-purple-500/10"
            glowClass="bg-purple-400"
            @click="isOpen = true"
        />

        <Drawer :isOpen="isOpen" title="Stock Movement" @close="closeDrawer">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Produk</label
                    >
                    <Combobox
                        v-model="selectedProductId"
                        :options="productOptions"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Pilih produk..."
                        :loading="productLoading"
                        remote
                        @search="onProductSearch"
                        @load-more="onProductLoadMore"
                    />
                    <p
                        v-if="errors.product_id"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ errors.product_id }}
                    </p>
                </div>

                <div
                    v-if="selectedProduct"
                    class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900"
                >
                    <p
                        class="text-sm font-medium text-gray-900 dark:text-white/90"
                    >
                        {{ selectedProduct.name }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Stok saat ini:
                        {{
                            Number(selectedProduct.stock ?? 0).toLocaleString(
                                'id-ID',
                            )
                        }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Harga jual:
                        {{ formatCurrencyId(selectedProduct.price) }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Tipe</label
                    >
                    <nav
                        class="flex overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900"
                    >
                        <button
                            v-for="type in typeOptions"
                            :key="type.value"
                            type="button"
                            @click="form.type = type.value"
                            :class="[
                                'inline-flex flex-1 items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out',
                                form.type === type.value
                                    ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white'
                                    : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                            ]"
                        >
                            {{ type.label }}
                        </button>
                    </nav>
                    <p v-if="errors.type" class="mt-1 text-sm text-error-500">
                        {{ errors.type }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Jumlah</label
                    >
                    <input
                        v-model.number="form.quantity"
                        type="number"
                        min="0"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Untuk penyesuaian, jumlah ini akan menjadi stok akhir
                        produk.
                    </p>
                    <p
                        v-if="errors.quantity"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ errors.quantity }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Deskripsi</label
                    >
                    <input
                        v-model="form.description"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        placeholder="Catatan pergerakan stok"
                    />
                </div>
            </div>

            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer"
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        :onClick="submit"
                        :disabled="submitDisabled"
                    >
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { PackageIcon } from '@/icons';

interface ProductOption {
    id: string;
    name: string;
    stock?: number;
    price?: number;
}

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
    product_id: '' as string | null | '',
    type: 'IN' as 'IN' | 'ADJUSTMENT',
    quantity: 0,
    description: '' as string,
});
const errors = ref<Record<string, string>>({});

const selectedProductId = ref<string | null>(null);
const selectedProduct = ref<ProductOption | null>(null);
const productOptions = ref<ProductOption[]>([]);
const productLoading = ref(false);
const productSearch = ref('');
const productPage = ref(1);
const productHasMore = ref(true);

const typeOptions = [
    { value: 'IN' as const, label: 'Masuk' },
    { value: 'ADJUSTMENT' as const, label: 'Penyesuaian' },
];

const fetchProductOptions = async (reset = false) => {
    if (reset) {
        productPage.value = 1;
        productOptions.value = [];
        productHasMore.value = true;
    }
    if (!productHasMore.value && !reset) {
        return;
    }

    productLoading.value = true;
    try {
        const { data } = await axios.get('/api/products/selection', {
            params: {
                page: productPage.value,
                per_page: 20,
                search: productSearch.value || undefined,
                is_active: 1,
            },
        });
        const payload = data?.data;
        const arr = Array.isArray(payload?.data)
            ? payload.data
            : Array.isArray(payload)
              ? payload
              : [];

        const mapped = arr.map((item: Record<string, unknown>) => ({
            id: String(item.id ?? ''),
            name: String(item.name ?? '-'),
            stock: Number(item.stock ?? 0),
            price: Number(item.price ?? 0),
        }));

        productOptions.value = reset
            ? mapped
            : [
                  ...productOptions.value,
                  ...mapped.filter(
                      (item) =>
                          !productOptions.value.some(
                              (existing) => existing.id === item.id,
                          ),
                  ),
              ];

        productHasMore.value = Boolean(payload?.next_page_url);
        productPage.value += 1;
    } finally {
        productLoading.value = false;
    }
};

const fetchProductDetail = async (productId: string) => {
    try {
        const { data } = await axios.get(`/api/products/${productId}`);
        const product = data?.data ?? data;
        selectedProduct.value = {
            id: String(product?.id ?? productId),
            name: String(product?.name ?? '-'),
            stock: Number(product?.stock ?? 0),
            price: Number(product?.price ?? 0),
        };
        form.value.product_id = selectedProduct.value.id;
    } catch {
        selectedProduct.value = null;
    }
};

watch(selectedProductId, (id) => {
    if (!id) {
        selectedProduct.value = null;
        form.value.product_id = '';
        return;
    }

    form.value.product_id = id;
    fetchProductDetail(id);
});

const onProductSearch = (query: string) => {
    productSearch.value = query;
    fetchProductOptions(true);
};

const onProductLoadMore = () => {
    fetchProductOptions(false);
};

const closeDrawer = () => {
    isOpen.value = false;
    form.value = { product_id: '', type: 'IN', quantity: 0, description: '' };
    selectedProductId.value = null;
    selectedProduct.value = null;
    errors.value = {};
    processing.value = false;
};

const submitDisabled = computed(() => {
    if (processing.value) {
        return true;
    }
    if (!form.value.product_id || !form.value.type) {
        return true;
    }
    if (
        Number.isNaN(Number(form.value.quantity)) ||
        Number(form.value.quantity) < 0
    ) {
        return true;
    }
    return false;
});

const formatCurrencyId = (value: unknown): string => {
    const numberValue = typeof value === 'number' ? value : Number(value ?? 0);
    if (Number.isNaN(numberValue)) {
        return 'Rp 0';
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

const submit = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await axios.post('/api/stock-movements', {
            product_id: form.value.product_id,
            type: form.value.type,
            quantity: Number(form.value.quantity),
            description: form.value.description || null,
        });

        closeDrawer();
        emit('submitted');
    } catch (e: unknown) {
        const error = e as {
            response?: {
                status?: number;
                data?: { errors?: Record<string, string[] | string> };
            };
        };

        if (error.response?.status === 422 && error.response.data?.errors) {
            const validationErrors = error.response.data.errors;
            errors.value = Object.keys(validationErrors).reduce(
                (acc, key) => {
                    acc[key] = Array.isArray(validationErrors[key])
                        ? (validationErrors[key] as string[])[0]
                        : String(validationErrors[key]);
                    return acc;
                },
                {} as Record<string, string>,
            );
        } else {
            alert('Gagal menyimpan pergerakan stok.');
        }
    } finally {
        processing.value = false;
    }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();

onMounted(() => {
    fetchProductOptions(true);
});
</script>
