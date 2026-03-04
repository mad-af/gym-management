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

export const OPD_PERMISSIONS = {
    VIEW: 'view_opds',
    CREATE: 'create_opds',
    EDIT: 'edit_opds',
    DELETE: 'delete_opds',
    ACTIVATE: 'activate_opds',
} as const;

export const EMPLOYEE_PERMISSIONS = {
    VIEW: 'view_employees',
    CREATE: 'create_employees',
    EDIT: 'edit_employees',
    DELETE: 'delete_employees',
    ACTIVATE: 'activate_employees',
} as const;

export const ROOM_PERMISSIONS = {
    VIEW: 'view_rooms',
    CREATE: 'create_rooms',
    EDIT: 'edit_rooms',
    DELETE: 'delete_rooms',
    ACTIVATE: 'activate_rooms',
} as const;

export const ASSET_CATEGORY_PERMISSIONS = {
    VIEW: 'view_asset_categories',
    CREATE: 'create_asset_categories',
    EDIT: 'edit_asset_categories',
    DELETE: 'delete_asset_categories',
} as const;

export const FUNDING_SOURCE_PERMISSIONS = {
    VIEW: 'view_funding_sources',
    CREATE: 'create_funding_sources',
    EDIT: 'edit_funding_sources',
    DELETE: 'delete_funding_sources',
    ACTIVATE: 'activate_funding_sources',
} as const;

export const ASSET_PERMISSIONS = {
    VIEW: 'view_assets',
    CREATE: 'create_assets',
    EDIT: 'edit_assets',
    DELETE: 'delete_assets',
    ACTIVATE: 'activate_assets',
} as const;

export const ASSET_TRANSFER_PERMISSIONS = {
    VIEW: 'view_asset_transfers',
    CREATE: 'create_asset_transfers',
    APPROVE: 'approve_asset_transfers',
} as const;

export const ASSET_DISPOSAL_PERMISSIONS = {
    VIEW: 'view_asset_disposals',
    CREATE: 'create_asset_disposals',
} as const;

export const ASSET_MAINTENANCE_PERMISSIONS = {
    VIEW: 'view_asset_maintenances',
    MANAGE: 'manage_asset_maintenances',
} as const;

export const ASSET_PROPOSAL_PERMISSIONS = {
    VIEW: 'view_asset_proposals',
    CREATE: 'create_asset_proposals',
    EDIT: 'edit_asset_proposals',
    DELETE: 'delete_asset_proposals',
} as const;

export const ROOM_INVENTORY_PERMISSIONS = {
    VIEW: 'view_room_inventory',
} as const;

export const WHATSAPP_CONFIG_PERMISSIONS = {
    VIEW: 'view_whatsapp_config',
    MANAGE: 'manage_whatsapp_config',
} as const;
