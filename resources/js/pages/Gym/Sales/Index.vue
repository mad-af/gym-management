<template>
    <AdminLayout>
        <PageBreadcrumb :pageTitle="currentPageTitle" />

        <DynamicTable :columns="columns" :data="tableData" :items-per-page="perPage" :total-items="totalItems"
            :current-page="currentPage" :is-server-side="true" @update:page="handlePageChange"
            @update:search="handleSearch" @update:perPage="handlePerPageChange">
            <template #header-actions>
                <Button size="sm" variant="outline" :onClick="() => isFilterDrawerOpen = true"
                    className="w-full sm:w-auto" :startIcon="FilterIcon">
                    Filter
                </Button>
                <Button v-can="'create_sales'" size="sm" variant="primary" :onClick="openDrawer"
                    className="w-full sm:w-auto" :endIcon="PlusIcon">
                    Buat Penjualan
                </Button>
            </template>
            <template #actions="{ row }">
                <a :href="`/gym/sales/${row.id}`" class="text-brand-600 hover:underline">Detail</a>
            </template>
        </DynamicTable>

        <Drawer :isOpen="isDrawerOpen" @close="closeDrawer" title="Buat Penjualan" size="lg">
            <div class="space-y-6">
                <!-- Customer Selection -->
                <div>
                    <Combobox label="Pelanggan" v-model="form.customer_id" :options="customerOptions"
                        placeholder="Cari Pelanggan..." :searchable="true" :remote="true" @search="fetchCustomers"
                        :loading="loadingCustomers" value-key="id" label-key="name" />
                    <p v-if="form.errors.customer_id" class="mt-1 text-sm text-error-500">{{ form.errors.customer_id }}
                    </p>
                </div>

                <!-- Items Section -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Items</label>
                        <Button size="sm" variant="outline" :onClick="addItem" :startIcon="PlusIcon">
                            Tambah Item
                        </Button>
                    </div>

                    <div v-if="formItems.length === 0"
                        class="text-sm text-gray-500 italic text-center py-4 bg-gray-50 rounded-lg dark:bg-gray-800 dark:text-gray-400">
                        Belum ada item ditambahkan.
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="(item, index) in formItems" :key="index"
                            class="p-4 border border-gray-200 rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Item #{{ index + 1 }}
                                </h4>
                                <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm">
                                    Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="col-span-1 sm:col-span-2">
                                    <Combobox label="Produk" v-model="item.product_id" :options="productOptions"
                                        placeholder="Cari Produk..." :searchable="true" :remote="true"
                                        @search="fetchProducts" :loading="loadingProducts" value-key="id"
                                        label-key="name" @update:modelValue="(val) => onProductSelect(val, index)" />
                                    <p v-if="form.errors[`items.${index}.product_id`]"
                                        class="mt-1 text-sm text-error-500">
                                        {{ form.errors[`items.${index}.product_id`] }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
                                    <input type="number" v-model.number="item.quantity" min="1"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-200">Harga
                                        Satuan</label>
                                    <input type="number" v-model.number="item.price"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                                </div>
                            </div>

                            <div class="mt-2 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                Subtotal: Rp {{ (item.quantity * item.price).toLocaleString('id-ID') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">Total:</span>
                    <span class="text-xl font-bold text-brand-600">Rp {{ totalAmount.toLocaleString('id-ID') }}</span>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer">Batal</Button>
                    <Button variant="primary" :onClick="saveItem" :disabled="form.processing">Simpan</Button>
                </div>
            </template>
        </Drawer>

        <Drawer :isOpen="isFilterDrawerOpen" @close="isFilterDrawerOpen = false" title="Filter Penjualan">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Pelanggan</label>
                    <input type="text" v-model="filters.customer_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="resetFilter">Reset</Button>
                    <Button variant="primary" :onClick="handleFilter">Terapkan Filter</Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import DynamicTable from '@/components/tables/data-tables/DynamicTable.vue';
import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { PlusIcon, FilterIcon } from '@/icons';

const currentPageTitle = ref('Sales');
const isDrawerOpen = ref(false);
const isFilterDrawerOpen = ref(false);
const filters = ref({ customer_id: '' });

const items = ref<any[]>([]);
const totalItems = ref(0);
const currentPage = ref(1);
const perPage = ref(10);
const searchFilter = ref('');

const customerOptions = ref<any[]>([]);
const loadingCustomers = ref(false);
const productOptions = ref<any[]>([]);
const loadingProducts = ref(false);

const formItems = ref<any[]>([]);

const form = ref({
    customer_id: '',
    items: [] as any[],
    errors: {} as Record<string, string>,
    processing: false,
});

const totalAmount = computed(() => {
    return formItems.value.reduce((sum, item) => sum + (item.quantity * item.price), 0);
});

const tableData = computed(() =>
    items.value.map((s: any) => ({
        id: s.id,
        customer_name: s.customer?.name || '-',
        total_amount: s.total_amount,
        created_at: s.created_at,
    }))
);

const columns: Column[] = [
    { key: 'customer_name', label: 'Pelanggan' },
    { key: 'total_amount', label: 'Total', type: 'text' },
    { key: 'created_at', label: 'Tanggal', type: 'text' },
    { key: 'actions', label: 'Aksi', type: 'action' },
];

const fetchItems = async () => {
    try {
        const { data } = await axios.get('/api/sales', {
            params: {
                page: currentPage.value,
                per_page: perPage.value,
                search: searchFilter.value,
                customer_id: filters.value.customer_id,
            },
        });
        items.value = data.data?.data || [];
        totalItems.value = data.data?.total || 0;
    } catch (e) {
        console.error(e);
    }
};

const fetchCustomers = async (query: string = '') => {
    loadingCustomers.value = true;
    try {
        const { data } = await axios.get('/api/customers/selection', {
            params: { search: query, per_page: 20 },
        });
        customerOptions.value = data.data?.data || [];
    } catch (e) {
        console.error(e);
    } finally {
        loadingCustomers.value = false;
    }
};

const fetchProducts = async (query: string = '') => {
    loadingProducts.value = true;
    try {
        const { data } = await axios.get('/api/products/selection', {
            params: { search: query, per_page: 20 },
        });
        productOptions.value = data.data?.data || [];
    } catch (e) {
        console.error(e);
    } finally {
        loadingProducts.value = false;
    }
};

const addItem = () => {
    formItems.value.push({
        product_id: '',
        quantity: 1,
        price: 0,
    });
};

const removeItem = (index: number) => {
    formItems.value.splice(index, 1);
};

const onProductSelect = (productId: string, index: number) => {
    const product = productOptions.value.find(p => p.id === productId);
    if (product) {
        formItems.value[index].price = product.price;
    }
};

const openDrawer = () => {
    form.value = {
        customer_id: '',
        items: [],
        errors: {},
        processing: false,
    };
    formItems.value = [];
    isDrawerOpen.value = true;
    fetchCustomers();
    fetchProducts();
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
};

const saveItem = async () => {
    form.value.processing = true;
    form.value.errors = {};
    try {
        const payload = {
            customer_id: form.value.customer_id,
            items: formItems.value,
        };
        await axios.post('/api/sales', payload);
        closeDrawer();
        fetchItems();
    } catch (e: any) {
        if (e.response?.status === 422) {
            form.value.errors = e.response.data.errors;
        }
    } finally {
        form.value.processing = false;
    }
};

const handlePageChange = (page: number) => {
    currentPage.value = page;
    fetchItems();
};

const handleSearch = (search: string) => {
    searchFilter.value = search;
    currentPage.value = 1;
    fetchItems();
};

const handlePerPageChange = (newPerPage: number) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchItems();
};

const handleFilter = () => {
    isFilterDrawerOpen.value = false;
    currentPage.value = 1;
    fetchItems();
};

const resetFilter = () => {
    filters.value.customer_id = '';
    handleFilter();
};

onMounted(() => {
    fetchItems();
});
</script>
