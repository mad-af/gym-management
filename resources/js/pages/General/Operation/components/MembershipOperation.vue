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

        <Drawer :isOpen="isOpen" title="Membership" @close="closeDrawer">
            <div class="space-y-6">
                <div class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Customer</label
                    >
                    <Combobox
                        v-model="selectedCustomerId"
                        :options="customerOptions"
                        labelKey="name"
                        valueKey="id"
                        placeholder="Pilih customer..."
                        :loading="customerLoading"
                        remote
                        actionText="Tambah customer"
                        @action="openCreateCustomerModal"
                        @search="onCustomerSearch"
                        @load-more="onCustomerLoadMore"
                    />
                    <p
                        v-if="errors.customer_id"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ errors.customer_id }}
                    </p>
                </div>

                <div
                    v-if="selectedCustomer"
                    class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900"
                >
                    <div
                        class="h-12 w-12 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                    >
                        <AppImage
                            v-if="selectedCustomer.avatar"
                            :src="selectedCustomer.avatar.url"
                            :alt="selectedCustomer.name"
                            containerClass="h-12 w-12 rounded-full"
                            imgClass="rounded-full"
                        />
                        <UserCircleIcon
                            v-else
                            class="m-3 h-6 w-6 text-gray-400 dark:text-gray-500"
                        />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-gray-800 dark:text-white/90"
                        >
                            {{ selectedCustomer.name }}
                        </p>
                        <p
                            class="truncate text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{
                                selectedCustomer.code ||
                                selectedCustomer.qr_code ||
                                '-'
                            }}
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label
                        class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200"
                    >
                        Paket Membership
                    </label>

                    <div
                        v-if="packageLoading"
                        class="rounded-lg border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
                    >
                        Memuat paket membership...
                    </div>

                    <div
                        v-else-if="!packageOptions.length"
                        class="rounded-lg border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
                    >
                        Belum ada paket membership aktif.
                    </div>

                    <div v-else class="grid gap-3 md:grid-cols-2">
                        <button
                            v-for="pkg in packageOptions"
                            :key="pkg.id"
                            type="button"
                            :aria-checked="form.package_id === pkg.id"
                            role="radio"
                            class="rounded-xl border p-4 text-left transition-all"
                            :class="
                                form.package_id === pkg.id
                                    ? 'border-brand-400 bg-brand-50 shadow-sm ring-2 ring-brand-500/20 dark:border-brand-400 dark:bg-brand-500/10'
                                    : 'border-gray-200 bg-white hover:border-brand-200 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-brand-500/60'
                            "
                            @click="form.package_id = pkg.id"
                        >
                            <div class="mb-3 flex items-start gap-3">
                                <div
                                    class="h-14 w-14 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800"
                                >
                                    <AppImage
                                        v-if="pkg.cover"
                                        :src="pkg.cover.url"
                                        :alt="pkg.name"
                                        containerClass="h-14 w-14 rounded-lg"
                                        imgClass="rounded-lg"
                                    />
                                    <div
                                        v-else
                                        class="flex h-14 w-14 items-center justify-center"
                                    >
                                        <PackageIcon
                                            class="h-6 w-6 text-gray-400 dark:text-gray-500"
                                        />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-semibold text-gray-900 dark:text-white/90"
                                    >
                                        {{ pkg.name }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ pkg.duration_days }} hari
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-medium text-brand-700 dark:text-brand-300"
                                    >
                                        {{ formatCurrencyId(pkg.price) }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-if="pkg.description"
                                class="mb-2 line-clamp-2 text-xs text-gray-600 dark:text-gray-300"
                            >
                                {{ pkg.description }}
                            </p>

                            <ul v-if="pkg.items.length" class="space-y-1">
                                <li
                                    v-for="item in pkg.items"
                                    :key="item.id"
                                    class="text-xs text-gray-600 dark:text-gray-300"
                                >
                                    • {{ item.item_name }}: {{ item.quantity }}
                                    {{ item.unit || '' }}
                                </li>
                            </ul>
                        </button>
                    </div>

                    <p
                        v-if="errors.package_id"
                        class="mt-1 text-sm text-error-500"
                    >
                        {{ errors.package_id }}
                    </p>
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

        <Teleport to="body">
            <Modal
                v-if="isCreateCustomerOpen"
                :fullScreenBackdrop="true"
                @close="closeCreateCustomerModal"
            >
                <template #body>
                    <div
                        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900"
                    >
                        <h4
                            class="mb-7 text-title-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            Tambah Customer
                        </h4>
                        <button
                            type="button"
                            @click="closeCreateCustomerModal"
                            class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:top-6 sm:right-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                        <div class="space-y-6">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Nama <span class="text-error-500">*</span>
                                </label>
                                <input
                                    v-model="createCustomerForm.name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Nama customer"
                                />
                                <p
                                    v-if="createCustomerErrors.name"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ createCustomerErrors.name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                    >Nomor Telepon</label
                                >
                                <input
                                    v-model="createCustomerForm.phone"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Nomor telepon"
                                />
                                <p
                                    v-if="createCustomerErrors.phone"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ createCustomerErrors.phone }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                    >Email</label
                                >
                                <input
                                    v-model="createCustomerForm.email"
                                    type="email"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Email"
                                />
                                <p
                                    v-if="createCustomerErrors.email"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ createCustomerErrors.email }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-8 flex w-full items-center justify-end gap-3"
                        >
                            <Button
                                size="sm"
                                variant="outline"
                                :onClick="closeCreateCustomerModal"
                                >Batal</Button
                            >
                            <Button
                                size="sm"
                                variant="primary"
                                :onClick="submitCreateCustomer"
                                :disabled="createCustomerProcessing"
                            >
                                {{
                                    createCustomerProcessing
                                        ? 'Menyimpan...'
                                        : 'Simpan'
                                }}
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
import { computed, onMounted, ref, watch } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Modal from '@/components/ui/Modal.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { PackageIcon, UserCircleIcon } from '@/icons';

interface MediaItem {
    url: string;
    placeholder?: string | null;
}

interface CustomerOption {
    id: string;
    name: string;
    code?: string | null;
    qr_code?: string | null;
    avatar?: MediaItem | null;
}

interface MembershipItem {
    id: string;
    item_name: string;
    quantity: number | string;
    unit?: string | null;
}

interface MembershipPackageOption {
    id: string;
    name: string;
    duration_days: number;
    price: number | string;
    description?: string | null;
    cover?: MediaItem | null;
    items: MembershipItem[];
}

const isOpen = ref(false);
const processing = ref(false);
const form = ref({
    customer_id: '' as string | null | '',
    package_id: '' as string | null | '',
});
const errors = ref<Record<string, string>>({});

const selectedCustomerId = ref<string | null>(null);
const selectedCustomer = ref<CustomerOption | null>(null);
const customerOptions = ref<CustomerOption[]>([]);
const customerLoading = ref(false);
const customerSearch = ref('');
const customerPage = ref(1);
const customerHasMore = ref(true);

const packageLoading = ref(false);
const packageOptions = ref<MembershipPackageOption[]>([]);

const isCreateCustomerOpen = ref(false);
const createCustomerProcessing = ref(false);
const createCustomerForm = ref({
    name: '',
    phone: '',
    email: '',
});
const createCustomerErrors = ref<Record<string, string>>({});

const fetchCustomerOptions = async (reset = false) => {
    if (reset) {
        customerPage.value = 1;
        customerOptions.value = [];
        customerHasMore.value = true;
    }
    if (!customerHasMore.value && !reset) {
        return;
    }

    customerLoading.value = true;
    try {
        const { data } = await axios.get('/api/customers/selection', {
            params: {
                per_page: 20,
                page: customerPage.value,
                search: customerSearch.value || undefined,
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
            code: typeof item.code === 'string' ? item.code : null,
            qr_code: typeof item.qr_code === 'string' ? item.qr_code : null,
            avatar: (item.avatar as MediaItem | null | undefined) ?? null,
        }));

        customerOptions.value = reset
            ? mapped
            : [
                  ...customerOptions.value,
                  ...mapped.filter(
                      (item) =>
                          !customerOptions.value.some(
                              (existing) => existing.id === item.id,
                          ),
                  ),
              ];

        customerHasMore.value = Boolean(payload?.next_page_url);
        customerPage.value += 1;
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

const fetchCustomerDetail = async (customerId: string) => {
    try {
        const { data } = await axios.get(`/api/customers/${customerId}`);
        const customer = data?.data ?? data;
        selectedCustomer.value = {
            id: String(customer?.id ?? customerId),
            name: String(customer?.name ?? '-'),
            code: typeof customer?.code === 'string' ? customer.code : null,
            qr_code:
                typeof customer?.qr_code === 'string' ? customer.qr_code : null,
            avatar: (customer?.avatar as MediaItem | null | undefined) ?? null,
        };
        form.value.customer_id = selectedCustomer.value.id;
    } catch {
        selectedCustomer.value = null;
    }
};

watch(selectedCustomerId, (id) => {
    if (!id) {
        selectedCustomer.value = null;
        form.value.customer_id = '';
        return;
    }

    form.value.customer_id = id;
    fetchCustomerDetail(id);
});

const fetchPackages = async () => {
    packageLoading.value = true;
    try {
        const { data } = await axios.get('/api/membership-packages', {
            params: { per_page: 50, page: 1, is_active: 1 },
        });

        const payload = data?.data;
        const arr = Array.isArray(payload?.data)
            ? payload.data
            : Array.isArray(payload)
              ? payload
              : [];

        packageOptions.value = arr.map((item: Record<string, unknown>) => ({
            id: String(item.id ?? ''),
            name: String(item.name ?? '-'),
            duration_days: Number(item.duration_days ?? 0),
            price: Number(item.price ?? 0),
            description:
                typeof item.description === 'string' ? item.description : null,
            cover: (item.cover as MediaItem | null | undefined) ?? null,
            items: Array.isArray(item.items)
                ? item.items.map(
                      (entry: Record<string, unknown>, index: number) => ({
                          id: String(
                              entry.id ?? `${item.id ?? 'item'}-${index}`,
                          ),
                          item_name: String(entry.item_name ?? '-'),
                          quantity: Number(entry.quantity ?? 0),
                          unit:
                              typeof entry.unit === 'string'
                                  ? entry.unit
                                  : null,
                      }),
                  )
                : [],
        }));
    } finally {
        packageLoading.value = false;
    }
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

        const customer = data?.data ?? data;
        const mapped: CustomerOption = {
            id: String(customer?.id ?? ''),
            name: String(customer?.name ?? '-'),
            code: typeof customer?.code === 'string' ? customer.code : null,
            qr_code:
                typeof customer?.qr_code === 'string' ? customer.qr_code : null,
            avatar: (customer?.avatar as MediaItem | null | undefined) ?? null,
        };

        customerOptions.value = [
            mapped,
            ...customerOptions.value.filter((item) => item.id !== mapped.id),
        ];
        selectedCustomerId.value = mapped.id;
        selectedCustomer.value = mapped;
        form.value.customer_id = mapped.id;
        closeCreateCustomerModal();
    } catch (e: unknown) {
        const error = e as {
            response?: {
                status?: number;
                data?: { errors?: Record<string, string[] | string> };
            };
        };

        if (error.response?.status === 422 && error.response.data?.errors) {
            const validationErrors = error.response.data.errors;
            createCustomerErrors.value = Object.keys(validationErrors).reduce(
                (acc, key) => {
                    acc[key] = Array.isArray(validationErrors[key])
                        ? (validationErrors[key] as string[])[0]
                        : String(validationErrors[key]);
                    return acc;
                },
                {} as Record<string, string>,
            );
        } else {
            alert('Gagal membuat customer.');
        }
    } finally {
        createCustomerProcessing.value = false;
    }
};

const closeDrawer = () => {
    isOpen.value = false;
    form.value = { customer_id: '', package_id: '' };
    selectedCustomerId.value = null;
    selectedCustomer.value = null;
    errors.value = {};
    processing.value = false;
    isCreateCustomerOpen.value = false;
};

const submitDisabled = computed(() => {
    if (processing.value) {
        return true;
    }
    if (!selectedCustomer.value?.id) {
        return true;
    }
    if (!form.value.package_id) {
        return true;
    }
    return false;
});

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

const submit = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await axios.post('/api/membership-transactions', {
            customer_id: selectedCustomer.value?.id ?? form.value.customer_id,
            package_id: form.value.package_id,
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
            alert('Gagal menyimpan transaksi membership.');
        }
    } finally {
        processing.value = false;
    }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();

onMounted(() => {
    fetchCustomerOptions(true);
    fetchPackages();
});
</script>
