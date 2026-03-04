import { usePage } from '@inertiajs/vue3';
import type { ObjectDirective, DirectiveBinding } from 'vue';

type CanValue =
    | string
    | string[]
    | {
          permission?: string | string[];
          permissions?: string | string[];
          mode?: 'any' | 'all';
      };

type CanBinding = DirectiveBinding<CanValue>;

const getRequiredPermissions = (binding: CanBinding): { required: string[]; mode: 'any' | 'all' } => {
    const value = binding.value;

    if (typeof value === 'string') {
        return { required: [value], mode: 'any' };
    }

    if (Array.isArray(value)) {
        return { required: value.filter(Boolean), mode: 'any' };
    }

    if (value && typeof value === 'object') {
        const permissions = value.permission ?? value.permissions ?? [];
        const mode: 'any' | 'all' = value.mode === 'all' ? 'all' : 'any';

        if (typeof permissions === 'string') {
            return { required: [permissions], mode };
        }

        if (Array.isArray(permissions)) {
            return { required: permissions.filter(Boolean), mode };
        }
    }

    return { required: [], mode: 'any' };
};

const checkPermissions = (userPermissions: string[], required: string[], mode: 'any' | 'all'): boolean => {
    if (!required.length) {
        return true;
    }

    if (!userPermissions || !userPermissions.length) {
        return false;
    }

    if (mode === 'all') {
        return required.every((permission) => userPermissions.includes(permission));
    }

    return required.some((permission) => userPermissions.includes(permission));
};

const applyPermission = (el: HTMLElement, binding: CanBinding) => {
    const page: any = usePage();
    const userPermissions: string[] = page?.props?.auth?.permissions ?? [];
    const { required, mode } = getRequiredPermissions(binding);

    const allowed = checkPermissions(userPermissions, required, mode);

    if (!allowed && el.parentNode) {
        el.parentNode.removeChild(el);
    }
};

const canDirective: ObjectDirective = {
    mounted(el, binding) {
        applyPermission(el as HTMLElement, binding as CanBinding);
    },
    updated(el, binding) {
        applyPermission(el as HTMLElement, binding as CanBinding);
    },
};

export default canDirective;
