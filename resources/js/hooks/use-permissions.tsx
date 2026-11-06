import { usePage } from '@inertiajs/react';
import { PERMISSIONS } from '@/types/permissions';
import type { PermissionKey } from '@/types/permissions';

/**
 * usePermission
 *
 * Reads from auth.user.roles and auth.user.permissions.
 *
 * These are flat string arrays merged into the user object by
 * HandleInertiaRequests::share() via array_merge($user->toArray(), [...]).
 *
 * Shape expected:
 *   auth.user.roles       → string[]       e.g. ['admin']
 *   auth.user.permissions → PermissionKey[] e.g. ['posts.view', 'posts.create']
 *
 * NOT:
 *   auth.user.roles       → Role[]   (Eloquent objects — wrong)
 *   auth.user.permissions → []       (empty because model relation — wrong)
 *
 * super-admin and admin both get full UI access (same as Gate::before).
 */
export function usePermission() {
    const { auth } = usePage().props;

    const isSuperAdmin = auth?.user?.is_super_admin ?? false;
    const userPermissions: PermissionKey[] = (auth?.user?.permissions ??
        []) as PermissionKey[];
    const userRoles: string[] = auth?.user?.roles ?? [];

    const hasRole = (role: string): boolean => userRoles.includes(role);

    const hasAnyRole = (roles: string[]): boolean =>
        roles.some((r) => userRoles.includes(r));

    const hasFullAccess = isSuperAdmin || hasRole('admin');

    /**
     * Check one permission.
     * Always use the PERMISSIONS constant — never a raw string.
     *
     *   ✅ can(PERMISSIONS.POSTS.DELETE)
     *   ❌ can('posts.delete')
     */
    const can = (permission: PermissionKey): boolean =>
        hasFullAccess || userPermissions.includes(permission);

    /**
     * Check if user has ANY of the given permissions.
     *
     *   canAny([PERMISSIONS.POSTS.EDIT, PERMISSIONS.POSTS.DELETE])
     */
    const canAny = (permissions: PermissionKey[]): boolean =>
        hasFullAccess || permissions.some((p) => userPermissions.includes(p));

    /**
     * Check if user has ALL of the given permissions.
     *
     *   canAll([PERMISSIONS.POSTS.CREATE, PERMISSIONS.POSTS.PUBLISH])
     */
    const canAll = (permissions: PermissionKey[]): boolean =>
        hasFullAccess || permissions.every((p) => userPermissions.includes(p));

    return { can, canAny, canAll, hasRole, hasAnyRole };
}

export { PERMISSIONS };
