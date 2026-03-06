<?php

namespace App\Enums;

enum Permission: string
{
    // User Management
    case VIEW_USERS = 'view_users';
    case CREATE_USERS = 'create_users';
    case EDIT_USERS = 'edit_users';
    case DELETE_USERS = 'delete_users';
    case ACTIVATE_USERS = 'activate_users';

    // Role Management
    case VIEW_ROLES = 'view_roles';
    case CREATE_ROLES = 'create_roles';
    case EDIT_ROLES = 'edit_roles';
    case DELETE_ROLES = 'delete_roles';
    case ACTIVATE_ROLES = 'activate_roles';

    // Asset Management (Restored for Dashboard)
    case VIEW_ASSETS = 'view_assets';
    case CREATE_ASSETS = 'create_assets';
    case EDIT_ASSETS = 'edit_assets';
    case DELETE_ASSETS = 'delete_assets';

    // Asset Proposals (Restored for Dashboard)
    case VIEW_ASSET_PROPOSALS = 'view_asset_proposals';

    // Asset Maintenances (Restored for Dashboard)
    case VIEW_ASSET_MAINTENANCES = 'view_asset_maintenances';

    // Gym: Customers
    case VIEW_CUSTOMERS = 'view_customers';
    case CREATE_CUSTOMERS = 'create_customers';
    case EDIT_CUSTOMERS = 'edit_customers';
    case DELETE_CUSTOMERS = 'delete_customers';

    // Gym: Membership Packages
    case VIEW_MEMBERSHIP_PACKAGES = 'view_membership_packages';
    case CREATE_MEMBERSHIP_PACKAGES = 'create_membership_packages';
    case EDIT_MEMBERSHIP_PACKAGES = 'edit_membership_packages';
    case DELETE_MEMBERSHIP_PACKAGES = 'delete_membership_packages';

    // Gym: Membership Package Items
    case VIEW_MEMBERSHIP_PACKAGE_ITEMS = 'view_membership_package_items';
    case CREATE_MEMBERSHIP_PACKAGE_ITEMS = 'create_membership_package_items';
    case EDIT_MEMBERSHIP_PACKAGE_ITEMS = 'edit_membership_package_items';
    case DELETE_MEMBERSHIP_PACKAGE_ITEMS = 'delete_membership_package_items';

    // Gym: Membership Transactions
    case VIEW_MEMBERSHIP_TRANSACTIONS = 'view_membership_transactions';
    case CREATE_MEMBERSHIP_TRANSACTIONS = 'create_membership_transactions';
    case EDIT_MEMBERSHIP_TRANSACTIONS = 'edit_membership_transactions';
    case DELETE_MEMBERSHIP_TRANSACTIONS = 'delete_membership_transactions';

    // Gym: Visits
    case VIEW_VISITS = 'view_visits';
    case CREATE_VISITS = 'create_visits';
    case EDIT_VISITS = 'edit_visits';
    case DELETE_VISITS = 'delete_visits';

    // Gym: Products
    case VIEW_PRODUCTS = 'view_products';
    case CREATE_PRODUCTS = 'create_products';
    case EDIT_PRODUCTS = 'edit_products';
    case DELETE_PRODUCTS = 'delete_products';

    // Gym: Stock Movements
    case VIEW_STOCK_MOVEMENTS = 'view_stock_movements';
    case CREATE_STOCK_MOVEMENTS = 'create_stock_movements';
    case DELETE_STOCK_MOVEMENTS = 'delete_stock_movements';

    // Gym: Sales
    case VIEW_SALES = 'view_sales';
    case CREATE_SALES = 'create_sales';
    case DELETE_SALES = 'delete_sales';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function grouped(): array
    {
        $groups = [
            'Manajemen Pengguna' => [
                self::VIEW_USERS,
                self::CREATE_USERS,
                self::EDIT_USERS,
                self::DELETE_USERS,
                self::ACTIVATE_USERS,
            ],
            'Manajemen Role' => [
                self::VIEW_ROLES,
                self::CREATE_ROLES,
                self::EDIT_ROLES,
                self::DELETE_ROLES,
                self::ACTIVATE_ROLES,
            ],
            'Manajemen Aset (Dashboard)' => [
                self::VIEW_ASSETS,
                self::CREATE_ASSETS,
                self::EDIT_ASSETS,
                self::DELETE_ASSETS,
            ],
            'Manajemen Proposal Aset' => [
                self::VIEW_ASSET_PROPOSALS,
            ],
            'Manajemen Pemeliharaan Aset' => [
                self::VIEW_ASSET_MAINTENANCES,
            ],
            'Manajemen Pelanggan' => [
                self::VIEW_CUSTOMERS,
                self::CREATE_CUSTOMERS,
                self::EDIT_CUSTOMERS,
                self::DELETE_CUSTOMERS,
            ],
            'Manajemen Paket Membership' => [
                self::VIEW_MEMBERSHIP_PACKAGES,
                self::CREATE_MEMBERSHIP_PACKAGES,
                self::EDIT_MEMBERSHIP_PACKAGES,
                self::DELETE_MEMBERSHIP_PACKAGES,
            ],
            'Manajemen Item Paket Membership' => [
                self::VIEW_MEMBERSHIP_PACKAGE_ITEMS,
                self::CREATE_MEMBERSHIP_PACKAGE_ITEMS,
                self::EDIT_MEMBERSHIP_PACKAGE_ITEMS,
                self::DELETE_MEMBERSHIP_PACKAGE_ITEMS,
            ],
            'Manajemen Transaksi Membership' => [
                self::VIEW_MEMBERSHIP_TRANSACTIONS,
                self::CREATE_MEMBERSHIP_TRANSACTIONS,
                self::EDIT_MEMBERSHIP_TRANSACTIONS,
                self::DELETE_MEMBERSHIP_TRANSACTIONS,
            ],
            'Manajemen Kunjungan' => [
                self::VIEW_VISITS,
                self::CREATE_VISITS,
                self::EDIT_VISITS,
                self::DELETE_VISITS,
            ],
            'Manajemen Produk' => [
                self::VIEW_PRODUCTS,
                self::CREATE_PRODUCTS,
                self::EDIT_PRODUCTS,
                self::DELETE_PRODUCTS,
            ],
            'Manajemen Pergerakan Stok' => [
                self::VIEW_STOCK_MOVEMENTS,
                self::CREATE_STOCK_MOVEMENTS,
                self::DELETE_STOCK_MOVEMENTS,
            ],
            'Manajemen Penjualan' => [
                self::VIEW_SALES,
                self::CREATE_SALES,
                self::DELETE_SALES,
            ],
        ];

        return collect($groups)->map(function ($permissions, $group) {
            return [
                'group' => $group,
                'permissions' => collect($permissions)->map(function ($permission) {
                    return [
                        'id' => $permission->value,
                        'name' => $permission->value,
                        'label' => $permission->label(),
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::VIEW_USERS => 'Lihat Pengguna',
            self::CREATE_USERS => 'Tambah Pengguna',
            self::EDIT_USERS => 'Edit Pengguna',
            self::DELETE_USERS => 'Hapus Pengguna',
            self::ACTIVATE_USERS => 'Aktivasi Pengguna',

            self::VIEW_ROLES => 'Lihat Role',
            self::CREATE_ROLES => 'Tambah Role',
            self::EDIT_ROLES => 'Edit Role',
            self::DELETE_ROLES => 'Hapus Role',
            self::ACTIVATE_ROLES => 'Aktivasi Role',

            self::VIEW_ASSETS => 'Lihat Aset',
            self::CREATE_ASSETS => 'Tambah Aset',
            self::EDIT_ASSETS => 'Edit Aset',
            self::DELETE_ASSETS => 'Hapus Aset',

            self::VIEW_ASSET_PROPOSALS => 'Lihat Proposal Aset',

            self::VIEW_ASSET_MAINTENANCES => 'Lihat Pemeliharaan Aset',

            self::VIEW_CUSTOMERS => 'Lihat Pelanggan',
            self::CREATE_CUSTOMERS => 'Tambah Pelanggan',
            self::EDIT_CUSTOMERS => 'Edit Pelanggan',
            self::DELETE_CUSTOMERS => 'Hapus Pelanggan',

            self::VIEW_MEMBERSHIP_PACKAGES => 'Lihat Paket Membership',
            self::CREATE_MEMBERSHIP_PACKAGES => 'Tambah Paket Membership',
            self::EDIT_MEMBERSHIP_PACKAGES => 'Edit Paket Membership',
            self::DELETE_MEMBERSHIP_PACKAGES => 'Hapus Paket Membership',

            self::VIEW_MEMBERSHIP_PACKAGE_ITEMS => 'Lihat Item Paket Membership',
            self::CREATE_MEMBERSHIP_PACKAGE_ITEMS => 'Tambah Item Paket Membership',
            self::EDIT_MEMBERSHIP_PACKAGE_ITEMS => 'Edit Item Paket Membership',
            self::DELETE_MEMBERSHIP_PACKAGE_ITEMS => 'Hapus Item Paket Membership',

            self::VIEW_MEMBERSHIP_TRANSACTIONS => 'Lihat Transaksi Membership',
            self::CREATE_MEMBERSHIP_TRANSACTIONS => 'Tambah Transaksi Membership',
            self::EDIT_MEMBERSHIP_TRANSACTIONS => 'Edit Transaksi Membership',
            self::DELETE_MEMBERSHIP_TRANSACTIONS => 'Hapus Transaksi Membership',

            self::VIEW_VISITS => 'Lihat Kunjungan',
            self::CREATE_VISITS => 'Tambah Kunjungan',
            self::EDIT_VISITS => 'Edit Kunjungan',
            self::DELETE_VISITS => 'Hapus Kunjungan',

            self::VIEW_PRODUCTS => 'Lihat Produk',
            self::CREATE_PRODUCTS => 'Tambah Produk',
            self::EDIT_PRODUCTS => 'Edit Produk',
            self::DELETE_PRODUCTS => 'Hapus Produk',

            self::VIEW_STOCK_MOVEMENTS => 'Lihat Pergerakan Stok',
            self::CREATE_STOCK_MOVEMENTS => 'Tambah Pergerakan Stok',
            self::DELETE_STOCK_MOVEMENTS => 'Hapus Pergerakan Stok',

            self::VIEW_SALES => 'Lihat Penjualan',
            self::CREATE_SALES => 'Tambah Penjualan',
            self::DELETE_SALES => 'Hapus Penjualan',
        };
    }
}
