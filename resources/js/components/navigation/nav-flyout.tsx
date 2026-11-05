import { Link } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { cn, toUrl } from '@/lib/utils';
import { NavBadge } from './nav-badge';
import { isLeafActive, isNodeActive } from './nav-utils';
import type { NavNode, NavNodeClassNames } from './types';

/** Merge a slot's default + per-node override onto an optional base. */
function slot(
    key: keyof NavNodeClassNames,
    defaults: NavNodeClassNames | undefined,
    node: NavNode,
    base?: string,
): string | undefined {
    return cn(base, defaults?.[key], node.classNames?.[key]) || undefined;
}

interface NavFlyoutProps {
    node: NavNode;
    currentPath: string;
    allLeafPaths: string[];
    defaults?: NavNodeClassNames;
}

/**
 * Collapsed (icon) mode renderer for a top-level group.
 *
 * The expandable tree is hidden when the sidebar is icon-collapsed, so the
 * children are surfaced through a portalled dropdown flyout instead. Nested
 * groups become nested sub-menus, so any depth is reachable without overlap.
 */
export function NavFlyout({
    node,
    currentPath,
    allLeafPaths,
    defaults,
}: NavFlyoutProps) {
    // Branch has an active child — open indicator only, not selected styling.
    const branchActive = isNodeActive(node, currentPath, allLeafPaths);
    const Icon = node.icon;

    return (
        <SidebarMenuItem className={slot('item', defaults, node)}>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <SidebarMenuButton
                        tooltip={node.title}
                        aria-current={branchActive ? 'true' : undefined}
                        className={cn(
                            branchActive && 'text-sidebar-accent-foreground',
                            slot('row', defaults, node),
                        )}
                    >
                        {Icon && (
                            <Icon className={slot('icon', defaults, node)} />
                        )}
                        <span
                            className={slot(
                                'title',
                                defaults,
                                node,
                                'flex-1 truncate',
                            )}
                        >
                            {node.title}
                        </span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    side="right"
                    align="start"
                    sideOffset={8}
                    className="min-w-48"
                >
                    <DropdownMenuLabel>{node.title}</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {node.items?.map((child) => (
                        <FlyoutNode
                            key={child.title}
                            node={child}
                            currentPath={currentPath}
                            allLeafPaths={allLeafPaths}
                            defaults={defaults}
                        />
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    );
}

function FlyoutNode({
    node,
    currentPath,
    allLeafPaths,
    defaults,
}: NavFlyoutProps) {
    const Icon = node.icon;

    if (node.items?.length) {
        return (
            <DropdownMenuSub>
                <DropdownMenuSubTrigger
                    className={cn(
                        isNodeActive(node, currentPath, allLeafPaths) &&
                            'text-primary data-[state=open]:text-primary',
                        slot('row', defaults, node),
                    )}
                >
                    {Icon && (
                        <Icon
                            className={slot(
                                'icon',
                                defaults,
                                node,
                                'text-muted-foreground',
                            )}
                        />
                    )}
                    <span className={slot('title', defaults, node, 'flex-1')}>
                        {node.title}
                    </span>
                </DropdownMenuSubTrigger>
                <DropdownMenuPortal>
                    <DropdownMenuSubContent className="min-w-44">
                        {node.items.map((child) => (
                            <FlyoutNode
                                key={child.title}
                                node={child}
                                currentPath={currentPath}
                                allLeafPaths={allLeafPaths}
                                defaults={defaults}
                            />
                        ))}
                    </DropdownMenuSubContent>
                </DropdownMenuPortal>
            </DropdownMenuSub>
        );
    }

    const label = (
        <>
            {Icon && (
                <Icon
                    className={slot(
                        'icon',
                        defaults,
                        node,
                        'text-muted-foreground',
                    )}
                />
            )}
            <span className={slot('title', defaults, node, 'flex-1')}>
                {node.title}
            </span>
            {node.badge != null && (
                <NavBadge className={slot('badge', defaults, node)}>
                    {node.badge}
                </NavBadge>
            )}
        </>
    );

    if (node.disabled) {
        return <DropdownMenuItem disabled>{label}</DropdownMenuItem>;
    }

    const active = isLeafActive(node.href, currentPath, allLeafPaths);

    return (
        <DropdownMenuItem
            asChild
            className={cn(
                active &&
                    'bg-petrol/10 font-medium text-petrol dark:bg-brass/15 dark:text-brass',
                slot('row', defaults, node),
            )}
        >
            {node.external ? (
                <a
                    href={toUrl(node.href ?? '#')}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {label}
                </a>
            ) : (
                <Link href={node.href ?? '#'} prefetch>
                    {label}
                </Link>
            )}
        </DropdownMenuItem>
    );
}
