export const DASHBOARD_PERMISSIONS = {
    VIEW: 'view_dashboard',
} as const;

export const OPERATION_PERMISSIONS = {
    VIEW: 'view_operations',
} as const;

export const USER_PERMISSIONS = {
    VIEW: 'view_users',
    CREATE: 'create_users',
    EDIT: 'edit_users',
    DELETE: 'delete_users',
    ACTIVATE: 'activate_users',
} as const;

export const ROLE_PERMISSIONS = {
    VIEW: 'view_roles',
    CREATE: 'create_roles',
    EDIT: 'edit_roles',
    DELETE: 'delete_roles',
    ACTIVATE: 'activate_roles',
} as const;

export const WHATSAPP_CONFIG_PERMISSIONS = {
    VIEW: 'view_whatsapp_config',
    MANAGE: 'manage_whatsapp_config',
} as const;

export const APP_SETTINGS_PERMISSIONS = {
    VIEW: 'view_app_settings',
    MANAGE: 'manage_app_settings',
} as const;

// Gym Management
export const CUSTOMER_PERMISSIONS = {
    VIEW: 'view_customers',
    CREATE: 'create_customers',
    EDIT: 'edit_customers',
    DELETE: 'delete_customers',
} as const;

export const MEMBERSHIP_PACKAGE_PERMISSIONS = {
    VIEW: 'view_membership_packages',
    CREATE: 'create_membership_packages',
    EDIT: 'edit_membership_packages',
    DELETE: 'delete_membership_packages',
} as const;

export const MEMBERSHIP_PACKAGE_ITEM_PERMISSIONS = {
    VIEW: 'view_membership_package_items',
    CREATE: 'create_membership_package_items',
    EDIT: 'edit_membership_package_items',
    DELETE: 'delete_membership_package_items',
} as const;

export const MEMBERSHIP_TRANSACTION_PERMISSIONS = {
    VIEW: 'view_membership_transactions',
    CREATE: 'create_membership_transactions',
    EDIT: 'edit_membership_transactions',
    DELETE: 'delete_membership_transactions',
    CANCEL: 'cancel_membership_transactions',
} as const;

export const VISIT_PERMISSIONS = {
    VIEW: 'view_visits',
    CREATE: 'create_visits',
    EDIT: 'edit_visits',
    DELETE: 'delete_visits',
    CANCEL: 'cancel_visits',
} as const;

export const PRODUCT_PERMISSIONS = {
    VIEW: 'view_products',
    CREATE: 'create_products',
    EDIT: 'edit_products',
    DELETE: 'delete_products',
} as const;

export const STOCK_MOVEMENT_PERMISSIONS = {
    VIEW: 'view_stock_movements',
    CREATE: 'create_stock_movements',
    DELETE: 'delete_stock_movements',
} as const;

export const SALE_PERMISSIONS = {
    VIEW: 'view_sales',
    CREATE: 'create_sales',
    DELETE: 'delete_sales',
    CANCEL: 'cancel_sales',
} as const;
