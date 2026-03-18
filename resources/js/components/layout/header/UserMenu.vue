<template>
    <div class="relative" ref="dropdownRef">
        <button
            class="flex items-center text-gray-700 dark:text-gray-400"
            @click.prevent="toggleDropdown"
        >
            <AppImage
                container-class="mr-3 h-11 w-11 rounded-full"
                :src="userAvatar.src"
                :placeholder="userAvatar.placeholder"
                :alt="user?.name || 'Pengguna'"
            />

            <span class="mr-1 block text-theme-sm font-medium">{{
                user?.name || 'Tamu'
            }}</span>

            <ChevronDownIcon :class="{ 'rotate-180': dropdownOpen }" />
        </button>

        <!-- Dropdown Start -->
        <div
            v-show="dropdownOpen"
            class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
        >
            <div>
                <span
                    class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    {{ user?.name || 'Tamu' }}
                </span>
                <span
                    class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400"
                >
                    {{ user?.email || 'Tidak ada email' }}
                </span>
            </div>

            <ul
                class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3 dark:border-gray-800"
            >
                <li
                    v-for="item in menuItems"
                    :key="item.href"
                    v-can="item.permission"
                >
                    <Link
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                    >
                        <!-- SVG icon would go here -->
                        <component
                            :is="item.icon"
                            class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                        />
                        {{ item.text }}
                    </Link>
                </li>
                <EditPasswordMenuItem @click="closeDropdown" />
            </ul>
            <Link
                href="/logout"
                method="post"
                as="button"
                @click="closeDropdown"
                class="group mt-3 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            >
                <LogoutIcon
                    class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                />
                Keluar
            </Link>
        </div>
        <!-- Dropdown End -->
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import EditPasswordMenuItem from '@/components/layout/header/EditPasswordMenuItem.vue';
import { USER_PERMISSIONS } from '@/directives/permissions';
import {
    UserCircleIcon,
    ChevronDownIcon,
    LogoutIcon,
    // InfoCircleIcon,
} from '@/icons';

const page = usePage();
// Cast user to any to avoid strict type checking on dynamic avatar field
const user = computed(() => page.props.auth.user as any);

const userAvatar = computed(() => {
    const avatar = user.value?.avatar;
    const defaultAvatar = '/images/user/owner.jpg';

    if (avatar && typeof avatar === 'object') {
        return {
            src: avatar.url || defaultAvatar,
            placeholder: avatar.placeholder || '',
        };
    }

    return {
        src: avatar || defaultAvatar,
        placeholder: '',
    };
});

const dropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

interface MenuItem {
    href: string;
    icon: any;
    text: string;
    permission?: string | string[];
}

const menuItems = computed<MenuItem[]>(() => [
    {
        href: `/users?search=${encodeURIComponent(user.value?.name || user.value?.email || '')}`,
        icon: UserCircleIcon,
        text: 'Ubah Profil',
        permission: USER_PERMISSIONS.VIEW,
    },
    // { href: '/profile', icon: InfoCircleIcon, text: 'Support' },
]);

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
