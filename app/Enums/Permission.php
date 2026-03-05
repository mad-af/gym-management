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

    // OPD Management
    case VIEW_OPDS = 'view_opds';
    case CREATE_OPDS = 'create_opds';
    case EDIT_OPDS = 'edit_opds';
    case DELETE_OPDS = 'delete_opds';
    case ACTIVATE_OPDS = 'activate_opds';

    // Employee Management
    case VIEW_EMPLOYEES = 'view_employees';
    case CREATE_EMPLOYEES = 'create_employees';
    case EDIT_EMPLOYEES = 'edit_employees';
    case DELETE_EMPLOYEES = 'delete_employees';
    case ACTIVATE_EMPLOYEES = 'activate_employees';

    // Room Management
    case VIEW_ROOMS = 'view_rooms';
    case CREATE_ROOMS = 'create_rooms';
    case EDIT_ROOMS = 'edit_rooms';
    case DELETE_ROOMS = 'delete_rooms';
    case ACTIVATE_ROOMS = 'activate_rooms';

    // Asset Category Management
    case VIEW_ASSET_CATEGORIES = 'view_asset_categories';
    case CREATE_ASSET_CATEGORIES = 'create_asset_categories';
    case EDIT_ASSET_CATEGORIES = 'edit_asset_categories';
    case DELETE_ASSET_CATEGORIES = 'delete_asset_categories';

    // Funding Source Management
    case VIEW_FUNDING_SOURCES = 'view_funding_sources';
    case CREATE_FUNDING_SOURCES = 'create_funding_sources';
    case EDIT_FUNDING_SOURCES = 'edit_funding_sources';
    case DELETE_FUNDING_SOURCES = 'delete_funding_sources';
    case ACTIVATE_FUNDING_SOURCES = 'activate_funding_sources';

    // Asset Management (Placeholder for future)
    case VIEW_ASSETS = 'view_assets';
    case CREATE_ASSETS = 'create_assets';
    case EDIT_ASSETS = 'edit_assets';
    case DELETE_ASSETS = 'delete_assets';
    case ACTIVATE_ASSETS = 'activate_assets';

    // Asset Transfer Management
    case VIEW_ASSET_TRANSFERS = 'view_asset_transfers';
    case CREATE_ASSET_TRANSFERS = 'create_asset_transfers';
    case APPROVE_ASSET_TRANSFERS = 'approve_asset_transfers';

    // Asset Disposal Management
    case VIEW_ASSET_DISPOSALS = 'view_asset_disposals';
    case CREATE_ASSET_DISPOSALS = 'create_asset_disposals';

    // Asset Maintenance Management
    case VIEW_ASSET_MAINTENANCES = 'view_asset_maintenances';
    case MANAGE_ASSET_MAINTENANCES = 'manage_asset_maintenances';

    // Asset Proposal Management
    case VIEW_ASSET_PROPOSALS = 'view_asset_proposals';
    case CREATE_ASSET_PROPOSALS = 'create_asset_proposals';
    case EDIT_ASSET_PROPOSALS = 'edit_asset_proposals';
    case DELETE_ASSET_PROPOSALS = 'delete_asset_proposals';

    // Room Inventory
    case VIEW_ROOM_INVENTORY = 'view_room_inventory';
    case GENERATE_ROOM_INVENTORY_PDF = 'generate_room_inventory_pdf';

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

    // Whatsapp Config
    case VIEW_WHATSAPP_CONFIG = 'view_whatsapp_config';
    case MANAGE_WHATSAPP_CONFIG = 'manage_whatsapp_config';

    /**
     * Get all permissions as an array of strings.
     */
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
            'Manajemen OPD' => [
                self::VIEW_OPDS,
                self::CREATE_OPDS,
                self::EDIT_OPDS,
                self::DELETE_OPDS,
                self::ACTIVATE_OPDS,
            ],
            'Manajemen Pegawai' => [
                self::VIEW_EMPLOYEES,
                self::CREATE_EMPLOYEES,
                self::EDIT_EMPLOYEES,
                self::DELETE_EMPLOYEES,
                self::ACTIVATE_EMPLOYEES,
            ],
            'Manajemen Ruangan' => [
                self::VIEW_ROOMS,
                self::CREATE_ROOMS,
                self::EDIT_ROOMS,
                self::DELETE_ROOMS,
                self::ACTIVATE_ROOMS,
            ],
            'Manajemen Kategori Aset' => [
                self::VIEW_ASSET_CATEGORIES,
                self::CREATE_ASSET_CATEGORIES,
                self::EDIT_ASSET_CATEGORIES,
                self::DELETE_ASSET_CATEGORIES,
            ],
            'Manajemen Sumber Pendanaan' => [
                self::VIEW_FUNDING_SOURCES,
                self::CREATE_FUNDING_SOURCES,
                self::EDIT_FUNDING_SOURCES,
                self::DELETE_FUNDING_SOURCES,
                self::ACTIVATE_FUNDING_SOURCES,
            ],
            'Manajemen Aset' => [
                self::VIEW_ASSETS,
                self::CREATE_ASSETS,
                self::EDIT_ASSETS,
                self::DELETE_ASSETS,
                self::ACTIVATE_ASSETS,
            ],
            'Manajemen Usulan Aset' => [
                self::VIEW_ASSET_PROPOSALS,
                self::CREATE_ASSET_PROPOSALS,
                self::EDIT_ASSET_PROPOSALS,
                self::DELETE_ASSET_PROPOSALS,
            ],
            'Manajemen Mutasi Aset' => [
                self::VIEW_ASSET_TRANSFERS,
                self::CREATE_ASSET_TRANSFERS,
                self::APPROVE_ASSET_TRANSFERS,
            ],
            'Manajemen Penghapusan Aset' => [
                self::VIEW_ASSET_DISPOSALS,
                self::CREATE_ASSET_DISPOSALS,
            ],
            'Manajemen Perawatan Aset' => [
                self::VIEW_ASSET_MAINTENANCES,
                self::MANAGE_ASSET_MAINTENANCES,
            ],
            'Manajemen Inventaris Ruangan' => [
                self::VIEW_ROOM_INVENTORY,
                self::GENERATE_ROOM_INVENTORY_PDF,
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

            self::VIEW_OPDS => 'Lihat OPD',
            self::CREATE_OPDS => 'Tambah OPD',
            self::EDIT_OPDS => 'Edit OPD',
            self::DELETE_OPDS => 'Hapus OPD',
            self::ACTIVATE_OPDS => 'Aktivasi OPD',

            self::VIEW_EMPLOYEES => 'Lihat Pegawai',
            self::CREATE_EMPLOYEES => 'Tambah Pegawai',
            self::EDIT_EMPLOYEES => 'Edit Pegawai',
            self::DELETE_EMPLOYEES => 'Hapus Pegawai',
            self::ACTIVATE_EMPLOYEES => 'Aktivasi Pegawai',

            self::VIEW_ROOMS => 'Lihat Ruangan',
            self::CREATE_ROOMS => 'Tambah Ruangan',
            self::EDIT_ROOMS => 'Edit Ruangan',
            self::DELETE_ROOMS => 'Hapus Ruangan',
            self::ACTIVATE_ROOMS => 'Aktivasi Ruangan',

            self::VIEW_ASSET_CATEGORIES => 'Lihat Kategori Aset',
            self::CREATE_ASSET_CATEGORIES => 'Tambah Kategori Aset',
            self::EDIT_ASSET_CATEGORIES => 'Edit Kategori Aset',
            self::DELETE_ASSET_CATEGORIES => 'Hapus Kategori Aset',

            self::VIEW_FUNDING_SOURCES => 'Lihat Sumber Pendanaan',
            self::CREATE_FUNDING_SOURCES => 'Tambah Sumber Pendanaan',
            self::EDIT_FUNDING_SOURCES => 'Edit Sumber Pendanaan',
            self::DELETE_FUNDING_SOURCES => 'Hapus Sumber Pendanaan',
            self::ACTIVATE_FUNDING_SOURCES => 'Aktivasi Sumber Pendanaan',

            self::VIEW_ASSETS => 'Lihat Aset',
            self::CREATE_ASSETS => 'Tambah Aset',
            self::EDIT_ASSETS => 'Edit Aset',
            self::DELETE_ASSETS => 'Hapus Aset',
            self::ACTIVATE_ASSETS => 'Aktivasi Aset',

            self::VIEW_ASSET_PROPOSALS => 'Lihat Usulan Aset',
            self::CREATE_ASSET_PROPOSALS => 'Tambah Usulan Aset',
            self::EDIT_ASSET_PROPOSALS => 'Edit Usulan Aset',
            self::DELETE_ASSET_PROPOSALS => 'Hapus Usulan Aset',

            self::VIEW_ASSET_TRANSFERS => 'Lihat Mutasi Aset',
            self::CREATE_ASSET_TRANSFERS => 'Buat Mutasi Aset',
            self::APPROVE_ASSET_TRANSFERS => 'Persetujuan Mutasi Aset',

            self::VIEW_ASSET_DISPOSALS => 'Lihat Penghapusan Aset',
            self::CREATE_ASSET_DISPOSALS => 'Buat Dokumen Penghapusan Aset',

            self::VIEW_ASSET_MAINTENANCES => 'Lihat Perawatan Aset',
            self::MANAGE_ASSET_MAINTENANCES => 'Kelola Perawatan Aset',

            self::VIEW_ROOM_INVENTORY => 'Lihat Inventaris Ruangan',
            self::GENERATE_ROOM_INVENTORY_PDF => 'Generate PDF Inventaris Ruangan',

            self::VIEW_WHATSAPP_CONFIG => 'Lihat Konfigurasi WhatsApp',
            self::MANAGE_WHATSAPP_CONFIG => 'Kelola Konfigurasi WhatsApp',

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
