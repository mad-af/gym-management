<template>
  <div class="overflow-hidden rounded-xl bg-white dark:border-gray-800 dark:bg-white/3">
    <div
      class="flex flex-col gap-2 rounded-xl rounded-b-none border border-b-0 border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
      <div class="flex items-center gap-3">
        <span class="text-gray-500 dark:text-gray-400">Show</span>
        <div class="relative z-20 bg-transparent">
          <select v-model="perPage"
            class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
            :class="{
              'text-gray-500 dark:text-gray-400': perPage,
            }">
            <option :value="5" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">5</option>
            <option :value="10" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">10</option>
            <option :value="20" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">20</option>
            <option :value="50" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">50</option>
          </select>
          <span
            class="pointer-events-none absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
        </div>
        <span class="text-gray-500 dark:text-gray-400">entries</span>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative">
          <button class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"
                fill="" />
            </svg>
          </button>
          <input v-model="search" type="text" placeholder="Search..."
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
        </div>

        <slot name="header-actions"></slot>
      </div>
    </div>

    <div class="max-w-full overflow-x-auto">
      <table class="w-full min-w-full">
        <thead>
          <tr>
            <th v-if="isSelectable" class="border border-gray-100 px-4 py-3 text-left dark:border-gray-800 w-[50px]">
              <label
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <span class="relative">
                  <input type="checkbox" class="sr-only" v-model="selectAll" @change="toggleSelectAll" />
                  <span :class="selectAll
                    ? 'border-brand-500 bg-brand-500'
                    : 'border-gray-300 bg-transparent dark:border-gray-700'
                    " class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px]">
                    <span :class="selectAll
                      ? ''
                      : 'opacity-0'
                      ">
                      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round"
                          stroke-linejoin="round" />
                      </svg>
                    </span>
                  </span>
                </span>
              </label>
            </th>
            <th v-for="col in columns" :key="col.key"
              class="border border-gray-100 px-4 py-3 text-left dark:border-gray-800" :class="col.class">
              <div class="flex w-full items-center justify-between"
                :class="{ 'cursor-pointer': col.sortable !== false }"
                @click="col.sortable !== false ? sortBy(col.key) : null">
                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                  {{ col.label }}
                </p>
                <span v-if="col.sortable !== false" class="flex flex-col gap-0.5">
                  <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    :class="{ 'fill-gray-700 dark:fill-gray-300': sortColumn === col.key && sortDirection === 'asc' }">
                    <path
                      d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" />
                  </svg>
                  <svg class="fill-gray-300 dark:fill-gray-700" width="8" height="5" viewBox="0 0 8 5" fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    :class="{ 'fill-gray-700 dark:fill-gray-300': sortColumn === col.key && sortDirection === 'desc' }">
                    <path
                      d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" />
                  </svg>
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in paginatedData" :key="row._key || row.id || JSON.stringify(row)" class="" :class="{
            'bg-gray-50 dark:bg-gray-900': row.selected,
          }">
            <td v-if="isSelectable && !row._cellAttributes?.checkbox?.hidden"
              class="border border-gray-100 px-4 py-3 dark:border-gray-800"
              :rowspan="row._cellAttributes?.checkbox?.rowspan || 1">
              <label
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <span class="relative">
                  <input type="checkbox" class="sr-only" v-model="row.selected" @change="updateSelectAll" />
                  <span :class="row.selected
                    ? 'border-brand-500 bg-brand-500'
                    : 'border-gray-300 bg-transparent dark:border-gray-700'
                    " class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px]">
                    <span :class="row.selected
                      ? ''
                      : 'opacity-0'
                      ">
                      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round"
                          stroke-linejoin="round" />
                      </svg>
                    </span>
                  </span>
                </span>
              </label>
            </td>
            <template v-for="col in columns" :key="col.key">
              <td v-if="!row._cellAttributes?.[col.key]?.hidden"
                class="border border-gray-100 px-4 py-3 dark:border-gray-800" :class="col.class"
                :rowspan="row._cellAttributes?.[col.key]?.rowspan || 1">

                <!-- Custom Slot -->
                <slot v-if="col.type === 'custom'" :name="`cell-${col.key}`" :row="row">
                  {{ row[col.key] }}
                </slot>

                <!-- Avatar Type -->
                <div v-else-if="col.type === 'avatar'" class="flex gap-3">
                  <div
                    class="h-10 w-10 overflow-hidden rounded-full bg-gray-100 text-gray-400 flex items-center justify-center dark:bg-gray-800 dark:text-gray-500">
                    <app-image v-if="getAvatarSrc(row, col)" :src="getAvatarSrc(row, col)"
                      :placeholder="getAvatarPlaceholder(row, col)" :alt="row[col.labelField || col.key]"
                      class="h-full w-full object-cover" />
                    <UserCircleIcon v-else class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">
                      {{ row[col.labelField || col.key] }}
                    </p>
                    <span class="text-sm text-gray-500 dark:text-gray-400" v-if="col.subLabelField">
                      {{ row[col.subLabelField] }}
                    </span>
                  </div>
                </div>

                <!-- Cover Type -->
                <div v-else-if="col.type === 'cover'" class="flex gap-3">
                  <div
                    class="h-12 w-12 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <app-image v-if="getAvatarSrc(row, col)" :src="getAvatarSrc(row, col)"
                      :placeholder="getAvatarPlaceholder(row, col)" :alt="row[col.labelField || col.key]"
                      class="h-full w-full object-cover" />
                    <svg v-else class="h-6 w-6 text-gray-400 dark:text-gray-600" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M5 5C4.44772 5 4 5.44772 4 6V18C4 18.5523 4.44772 19 5 19H19C19.5523 19 20 18.5523 20 18V6C20 5.44772 19.5523 5 19 5H5ZM6 7H18V13.5858L15.7071 11.2929C15.3166 10.9024 14.6834 10.9024 14.2929 11.2929L11 14.5858L9.70711 13.2929C9.31658 12.9024 8.68342 12.9024 8.29289 13.2929L6 15.5858V7Z"
                        fill="currentColor" />
                    </svg>
                  </div>
                  <div>
                    <p class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">
                      {{ row[col.labelField || col.key] }}
                    </p>
                    <span class="text-sm text-gray-500 dark:text-gray-400" v-if="col.subLabelField">
                      {{ row[col.subLabelField] }}
                    </span>
                  </div>
                </div>

                <!-- Status Type -->
                <span v-else-if="col.type === 'status'" :class="getStatusClass(row[col.key], col.statusMap)"
                  class="rounded-full px-2 py-0.5 text-theme-xs font-medium">
                  {{ row[col.key] }}
                </span>

                <!-- Action Type -->
                <div v-else-if="col.type === 'action'" class="flex w-full items-center gap-2 justify-center">
                  <slot name="actions" :row="row">
                    <div v-if="col.actions && col.actions.length > 0" class="flex items-center gap-2">
                      <button v-for="(action, idx) in col.actions" :key="idx" @click="handleActionClick(action, row)"
                        class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white/90"
                        :class="action.class" :title="action.label">
                        <!-- Render string/SVG icon -->
                        <span v-if="action.icon && typeof action.icon === 'string'" v-html="action.icon"></span>
                        <!-- Render component icon -->
                        <component v-else-if="action.icon" :is="action.icon" />
                        <!-- Render default icon based on type -->
                        <span v-else v-html="getActionIcon(action)"></span>
                      </button>
                    </div>
                    <div v-else class="flex items-center gap-2">
                      <button @click="$emit('edit', row)"
                        class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white/90">
                        <svg class="fill-current" width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <path
                            d="M3 17.25V21H6.75L17.81 9.94L14.06 6.19L3 17.25ZM20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C17.98 2.9 17.35 2.9 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04Z" />
                        </svg>
                      </button>
                      <button @click="$emit('delete', row)"
                        class="text-gray-500 hover:text-error-500 dark:text-gray-400 dark:hover:text-error-500">
                        <svg class="fill-current" width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <path
                            d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" />
                        </svg>
                      </button>
                    </div>
                  </slot>
                </div>

                <!-- Default Text -->
                <p v-else class="text-theme-sm text-gray-700 dark:text-gray-400">
                  {{ row[col.key] }}
                </p>
              </td>
            </template>
          </tr>
          <tr v-if="paginatedData.length === 0">
            <td :colspan="columns.length + (isSelectable ? 1 : 0)"
              class="border border-gray-100 px-4 py-8 text-center dark:border-gray-800">
              <p class="text-gray-500 dark:text-gray-400">No data available</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls -->
    <div class="rounded-b-xl border border-t-0 border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
      <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
        <p
          class="border-b border-gray-100 pb-3 text-center text-sm font-medium text-gray-500 xl:border-b-0 xl:pb-0 xl:text-left dark:border-gray-800 dark:text-gray-400">
          Showing {{ startEntry }} to {{ endEntry }} of
          {{ totalEntries }} entries
        </p>
        <div class="flex items-center justify-center gap-0.5 pt-3 xl:justify-end xl:pt-0">
          <button @click="prevPage" :disabled="currentPage === 1"
            class="mr-2.5 flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
            Previous
          </button>
          <button @click="goToPage(1)" :class="currentPage === 1
            ? 'bg-blue-500/[0.08] text-brand-500'
            : 'text-gray-700 dark:text-gray-400'
            "
            class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">
            1
          </button>
          <span v-if="currentPage > 3"
            class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">...</span>
          <button v-for="page in pagesAroundCurrent" :key="page" @click="goToPage(page)" :class="currentPage === page
            ? 'bg-blue-500/[0.08] text-brand-500'
            : 'text-gray-700 dark:text-gray-400'
            "
            class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">
            {{ page }}
          </button>
          <span v-if="currentPage < totalPages - 2"
            class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium text-gray-700 hover:bg-blue-500/[0.08] hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-500">...</span>
          <button v-if="totalPages > 1 && totalPages > 1" @click="goToPage(totalPages)" :class="currentPage === totalPages
            ? 'bg-blue-500/[0.08] text-brand-500'
            : 'text-gray-700 dark:text-gray-400'
            "
            class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium hover:bg-blue-500/[0.08] hover:text-brand-500 dark:hover:text-brand-500">
            {{ totalPages }}
          </button>
          <button @click="nextPage" :disabled="currentPage === totalPages"
            class="ml-2.5 flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-gray-700 shadow-theme-xs hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import { UserCircleIcon } from '@/icons';

export interface Action {
  label: string;
  type?: 'edit' | 'delete' | 'view' | 'custom';
  onClick?: (row: any) => void;
  icon?: any; // Component or string
  class?: string;
}

export interface Column {
  key: string;
  label: string;
  type?: 'text' | 'avatar' | 'cover' | 'status' | 'action' | 'custom';
  sortable?: boolean;
  class?: string;
  // For avatar/cover type
  avatarField?: string;
  labelField?: string;
  subLabelField?: string;
  // For status type
  statusMap?: Record<string, string>;
  // For action type
  actions?: Action[];
}

const props = defineProps<{
  columns: Column[];
  data: any[];
  itemsPerPage?: number;
  totalItems?: number;
  isServerSide?: boolean;
  currentPage?: number;
  selectable?: boolean;
}>();

const emit = defineEmits(['update:selection', 'edit', 'delete', 'view', 'download', 'update:page', 'update:search', 'update:sort', 'update:perPage']);

const search = ref('');
const sortColumn = ref('');
const sortDirection = ref('asc');
const currentPage = ref(props.currentPage || 1);
const perPage = ref(props.itemsPerPage || 10);
const selectAll = ref(false);
const isSelectable = computed(() => props.selectable !== false); // Default true if not provided

// Watch for prop changes in server-side mode
watch(() => props.currentPage, (newVal) => {
  if (newVal) currentPage.value = newVal;
});

watch(search, (newVal) => {
  if (props.isServerSide) {
    emit('update:search', newVal);
  }
});

watch(perPage, (newVal) => {
  if (props.isServerSide) {
    emit('update:perPage', newVal);
  }
});

// Initialize selected state in data
const localData = ref<any[]>([]);

watch(() => props.data, (newData) => {
  localData.value = newData.map(item => ({ ...item, selected: false }));
}, { immediate: true, deep: true });

const filteredData = computed(() => {
  if (props.isServerSide) {
    return localData.value;
  }

  const searchLower = search.value.toLowerCase();
  let result = [...localData.value];

  if (searchLower) {
    result = result.filter((item) => {
      return props.columns.some(col => {
        const val = item[col.key];
        return val && String(val).toLowerCase().includes(searchLower);
      });
    });
  }

  if (sortColumn.value) {
    result.sort((a, b) => {
      const modifier = sortDirection.value === 'asc' ? 1 : -1;
      const valA = a[sortColumn.value];
      const valB = b[sortColumn.value];

      if (valA < valB) return -1 * modifier;
      if (valA > valB) return 1 * modifier;
      return 0;
    });
  }

  return result;
});

const paginatedData = computed(() => {
  if (props.isServerSide) {
    return localData.value;
  }
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredData.value.slice(start, end);
});

const totalEntries = computed(() => props.isServerSide && props.totalItems !== undefined ? props.totalItems : filteredData.value.length);
const startEntry = computed(() => totalEntries.value === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1);
const endEntry = computed(() => {
  const end = currentPage.value * perPage.value;
  return end > totalEntries.value ? totalEntries.value : end;
});
const totalPages = computed(() =>
  Math.ceil(totalEntries.value / perPage.value),
);

const pagesAroundCurrent = computed(() => {
  const pages: number[] = [];
  if (totalPages.value <= 1) return pages;

  const startPage = Math.max(2, currentPage.value - 2);
  const endPage = Math.min(totalPages.value - 1, currentPage.value + 2);

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
});

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    if (props.isServerSide) {
      emit('update:page', page);
    } else {
      currentPage.value = page;
    }
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    if (props.isServerSide) {
      emit('update:page', currentPage.value + 1);
    } else {
      currentPage.value++;
    }
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    if (props.isServerSide) {
      emit('update:page', currentPage.value - 1);
    } else {
      currentPage.value--;
    }
  }
};

const sortBy = (column: string) => {
  if (props.isServerSide) {
    if (sortColumn.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortDirection.value = 'asc';
      sortColumn.value = column;
    }
    emit('update:sort', { column: sortColumn.value, direction: sortDirection.value });
    return;
  }

  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortDirection.value = 'asc';
    sortColumn.value = column;
  }
};

const toggleSelectAll = () => {
  localData.value.forEach((item) => (item.selected = selectAll.value));
  emitSelection();
};

const updateSelectAll = () => {
  selectAll.value = localData.value.length > 0 && localData.value.every((item) => item.selected);
  emitSelection();
};

const emitSelection = () => {
  const selectedItems = localData.value.filter(item => item.selected);
  emit('update:selection', selectedItems);
};

const getStatusClass = (status: string, map?: Record<string, string>) => {
  if (!map) return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
  return map[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
};

const getAvatarSrc = (row: any, col: Column) => {
  const val = row[col.avatarField || 'avatar'];
  if (val && typeof val === 'object' && 'url' in val) {
    return val.url;
  }
  return val;
};

const getAvatarPlaceholder = (row: any, col: Column) => {
  const val = row[col.avatarField || 'avatar'];
  if (val && typeof val === 'object' && 'placeholder' in val) {
    return val.placeholder;
  }
  return '';
};

const Icons = {
  view: `<svg class="fill-current" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" /></svg>`,
  edit: `<svg class="fill-current" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17.25V21H6.75L17.81 9.94L14.06 6.19L3 17.25ZM20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C17.98 2.9 17.35 2.9 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04Z" /></svg>`,
  delete: `<svg class="fill-current" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" /></svg>`
};

const getActionIcon = (action: Action) => {
  if (action.icon) return ''; // Will be handled in template
  if (action.type && Icons[action.type as keyof typeof Icons]) {
    return Icons[action.type as keyof typeof Icons];
  }
  return '';
};

const handleActionClick = (action: Action, row: any) => {
  if (action.onClick) {
    action.onClick(row);
  } else if (action.type === 'edit') {
    emit('edit', row);
  } else if (action.type === 'delete') {
    emit('delete', row);
  } else if (action.type === 'view') {
    emit('view', row);
  }
};
</script>

<style scoped>
/* Add any additional styles here */
</style>
