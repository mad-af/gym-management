<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-99999 mt-16 flex h-screen flex-col border-r border-gray-200 bg-white px-5 text-gray-900 transition-all duration-300 ease-in-out lg:mt-0 dark:border-gray-800 dark:bg-gray-900',
            {
                'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
                'lg:w-[90px]': !isExpanded && !isHovered,
                'w-[290px] translate-x-0': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded && (isHovered = true)"
        @mouseleave="isHovered = false"
    >
        <div
            :class="[
                'flex py-8',
                !isExpanded && !isHovered
                    ? 'lg:justify-center'
                    : 'justify-start',
            ]"
        >
            <Link href="/dashboard">
                <ApplicationLogo
                    :collapsed="!isExpanded && !isHovered && !isMobileOpen"
                />
            </Link>
        </div>
        <div
            class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear"
        >
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <div
                        v-for="(menuGroup, groupIndex) in visibleMenuGroups"
                        :key="groupIndex"
                    >
                        <h2
                            :class="[
                                'mb-4 flex text-xs leading-[20px] text-gray-400 uppercase',
                                !isExpanded && !isHovered
                                    ? 'lg:justify-center'
                                    : 'justify-start',
                            ]"
                        >
                            <template
                                v-if="isExpanded || isHovered || isMobileOpen"
                            >
                                {{ menuGroup.title }}
                            </template>
                            <MoreDots v-else />
                        </h2>
                        <ul class="flex flex-col gap-4">
                            <li
                                v-for="(item, index) in menuGroup.items"
                                :key="item.name"
                                v-can="item.permission"
                            >
                                <button
                                    v-if="item.subItems"
                                    @click="toggleSubmenu(groupIndex, index)"
                                    :class="[
                                        'group menu-item w-full',
                                        {
                                            'menu-item-active': isSubmenuOpen(
                                                groupIndex,
                                                index,
                                            ),
                                            'menu-item-inactive':
                                                !isSubmenuOpen(
                                                    groupIndex,
                                                    index,
                                                ),
                                        },
                                        !isExpanded && !isHovered
                                            ? 'lg:justify-center'
                                            : 'lg:justify-start',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            isSubmenuOpen(groupIndex, index)
                                                ? 'menu-item-icon-active'
                                                : 'menu-item-icon-inactive',
                                        ]"
                                    >
                                        <component :is="item.icon" />
                                    </span>
                                    <span
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                        class="menu-item-text"
                                        >{{ item.name }}</span
                                    >
                                    <ChevronDownIcon
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                        :class="[
                                            'ml-auto h-5 w-5 transition-transform duration-200',
                                            {
                                                'rotate-180 text-brand-500':
                                                    isSubmenuOpen(
                                                        groupIndex,
                                                        index,
                                                    ),
                                            },
                                        ]"
                                    />
                                </button>
                                <Link
                                    v-else-if="item.path"
                                    :href="item.path"
                                    :class="[
                                        'group menu-item',
                                        {
                                            'menu-item-active': isActive(
                                                item.path,
                                            ),
                                            'menu-item-inactive': !isActive(
                                                item.path,
                                            ),
                                        },
                                    ]"
                                >
                                    <span
                                        :class="[
                                            isActive(item.path)
                                                ? 'menu-item-icon-active'
                                                : 'menu-item-icon-inactive',
                                        ]"
                                    >
                                        <component :is="item.icon" />
                                    </span>
                                    <span
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                        class="menu-item-text"
                                        >{{ item.name }}</span
                                    >
                                </Link>
                                <transition
                                    @enter="startTransition"
                                    @after-enter="endTransition"
                                    @before-leave="startTransition"
                                    @after-leave="endTransition"
                                >
                                    <div
                                        v-show="
                                            isSubmenuOpen(groupIndex, index) &&
                                            (isExpanded ||
                                                isHovered ||
                                                isMobileOpen)
                                        "
                                    >
                                        <ul class="mt-2 ml-9 space-y-1">
                                            <li
                                                v-for="subItem in item.subItems"
                                                :key="subItem.name"
                                            >
                                                <Link
                                                    :href="subItem.path"
                                                    :class="[
                                                        'menu-dropdown-item',
                                                        {
                                                            'menu-dropdown-item-active':
                                                                isActive(
                                                                    subItem.path,
                                                                ),
                                                            'menu-dropdown-item-inactive':
                                                                !isActive(
                                                                    subItem.path,
                                                                ),
                                                        },
                                                    ]"
                                                >
                                                    {{ subItem.name }}
                                                    <span
                                                        class="ml-auto flex items-center gap-1"
                                                    >
                                                        <span
                                                            v-if="subItem.new"
                                                            :class="[
                                                                'menu-dropdown-badge',
                                                                {
                                                                    'menu-dropdown-badge-active':
                                                                        isActive(
                                                                            subItem.path,
                                                                        ),
                                                                    'menu-dropdown-badge-inactive':
                                                                        !isActive(
                                                                            subItem.path,
                                                                        ),
                                                                },
                                                            ]"
                                                        >
                                                            new
                                                        </span>
                                                        <span
                                                            v-if="subItem.pro"
                                                            :class="[
                                                                'menu-dropdown-badge',
                                                                {
                                                                    'menu-dropdown-badge-active':
                                                                        isActive(
                                                                            subItem.path,
                                                                        ),
                                                                    'menu-dropdown-badge-inactive':
                                                                        !isActive(
                                                                            subItem.path,
                                                                        ),
                                                                },
                                                            ]"
                                                        >
                                                            pro
                                                        </span>
                                                    </span>
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </transition>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- <SidebarWidget v-if="isExpanded || isHovered || isMobileOpen" /> -->
        </div>
    </aside>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ApplicationLogo from '@/components/common/ApplicationLogo.vue';
import { useSidebar } from '@/composables/useSidebar';
import {
    USER_PERMISSIONS,
    ROLE_PERMISSIONS,
    CUSTOMER_PERMISSIONS,
    MEMBERSHIP_PACKAGE_PERMISSIONS,
    // MEMBERSHIP_PACKAGE_ITEM_PERMISSIONS,
    MEMBERSHIP_TRANSACTION_PERMISSIONS,
    VISIT_PERMISSIONS,
    PRODUCT_PERMISSIONS,
    STOCK_MOVEMENT_PERMISSIONS,
    SALE_PERMISSIONS,
    WHATSAPP_CONFIG_PERMISSIONS,
} from '@/directives/permissions';
import {
    LayoutDashboardIcon,
    UserCircleIcon,
    UserGroupIcon,
    PackageIcon,
    BanknoteIcon,
    ShieldCheckIcon,
    WrenchIcon,
    MoreDots,
    ChevronDownIcon,
    DoorOpenIcon,
    GridIcon,
} from '../../icons';

const page = usePage();

const { isExpanded, isMobileOpen, isHovered } = useSidebar();

const isActive = (path: string) => page.url === path;

interface SubItem {
    name: string;
    path: string;
    pro?: boolean;
    new?: boolean;
}

interface MenuItem {
    icon: any;
    name: string;
    path?: string;
    permission?: string | string[];
    subItems?: SubItem[];
}

interface MenuGroup {
    title: string;
    items: MenuItem[];
}

const menuGroups = ref<MenuGroup[]>([
    {
        title: 'General',
        items: [
            {
                icon: LayoutDashboardIcon,
                name: 'Dashboard',
                path: '/dashboard',
            },
            {
                icon: WrenchIcon,
                name: 'Operasional',
                path: '/operations',
            },
            {
                icon: ShieldCheckIcon,
                name: 'Setting Aplikasi',
                path: '/settings/application',
            },
            {
                icon: ShieldCheckIcon,
                name: 'WhatsApp Config',
                path: '/settings/whatsapp-config',
                permission: WHATSAPP_CONFIG_PERMISSIONS.VIEW,
            },
        ],
    },
    {
        title: 'Transactions',
        items: [
            {
                icon: DoorOpenIcon,
                name: 'Visits / Check In',
                path: '/visits',
                permission: VISIT_PERMISSIONS.VIEW,
            },
            {
                icon: PackageIcon,
                name: 'Membership Transactions',
                path: '/membership/transactions',
                permission: MEMBERSHIP_TRANSACTION_PERMISSIONS.VIEW,
            },
            {
                icon: BanknoteIcon,
                name: 'Sales',
                path: '/sales',
                permission: SALE_PERMISSIONS.VIEW,
            },
            {
                icon: GridIcon,
                name: 'Stock Movements',
                path: '/inventory/stock-movements',
                permission: STOCK_MOVEMENT_PERMISSIONS.VIEW,
            },
        ],
    },
    {
        title: 'Master',
        items: [
            {
                icon: PackageIcon,
                name: 'Membership Packages',
                path: '/membership/packages',
                permission: MEMBERSHIP_PACKAGE_PERMISSIONS.VIEW,
            },
            {
                icon: GridIcon,
                name: 'Products',
                path: '/inventory/products',
                permission: PRODUCT_PERMISSIONS.VIEW,
            },
            {
                icon: UserGroupIcon,
                name: 'Customers',
                path: '/customers',
                permission: CUSTOMER_PERMISSIONS.VIEW,
            },
            {
                icon: UserCircleIcon,
                name: 'Manajemen Pengguna',
                path: '/users',
                permission: USER_PERMISSIONS.VIEW,
            },
            {
                icon: ShieldCheckIcon,
                name: 'Role & Hak Akses',
                path: '/roles',
                permission: ROLE_PERMISSIONS.VIEW,
            },
        ],
    },
]);

const userPermissions = computed<string[]>(() => {
    const props: any = page.props;
    return props?.auth?.permissions ?? [];
});

const canViewItem = (item: MenuItem): boolean => {
    if (!item.permission) {
        return true;
    }

    const permissions = userPermissions.value;

    if (!permissions || !permissions.length) {
        return false;
    }

    if (Array.isArray(item.permission)) {
        return item.permission.some((permission) =>
            permissions.includes(permission),
        );
    }

    return permissions.includes(item.permission);
};

const visibleMenuGroups = computed<MenuGroup[]>(() =>
    menuGroups.value
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => canViewItem(item)),
        }))
        .filter((group) => group.items.length > 0),
);

const openSubmenuIndex = ref<number | null>(null);
const openSubmenuParentIndex = ref<number | null>(null);

const toggleSubmenu = (parentIndex: number, index: number) => {
    if (
        openSubmenuParentIndex.value === parentIndex &&
        openSubmenuIndex.value === index
    ) {
        openSubmenuParentIndex.value = null;
        openSubmenuIndex.value = null;
    } else {
        openSubmenuParentIndex.value = parentIndex;
        openSubmenuIndex.value = index;
    }
};

const isSubmenuOpen = (parentIndex: number, index: number) => {
    const isManuallyOpen =
        openSubmenuParentIndex.value === parentIndex &&
        openSubmenuIndex.value === index;

    const group = visibleMenuGroups.value[parentIndex];
    const item = group?.items[index];

    const hasActiveSubItem =
        item?.subItems?.some((subItem) => isActive(subItem.path)) ?? false;

    return isManuallyOpen || hasActiveSubItem;
};

const startTransition = (el: any) => {
    el.style.height = '0';
    el.style.opacity = '0';
};

const endTransition = (el: any) => {
    el.style.height = el.scrollHeight + 'px';
    el.style.opacity = '1';
};
</script>
