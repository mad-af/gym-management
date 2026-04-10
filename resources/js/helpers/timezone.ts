import { usePage } from '@inertiajs/vue3';

export function useTimezone() {
    const appTimezone = (usePage().props.appTimezone as string) ?? 'UTC';

    const formatDateTimeId = (value: unknown): string => {
        if (!value) return '-';
        const d = value instanceof Date ? value : new Date(String(value));
        if (Number.isNaN(d.getTime())) return String(value);
        return new Intl.DateTimeFormat('id-ID', {
            timeZone: appTimezone,
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(d);
    };

    const formatDateTimeIdWithTz = (value: unknown): string => {
        if (!value) return '-';
        const d = value instanceof Date ? value : new Date(String(value));
        if (Number.isNaN(d.getTime())) return String(value);
        const local = new Intl.DateTimeFormat('id-ID', {
            timeZone: appTimezone,
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(d);
        const utc = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'UTC',
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            timeZoneName: 'short',
        }).format(d);
        return `${local} (${utc})`;
    };

    const formatDateId = (value: unknown): string => {
        if (!value) return '-';
        const raw = String(value);
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            const [y, m, d] = raw.split('-').map(Number);
            const dt = new Date(Date.UTC(y, (m || 1) - 1, d || 1));
            return new Intl.DateTimeFormat('id-ID', {
                timeZone: appTimezone,
                year: 'numeric',
                month: 'short',
                day: '2-digit',
            }).format(dt);
        }
        const dt = new Date(raw);
        if (Number.isNaN(dt.getTime())) return raw;
        return new Intl.DateTimeFormat('id-ID', {
            timeZone: appTimezone,
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        }).format(dt);
    };

    return {
        formatDateTimeId,
        formatDateTimeIdWithTz,
        formatDateId,
        appTimezone,
    };
}
