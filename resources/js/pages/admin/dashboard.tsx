import { Head, Link } from '@inertiajs/react';
import {
    Coffee,
    FolderTree,
    Inbox,
    Package,
    ShoppingBasket,
    UserCog,
    Users,
    type LucideIcon,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import adminAdminManagement from '@/routes/admin/admin-management';
import adminCoffeeServiceRequests from '@/routes/admin/coffee-service-requests';
import adminMachineCategories from '@/routes/admin/machine-categories';
import adminMachines from '@/routes/admin/machines';
import adminProducts from '@/routes/admin/products';
import adminUserManagement from '@/routes/admin/user-management';
import adminVendingRequests from '@/routes/admin/vending-requests';

type RequestSummary = {
    id: number;
    business_name: string;
    status: string;
    created_at: string;
};

type AdminDashboardProps = {
    stats: {
        operators: number;
        admins: number;
        machines: number;
        categories: number;
        products: number;
        vendingRequests: { total: number; new: number };
        coffeeRequests: { total: number; new: number };
    };
    recentVendingRequests: RequestSummary[];
    recentCoffeeRequests: RequestSummary[];
};

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString(undefined, {
        dateStyle: 'medium',
    });
}

function statusLabel(status: string): string {
    if (status === 'new') {
        return 'New';
    }

    if (status === 'reviewed') {
        return 'Reviewed';
    }

    if (status === 'closed') {
        return 'Closed';
    }

    return status;
}

export default function AdminDashboard({
    stats,
    recentVendingRequests,
    recentCoffeeRequests,
}: AdminDashboardProps) {
    const statCards: {
        label: string;
        value: number;
        hint?: string;
        href: string;
        icon: LucideIcon;
    }[] = [
        {
            label: 'Operators',
            value: stats.operators,
            href: adminUserManagement.index().url,
            icon: Users,
        },
        {
            label: 'Admins',
            value: stats.admins,
            href: adminAdminManagement.index().url,
            icon: UserCog,
        },
        {
            label: 'Machines',
            value: stats.machines,
            href: adminMachines.index().url,
            icon: Package,
        },
        {
            label: 'Categories',
            value: stats.categories,
            href: adminMachineCategories.index().url,
            icon: FolderTree,
        },
        {
            label: 'Products',
            value: stats.products,
            href: adminProducts.index().url,
            icon: ShoppingBasket,
        },
        {
            label: 'Vending requests',
            value: stats.vendingRequests.total,
            hint:
                stats.vendingRequests.new > 0
                    ? `${stats.vendingRequests.new} new`
                    : undefined,
            href: adminVendingRequests.index().url,
            icon: Inbox,
        },
        {
            label: 'Coffee requests',
            value: stats.coffeeRequests.total,
            hint:
                stats.coffeeRequests.new > 0
                    ? `${stats.coffeeRequests.new} new`
                    : undefined,
            href: adminCoffeeServiceRequests.index().url,
            icon: Coffee,
        },
    ];

    const quickLinks: { title: string; href: string; icon: LucideIcon }[] = [
        {
            title: 'User management',
            href: adminUserManagement.index().url,
            icon: Users,
        },
        {
            title: 'Admin management',
            href: adminAdminManagement.index().url,
            icon: UserCog,
        },
        {
            title: 'Machines',
            href: adminMachines.index().url,
            icon: Package,
        },
        {
            title: 'Categories',
            href: adminMachineCategories.index().url,
            icon: FolderTree,
        },
        {
            title: 'Products',
            href: adminProducts.index().url,
            icon: ShoppingBasket,
        },
        {
            title: 'Vending requests',
            href: adminVendingRequests.index().url,
            icon: Inbox,
        },
        {
            title: 'Coffee requests',
            href: adminCoffeeServiceRequests.index().url,
            icon: Coffee,
        },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">
                        Dashboard
                    </h1>
                    <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                        Overview of operators, catalog, and inbound service
                        requests across the platform.
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card) => {
                        const Icon = card.icon;

                        return (
                            <Link
                                key={card.label}
                                href={card.href}
                                className="flex items-center gap-4 rounded-xl border bg-card p-5 shadow-sm transition-colors hover:bg-muted/40"
                            >
                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Icon className="h-5 w-5" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-2xl font-bold tabular-nums">
                                        {card.value}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {card.label}
                                    </p>
                                    {card.hint && (
                                        <p className="mt-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            {card.hint}
                                        </p>
                                    )}
                                </div>
                            </Link>
                        );
                    })}
                </div>

                <div>
                    <h2 className="mb-3 text-sm font-semibold tracking-wide uppercase">
                        Quick links
                    </h2>
                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {quickLinks.map((link) => {
                            const Icon = link.icon;

                            return (
                                <Link
                                    key={link.title}
                                    href={link.href}
                                    className="inline-flex items-center gap-2 rounded-lg border bg-card px-3.5 py-2.5 text-sm font-medium shadow-sm transition-colors hover:bg-muted/40"
                                >
                                    <Icon className="h-4 w-4 text-muted-foreground" />
                                    {link.title}
                                </Link>
                            );
                        })}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <RecentRequestsCard
                        title="Recent vending requests"
                        empty="No vending requests yet."
                        items={recentVendingRequests}
                        viewAllHref={adminVendingRequests.index().url}
                        showHref={(id) =>
                            adminVendingRequests.show(id).url
                        }
                    />
                    <RecentRequestsCard
                        title="Recent coffee requests"
                        empty="No coffee requests yet."
                        items={recentCoffeeRequests}
                        viewAllHref={adminCoffeeServiceRequests.index().url}
                        showHref={(id) =>
                            adminCoffeeServiceRequests.show(id).url
                        }
                    />
                </div>
            </div>
        </>
    );
}

function RecentRequestsCard({
    title,
    empty,
    items,
    viewAllHref,
    showHref,
}: {
    title: string;
    empty: string;
    items: RequestSummary[];
    viewAllHref: string;
    showHref: (id: number) => string;
}) {
    return (
        <div className="rounded-xl border bg-card p-5 shadow-sm">
            <div className="mb-4 flex items-center justify-between gap-3">
                <h2 className="text-sm font-semibold tracking-wide uppercase">
                    {title}
                </h2>
                <Button variant="outline" size="sm" asChild>
                    <Link href={viewAllHref}>View all</Link>
                </Button>
            </div>

            {items.length === 0 ? (
                <p className="text-sm text-muted-foreground">{empty}</p>
            ) : (
                <ul className="divide-y">
                    {items.map((item) => (
                        <li key={item.id}>
                            <Link
                                href={showHref(item.id)}
                                className="flex items-center justify-between gap-3 py-3 transition-colors hover:bg-muted/30"
                            >
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium">
                                        {item.business_name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {formatDate(item.created_at)}
                                    </p>
                                </div>
                                <Badge
                                    variant={
                                        item.status === 'new'
                                            ? 'secondary'
                                            : 'outline'
                                    }
                                    className="shrink-0 capitalize"
                                >
                                    {statusLabel(item.status)}
                                </Badge>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
