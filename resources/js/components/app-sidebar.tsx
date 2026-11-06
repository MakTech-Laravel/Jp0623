import { Link } from '@inertiajs/react';
import {
    BookOpen,
    CircleHelp,
    CreditCard,
    FolderGit2,
    FolderTree,
    KeyRound,
    LayoutGrid,
    LockKeyhole,
    MapPinned,
    MapPin,
    Package,
    Receipt,
    Route,
    Send,
    Settings,
    Shield,
    ShieldCheck,
    ShoppingBasket,
    Ticket,
    Upload,
    User,
    Users,
    Wallet,
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
import {
    index as accountIndex,
    location as accountLocation,
} from '@/routes/account';
import adminMachineCategories from '@/routes/admin/machine-categories';
import adminMachines from '@/routes/admin/machines';
import adminPermissions from '@/routes/admin/permissions';
import adminProducts from '@/routes/admin/products';
import adminRoles from '@/routes/admin/roles';
import adminUsers from '@/routes/admin/users';
import { index as billingIndex } from '@/routes/billing';
import { index as fileUploadDemo } from '@/routes/file-upload-demo';
import {
    commission as leadsCommission,
    faq as leadsFaq,
    index as leadsIndex,
    purchased as leadsPurchased,
    submit as leadsSubmit,
    tickets as leadsTickets,
} from '@/routes/leads';
import {
    index as routesIndex,
    sell as routesSell,
} from '@/routes/routes';
import { index as subscriptionIndex } from '@/routes/subscription';
import type { NavItem } from '@/types';
import { PERMISSIONS } from '@/types/permissions';

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/react-starter-kit',
    //     icon: FolderGit2,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#react',
    //     icon: BookOpen,
    // },
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
                        {
                            title: 'Lead issues & tickets',
                            href: leadsTickets(),
                            icon: Ticket,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Submit lead for sale',
                            href: leadsSubmit(),
                            icon: Send,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Commission earned',
                            href: leadsCommission(),
                            icon: Wallet,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'FAQ',
                            href: leadsFaq(),
                            icon: CircleHelp,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                    ],
                },
                {
                    title: 'Routes for sale',
                    icon: Route,
                    permissions: [PERMISSIONS.DASHBOARD.VIEW],
                    items: [
                        {
                            title: 'Routes for sale',
                            href: routesIndex(),
                            icon: Route,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Submit route for sale',
                            href: routesSell(),
                            icon: Send,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                    ],
                },
                {
                    title: 'Account',
                    icon: User,
                    permissions: [PERMISSIONS.DASHBOARD.VIEW],
                    items: [
                        {
                            title: 'Account info',
                            href: accountIndex(),
                            icon: User,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Location settings',
                            href: accountLocation(),
                            icon: MapPinned,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Subscription',
                            href: subscriptionIndex(),
                            icon: CreditCard,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                        {
                            title: 'Billing & invoices',
                            href: billingIndex(),
                            icon: Receipt,
                            permissions: [PERMISSIONS.DASHBOARD.VIEW],
                        },
                    ],
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
                title: 'Product Management',
                icon: ShoppingBasket,
                permissions: [PERMISSIONS.PRODUCTS.INDEX],
                items: [
                    {
                        title: 'Products',
                        href: adminProducts.index(),
                        icon: ShoppingBasket,
                        permissions: [PERMISSIONS.PRODUCTS.INDEX],
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
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="h-auto! py-1.5 group-data-[collapsible=icon]:size-8!"
                        >
                            <Link
                                href={dashboard()}
                                prefetch
                                className="w-full"
                            >
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
