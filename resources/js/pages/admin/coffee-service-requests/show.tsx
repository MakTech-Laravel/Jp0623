import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Coffee, Trash2 } from 'lucide-react';
import { motion } from 'motion/react';
import type { ReactNode } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import coffeeServiceRequests from '@/routes/admin/coffee-service-requests';
import type { AdminCoffeeServiceRequest } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

function statusLabel(status: AdminCoffeeServiceRequest['status']): string {
    if (status === 'new') {
        return 'New';
    }

    if (status === 'reviewed') {
        return 'Reviewed';
    }

    return 'Closed';
}

export default function ShowCoffeeServiceRequest({
    coffeeServiceRequest,
}: {
    coffeeServiceRequest: AdminCoffeeServiceRequest;
}) {
    const { can } = usePermission();

    return (
        <>
            <Head title={coffeeServiceRequest.business_name} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Request details"
                    description={coffeeServiceRequest.business_name}
                    icon={Coffee}
                >
                    <Button variant="outline" asChild>
                        <Link href={coffeeServiceRequests.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                    {can(PERMISSIONS.COFFEE_SERVICE_REQUESTS.EDIT) && (
                        <Select
                            value={coffeeServiceRequest.status}
                            onValueChange={(status) =>
                                router.put(
                                    coffeeServiceRequests.update(
                                        coffeeServiceRequest.id,
                                    ).url,
                                    { status },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="new">New</SelectItem>
                                <SelectItem value="reviewed">
                                    Reviewed
                                </SelectItem>
                                <SelectItem value="closed">Closed</SelectItem>
                            </SelectContent>
                        </Select>
                    )}
                    {can(PERMISSIONS.COFFEE_SERVICE_REQUESTS.DELETE) && (
                        <ConfirmDeleteDialog
                            description={
                                <>
                                    Delete request from{' '}
                                    <strong>
                                        {coffeeServiceRequest.business_name}
                                    </strong>
                                    ?
                                </>
                            }
                            onConfirm={() =>
                                router.delete(
                                    coffeeServiceRequests.destroy(
                                        coffeeServiceRequest.id,
                                    ).url,
                                )
                            }
                        >
                            <Button
                                variant="outline"
                                className="text-destructive"
                            >
                                <Trash2 className="h-4 w-4" /> Delete
                            </Button>
                        </ConfirmDeleteDialog>
                    )}
                </AdminPageHeader>

                <motion.div
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="space-y-6"
                >
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge
                            variant={
                                coffeeServiceRequest.status === 'new'
                                    ? 'secondary'
                                    : 'outline'
                            }
                        >
                            {statusLabel(coffeeServiceRequest.status)}
                        </Badge>
                        <span className="text-sm text-muted-foreground">
                            Submitted{' '}
                            {new Date(
                                coffeeServiceRequest.created_at,
                            ).toLocaleString()}
                        </span>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-2">
                        <Section title="Business">
                            <Detail
                                label="Business"
                                value={coffeeServiceRequest.business_name}
                            />
                            <Detail
                                label="ZIP code"
                                value={coffeeServiceRequest.zip_code}
                            />
                        </Section>

                        <Section title="Contact">
                            <Detail
                                label="Name"
                                value={coffeeServiceRequest.contact_name}
                            />
                            <Detail
                                label="Email"
                                value={coffeeServiceRequest.email}
                            />
                            <Detail
                                label="Phone"
                                value={coffeeServiceRequest.phone}
                            />
                        </Section>

                        <Section title="Notes">
                            <Detail
                                label="Anything else"
                                value={coffeeServiceRequest.notes ?? '—'}
                                className="sm:col-span-2"
                            />
                        </Section>
                    </div>
                </motion.div>
            </div>
        </>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <div className="rounded-xl border bg-card p-6 shadow-sm">
            <h2 className="mb-4 text-sm font-semibold tracking-wide uppercase">
                {title}
            </h2>
            <dl className="grid gap-4 sm:grid-cols-2">{children}</dl>
        </div>
    );
}

function Detail({
    label,
    value,
    className,
}: {
    label: string;
    value: string;
    className?: string;
}) {
    return (
        <div className={className}>
            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </dt>
            <dd className="mt-1 text-sm whitespace-pre-wrap">{value}</dd>
        </div>
    );
}

ShowCoffeeServiceRequest.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        {
            title: 'Coffee requests',
            href: coffeeServiceRequests.index(),
        },
        { title: 'Details', href: '#' },
    ],
};
