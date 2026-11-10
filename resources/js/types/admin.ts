// =============================================================================
// ADMIN — shared shapes for the access-management pages
// (admin/users, admin/roles, admin/permissions).
// =============================================================================

/** The protected, system-critical role. Mirrors App\Enums\RoleEnum::SUPER_ADMIN. */
export const SUPER_ADMIN_ROLE = 'super-admin';

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

export interface RoleRef {
    id: number;
    name: string;
}

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: RoleRef[];
}

export interface AdminOperatorProfile {
    id: number;
    first_name: string;
    last_name: string;
    company_name: string;
    phone: string | null;
    owns_machines: boolean;
    machine_types: string[];
    machines_needed: string;
    primary_city: string;
    terms_accepted_at: string | null;
    email_code_confirmed_at: string | null;
    trial_started_at: string | null;
}

export interface AdminUserDetail extends AdminUser {
    permissions: { id: number; name: string }[];
    updated_at: string;
}

export interface AdminRoleListItem {
    id: number;
    name: string;
    guard_name: string;
    permissions_count: number;
    users_count: number;
    created_at: string;
}

export interface AdminRoleDetail {
    id: number;
    name: string;
    permissions: string[];
    is_super_admin: boolean;
}

/** A single assignable permission, as sent to the role editor. */
export interface PermissionOption {
    id: number;
    name: string;
    group: string;
}

/** A permission row for the read-only permissions listing. */
export interface PermissionListItem {
    id: number;
    name: string;
    group: string;
    guard_name: string;
    roles_count: number;
}

export interface MachineCategoryOption {
    id: number;
    name: string;
}

export interface AdminMachineCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    machines_count?: number;
    created_at: string;
    updated_at: string;
}

export interface AdminMachine {
    id: number;
    machine_category_id: number;
    name: string;
    slug: string;
    kind: string;
    tags: string[] | null;
    description: string | null;
    image: string | null;
    image_url: string | null;
    capacity: string | null;
    dimensions: string | null;
    best_for: string | null;
    status: string;
    is_featured: boolean;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    category?: MachineCategoryOption & { slug?: string };
}

export interface AdminProduct {
    id: number;
    name: string;
    slug: string;
    kind: string;
    tags: string[] | null;
    description: string | null;
    image: string | null;
    image_url: string | null;
    is_featured: boolean;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface AdminVendingRequest {
    id: number;
    location_type: string;
    business_name: string;
    address_or_zip: string;
    address: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    latitude: string | null;
    longitude: string | null;
    employees_on_site: number | null;
    daily_visitors: number | null;
    placement: string;
    space_notes: string | null;
    food_on_site: string;
    comparing_quotes: string;
    machine_interests: string[];
    machine_count: string;
    existing_vending: string;
    switch_reason: string | null;
    contact_name: string;
    job_title: string | null;
    email: string;
    phone: string;
    terms_accepted: boolean;
    status: 'new' | 'reviewed';
    created_at: string;
    updated_at: string;
}

export interface AdminCoffeeServiceRequest {
    id: number;
    business_name: string;
    zip_code: string;
    address: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    latitude: string | null;
    longitude: string | null;
    contact_name: string;
    phone: string;
    email: string;
    notes: string | null;
    terms_accepted: boolean;
    status: 'new' | 'reviewed' | 'closed';
    created_at: string;
    updated_at: string;
}

/** Prefer structured city/state/postal; fall back to legacy display fields. */
export function formatRequestLocation(request: {
    address?: string | null;
    city?: string | null;
    state?: string | null;
    postal_code?: string | null;
    address_or_zip?: string | null;
    zip_code?: string | null;
}): string {
    const cityState = [request.city, request.state].filter(Boolean).join(', ');
    const withPostal = [cityState, request.postal_code]
        .filter(Boolean)
        .join(' ');

    if (request.address && withPostal) {
        return `${request.address}, ${withPostal}`;
    }

    if (withPostal) {
        return withPostal;
    }

    return request.address_or_zip || request.zip_code || '—';
}

/** Build the public URL for a stored avatar path, or null. */
export function avatarUrl(path: string | null): string | null {
    if (!path) {
        return null;
    }

    return path.startsWith('http') ? path : `/storage/${path}`;
}

/** Group an ordered permission list by its `group` field, preserving order. */
export function groupByGroup<T extends { group: string }>(
    items: T[],
): Record<string, T[]> {
    return items.reduce<Record<string, T[]>>((acc, item) => {
        (acc[item.group] ??= []).push(item);

        return acc;
    }, {});
}
