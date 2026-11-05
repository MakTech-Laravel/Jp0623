import { Link } from '@inertiajs/react';
import {
    BookOpen,
    CreditCard,
    FolderGit2,
    FolderTree,
    KeyRound,
    LayoutGrid,
    LockKeyhole,
    MapPin,
    Package,
    Settings,
    Shield,
    ShieldCheck,
    Upload,
    User,
    Users,
} from 'lucide-react';
import { useMemo } from 'react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavUser } from '@/components/nav-user';
import { SidebarNav } from '@/components/navigation';
import type { NavNode } from '@/components/navigation';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import adminMachineCategories from '@/routes/admin/machine-categories';
import adminMachines from '@/routes/admin/machines';
import adminPermissions from '@/routes/admin/permissions';
import adminRoles from '@/routes/admin/roles';
import adminUsers from '@/routes/admin/users';
import { index as fileUploadDemo } from '@/routes/file-upload-demo';
import { index as leadsIndex, purchased as leadsPurchased } from '@/routes/leads';
import { index as subscriptionIndex } from '@/routes/subscription';
import type { NavItem } from '@/types';
import { PERMISSIONS } from '@/types/permissions';

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { hasAnyRole } = usePermission();
    const isAdmin = hasAnyRole(['admin', 'super-admin']);

    const mainNav = useMemo((): NavNode[] => {
        const items: NavNode[] = [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
                permissions: [PERMISSIONS.DASHBOARD.VIEW],
            },
        ];

        if (!isAdmin) {
            items.push(
                {
                    title: 'Location leads',
                    icon: MapPin,
                    permissions: [PERMISSIONS.DASHBOARD.VIEW],
                    items: [
                        {
                            title: 'All location leads',
                            href: leadsIndex(),
                            icon: MapPin,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Purchased location leads',
                            href: leadsPurchased(),
                            icon: MapPin,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                    ],
                },
                {
                    title: 'Subscription',
                    href: subscriptionIndex(),
                    icon: CreditCard,
                    permissions: [PERMISSIONS.DASHBOARD.VIEW],
                },
            );
        }

        items.push(
            {
                title: 'File Upload Demo',
                href: fileUploadDemo(),
                icon: Upload,
                permissions: [PERMISSIONS.FILE_UPLOAD.INDEX],
            },
            {
                title: 'Machine Management',
                icon: Package,
                permissions: [
                    PERMISSIONS.MACHINE_CATEGORIES.INDEX,
                    PERMISSIONS.MACHINES.INDEX,
                ],
                items: [
                    {
                        title: 'Categories',
                        href: adminMachineCategories.index(),
                        icon: FolderTree,
                        permissions: [PERMISSIONS.MACHINE_CATEGORIES.INDEX],
                    },
                    {
                        title: 'Machines',
                        href: adminMachines.index(),
                        icon: Package,
                        permissions: [PERMISSIONS.MACHINES.INDEX],
                    },
                ],
            },
            {
                title: 'Access Control',
                icon: ShieldCheck,
                permissions: [
                    PERMISSIONS.USERS.INDEX,
                    PERMISSIONS.ROLES.INDEX,
                    PERMISSIONS.PERMISSIONS.INDEX,
                ],
                items: [
                    {
                        title: 'Users',
                        href: adminUsers.index(),
                        icon: Users,
                        permissions: [PERMISSIONS.USERS.INDEX],
                    },
                    {
                        title: 'Roles',
                        href: adminRoles.index(),
                        icon: Shield,
                        permissions: [PERMISSIONS.ROLES.INDEX],
                    },
                    {
                        title: 'Permissions',
                        href: adminPermissions.index(),
                        icon: KeyRound,
                        permissions: [PERMISSIONS.PERMISSIONS.INDEX],
                    },
                    {
                        title: 'Settings',
                        icon: Settings,
                        permissions: [PERMISSIONS.SETTINGS.INDEX],
                        classNames: {
                            icon: 'size-5',
                        },
                        items: [
                            {
                                title: 'Profile',
                                href: '#',
                                icon: User,
                                permissions: [PERMISSIONS.SETTINGS.INDEX],
                            },
                            {
                                title: 'Security',
                                icon: Shield,
                                permissions: [PERMISSIONS.SETTINGS.INDEX],
                                items: [
                                    {
                                        title: 'Password',
                                        href: '#',
                                        icon: LockKeyhole,
                                        permissions: [
                                            PERMISSIONS.SETTINGS.INDEX,
                                        ],
                                    },
                                ],
                            },
                        ],
                    },
                ],
            },
        );

        return items;
    }, [isAdmin]);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarNav items={mainNav} label="Platform" />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
