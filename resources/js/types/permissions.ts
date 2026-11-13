// =============================================================================
// PERMISSIONS — Fixed, developer-defined, seeded from CSV.
// Never created, edited, or deleted from the UI.
// To add a new permission: add it here + permissions.csv, re-run seeder.
// =============================================================================

export const PERMISSIONS = {
    DASHBOARD: {
        VIEW: 'dashboard.view',
    },
    REPOSITORY: {
        VIEW: 'repository.view',
    },
    DOCUMENTATION: {
        VIEW: 'documentation.view',
    },
    FILE_UPLOAD: {
        INDEX: 'file-upload.index',
    },
    POSTS: {
        INDEX: 'posts.index',
        VIEW: 'posts.view',
        CREATE: 'posts.create',
        EDIT: 'posts.edit',
        DELETE: 'posts.delete',
        PUBLISH: 'posts.publish',
    },
    USERS: {
        INDEX: 'users.index',
        VIEW: 'users.view',
        CREATE: 'users.create',
        EDIT: 'users.edit',
        DELETE: 'users.delete',
        IMPERSONATE: 'users.impersonate',
    },
    ROLES: {
        INDEX: 'roles.index',
        VIEW: 'roles.view',
        CREATE: 'roles.create',
        EDIT: 'roles.edit',
        DELETE: 'roles.delete',
    },
    PERMISSIONS: {
        INDEX: 'permissions.index',
        EXPORT: 'permissions.export',
    },
    SETTINGS: {
        INDEX: 'settings.index',
        VIEW: 'settings.view',
        EDIT: 'settings.edit',
    },
    REPORTS: {
        INDEX: 'reports.index',
        VIEW: 'reports.view',
        EXPORT: 'reports.export',
    },
    MACHINE_CATEGORIES: {
        INDEX: 'machine-categories.index',
        VIEW: 'machine-categories.view',
        CREATE: 'machine-categories.create',
        EDIT: 'machine-categories.edit',
        DELETE: 'machine-categories.delete',
    },
    MACHINES: {
        INDEX: 'machines.index',
        VIEW: 'machines.view',
        CREATE: 'machines.create',
        EDIT: 'machines.edit',
        DELETE: 'machines.delete',
    },
    PRODUCTS: {
        INDEX: 'products.index',
        VIEW: 'products.view',
        CREATE: 'products.create',
        EDIT: 'products.edit',
        DELETE: 'products.delete',
    },
    VENDING_REQUESTS: {
        INDEX: 'vending-requests.index',
        VIEW: 'vending-requests.view',
        CREATE: 'vending-requests.create',
        EDIT: 'vending-requests.edit',
        DELETE: 'vending-requests.delete',
    },
    COFFEE_SERVICE_REQUESTS: {
        INDEX: 'coffee-service-requests.index',
        VIEW: 'coffee-service-requests.view',
        CREATE: 'coffee-service-requests.create',
        EDIT: 'coffee-service-requests.edit',
        DELETE: 'coffee-service-requests.delete',
    },
    HOME_FAQS: {
        INDEX: 'home-faqs.index',
        CREATE: 'home-faqs.create',
        EDIT: 'home-faqs.edit',
        DELETE: 'home-faqs.delete',
    },
    MACHINE_FAQS: {
        INDEX: 'machine-faqs.index',
        CREATE: 'machine-faqs.create',
        EDIT: 'machine-faqs.edit',
        DELETE: 'machine-faqs.delete',
    },
    OFFICE_COFFEE_FAQS: {
        INDEX: 'office-coffee-faqs.index',
        CREATE: 'office-coffee-faqs.create',
        EDIT: 'office-coffee-faqs.edit',
        DELETE: 'office-coffee-faqs.delete',
    },
    LOCATION_TYPES: {
        INDEX: 'location-types.index',
        CREATE: 'location-types.create',
        EDIT: 'location-types.edit',
        DELETE: 'location-types.delete',
    },
    OPERATOR_SMS: {
        INDEX: 'operator-sms.index',
        SEND: 'operator-sms.send',
    },
    PAYMENTS: {
        INDEX: 'payments.index',
    },
} as const;

// Auto-derived union type from the const above.
// Adding a new entry to PERMISSIONS automatically updates this type.
type PermissionGroup = typeof PERMISSIONS;
export type PermissionKey = {
    [G in keyof PermissionGroup]: PermissionGroup[G][keyof PermissionGroup[G]];
}[keyof PermissionGroup];
// Result: 'posts.view' | 'posts.create' | 'posts.edit' | ...

// =============================================================================
// PERMISSION OBJECT — full shape returned by the backend API.
//
// `group` is ONLY used when fetching the full permission list for the
// role management UI (e.g. GET /admin/permissions).
// It is NOT included in auth.permissions — that is a flat PermissionKey[].
//
// With group:
//   Useful for rendering grouped checkboxes in the role editor UI.
//   Example grouped output:
//     Posts       → [ ] posts.view  [ ] posts.create  [ ] posts.edit ...
//     Users       → [ ] users.view  [ ] users.create  ...
//     Roles       → [ ] roles.view  ...
//
// Without group:
//   If you don't need grouping in your UI, ignore `group` entirely.
//   The flat permissions array on auth.user is always enough for can() checks.
// =============================================================================

// Use this when you need grouping in the role management UI
export interface Permission {
    id: number;
    name: PermissionKey;
    group: string; // e.g. 'Posts', 'Users', 'Roles'
    guard_name: string;
}

// Use this when you don't care about grouping (e.g. a flat list of checkboxes)
export interface PermissionFlat {
    id: number;
    name: PermissionKey;
    guard_name: string;
}

// Helper type: permissions grouped by their group key.
// Returned by the groupPermissions() utility below.
// Example: { Posts: [Permission, ...], Users: [...] }
export type PermissionsByGroup = Record<string, Permission[]>;

// =============================================================================
// ROLES — Dynamic, admin-managed at runtime via the UI.
// Admins can create, rename, delete roles and assign permissions to them.
// Always plain strings — never a hardcoded union type.
// =============================================================================

export interface Role {
    id: number;
    name: string; // dynamic — never hardcode as a union
    guard_name: string;
    permissions: Permission[]; // full permission objects with group info
}

// Payload sent to POST /admin/roles
export interface CreateRolePayload {
    name: string;
    permissions: PermissionKey[];
}

// Payload sent to PUT /admin/roles/{id}
export interface UpdateRolePayload {
    name?: string;
    permissions: PermissionKey[];
}

// =============================================================================
// UTILITY — group a flat Permission[] by their group field.
//
// Use this on the role management page to render grouped checkboxes.
//
// Example:
//   const grouped = groupPermissions(permissions);
//   // {
//   //   Posts:    [{ id:1, name:'posts.view', group:'Posts' }, ...],
//   //   Users:    [{ id:6, name:'users.view', group:'Users' }, ...],
//   //   ...
//   // }
//
//   Object.entries(grouped).map(([group, perms]) => (
//     <fieldset key={group}>
//       <legend>{group}</legend>
//       {perms.map(p => <Checkbox key={p.id} label={p.name} />)}
//     </fieldset>
//   ))
// =============================================================================
export function groupPermissions(
    permissions: Permission[],
): PermissionsByGroup {
    return permissions.reduce<PermissionsByGroup>((acc, permission) => {
        const key = permission.group ?? 'Other';

        if (!acc[key]) {
            acc[key] = [];
        }

        acc[key].push(permission);

        return acc;
    }, {});
}
