import type { InertiaLinkProps } from '@inertiajs/react';
import { toUrl } from '@/lib/utils';
import type { PermissionKey } from '@/types/permissions';
import type { NavNode } from './types';

type Href = NonNullable<InertiaLinkProps['href']>;

/** Normalise any href (string or Wayfinder object, relative or absolute) to a pathname. */
export function toPath(href?: Href): string | null {
    if (!href) {
        return null;
    }

    const raw = toUrl(href);

    if (!raw || raw === '#') {
        return null;
    }

    let path = raw;

    if (raw.startsWith('http')) {
        try {
            path = new URL(raw).pathname;
        } catch {
            return null;
        }
    }

    path = path.split('?')[0].split('#')[0];

    // Drop a trailing slash, except for the root "/".
    return path.length > 1 ? path.replace(/\/+$/, '') : path;
}

function normaliseCurrentPath(currentPath: string): string {
    return currentPath.length > 1
        ? currentPath.replace(/\/+$/, '')
        : currentPath;
}

/**
 * Whether `href` matches the current path. Matches the exact path or a
 * sub-path (`/admin/users` is active on `/admin/users/5`), but never a sibling
 * that merely shares a prefix (`/admin/users` is NOT active on `/admin/users-archive`).
 */
export function isPathActive(
    href: Href | undefined,
    currentPath: string,
): boolean {
    const target = toPath(href);

    if (!target) {
        return false;
    }

    const current = normaliseCurrentPath(currentPath);

    return current === target || current.startsWith(`${target}/`);
}

/**
 * Leaf is active only when it matches the current path and no longer leaf href
 * in the nav tree is a better (more specific) match.
 *
 * `/leads/purchased` → Purchased wins over All leads (`/leads`).
 * `/admin/machines/create` → Machines (`/admin/machines`) still wins when that
 * is the longest matching leaf in the tree.
 */
export function isLeafActive(
    href: Href | undefined,
    currentPath: string,
    allLeafPaths: string[],
): boolean {
    const target = toPath(href);

    if (!target || !isPathActive(href, currentPath)) {
        return false;
    }

    const current = normaliseCurrentPath(currentPath);

    return !allLeafPaths.some((other) => {
        if (other === target || other.length <= target.length) {
            return false;
        }

        return current === other || current.startsWith(`${other}/`);
    });
}

/** Collect normalised leaf paths from a nav tree (for longest-match active state). */
export function collectLeafPaths(nodes: NavNode[]): string[] {
    const paths: string[] = [];

    for (const node of nodes) {
        if (node.items?.length) {
            paths.push(...collectLeafPaths(node.items));
            continue;
        }

        const path = toPath(node.href);

        if (path) {
            paths.push(path);
        }
    }

    return paths;
}

/** A node is active when it (a leaf) matches, or any descendant leaf matches. */
export function isNodeActive(
    node: NavNode,
    currentPath: string,
    allLeafPaths: string[] = [],
): boolean {
    if (node.items?.length) {
        return node.items.some((child) =>
            isNodeActive(child, currentPath, allLeafPaths),
        );
    }

    if (allLeafPaths.length > 0) {
        return isLeafActive(node.href, currentPath, allLeafPaths);
    }

    return isPathActive(node.href, currentPath);
}

/**
 * Filter a tree by permissions:
 *   - drop any node whose explicit `permissions` the user lacks;
 *   - recurse into groups and drop empty groups that have no own link.
 */
export function filterNavNodes(
    nodes: NavNode[],
    canAny: (permissions: PermissionKey[]) => boolean,
): NavNode[] {
    const result: NavNode[] = [];

    for (const node of nodes) {
        if (node.permissions?.length && !canAny(node.permissions)) {
            continue;
        }

        if (node.items?.length) {
            const items = filterNavNodes(node.items, canAny);

            if (items.length === 0 && !node.href) {
                continue;
            }

            result.push({ ...node, items });
        } else {
            result.push(node);
        }
    }

    return result;
}
