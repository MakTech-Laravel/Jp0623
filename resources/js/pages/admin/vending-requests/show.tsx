import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Inbox, Trash2 } from 'lucide-react';
import { motion } from 'motion/react';
import type { ReactNode } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import vendingRequests from '@/routes/admin/vending-requests';
import type { AdminVendingRequest } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

export default function ShowVendingRequest({
    vendingRequest,
}: {
    vendingRequest: AdminVendingRequest;
}) {
    const { can } = usePermission();

    return (
        <>
            <Head title={vendingRequest.business_name} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Request details"
                    description={vendingRequest.business_name}
                    icon={Inbox}
                >
                    <Button variant="outline" asChild>
                        <Link href={vendingRequests.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                    {can(PERMISSIONS.VENDING_REQUESTS.EDIT) && (
                        <Button
                            type="button"
                            variant={
                                vendingRequest.status === 'reviewed'
                                    ? 'outline'
                                    : 'default'
                            }
                            onClick={() =>
                                router.put(
                                    vendingRequests.update(vendingRequest.id)
                                        .url,
                                    {
                                        status:
                                            vendingRequest.status === 'new'
                                                ? 'reviewed'
                                                : 'new',
                                    },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            {vendingRequest.status === 'new'
                                ? 'Mark reviewed'
                                : 'Mark new'}
                        </Button>
                    )}
                    {can(PERMISSIONS.VENDING_REQUESTS.DELETE) && (
                        <ConfirmDeleteDialog
                            description={
                                <>
                                    Delete request from{' '}
                                    <strong>
                                        {vendingRequest.business_name}
                                    </strong>
                                    ?
                                </>
                            }
                            onConfirm={() =>
                                router.delete(
                                    vendingRequests.destroy(vendingRequest.id)
                                        .url,
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
                                vendingRequest.status === 'new'
                                    ? 'secondary'
                                    : 'outline'
                            }
                        >
                            {vendingRequest.status === 'new'
                                ? 'New'
                                : 'Reviewed'}
                        </Badge>
                        <span className="text-sm text-muted-foreground">
                            Submitted{' '}
                            {new Date(
                                vendingRequest.created_at,
                            ).toLocaleString()}
                        </span>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-2">
                        <Section title="Location">
                            <Detail
                                label="Type"
                                value={vendingRequest.location_type}
                            />
                            <Detail
                                label="Business"
                                value={vendingRequest.business_name}
                            />
                            <Detail
                                label="Address / ZIP"
                                value={vendingRequest.address_or_zip}
                            />
                        </Section>

                        <Section title="Contact">
                            <Detail
                                label="Name"
                                value={vendingRequest.contact_name}
                            />
                            <Detail
                                label="Job title"
                                value={vendingRequest.job_title ?? '—'}
                            />
                            <Detail label="Email" value={vendingRequest.email} />
                            <Detail label="Phone" value={vendingRequest.phone} />
                        </Section>

                        <Section title="Site details">
                            <Detail
                                label="Employees"
                                value={
                                    vendingRequest.employees_on_site === null
                                        ? '—'
                                        : String(
                                              vendingRequest.employees_on_site,
                                          )
                                }
                            />
                            <Detail
                                label="Daily visitors"
                                value={
                                    vendingRequest.daily_visitors === null
                                        ? '—'
                                        : String(vendingRequest.daily_visitors)
                                }
                            />
                            <Detail
                                label="Placement"
                                value={vendingRequest.placement}
                            />
                            <Detail
                                label="Food on site"
                                value={vendingRequest.food_on_site}
                            />
                            <Detail
                                label="Comparing quotes"
                                value={vendingRequest.comparing_quotes}
                            />
                            <Detail
                                label="Space notes"
                                value={vendingRequest.space_notes ?? '—'}
                                className="sm:col-span-2"
                            />
                        </Section>

                        <Section title="Machines">
                            <div className="sm:col-span-2">
                                <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Interests
                                </dt>
                                <dd className="mt-2 flex flex-wrap gap-1">
                                    {vendingRequest.machine_interests.map(
                                        (interest) => (
                                            <Badge
                                                key={interest}
                                                variant="outline"
                                            >
                                                {interest}
                                            </Badge>
                                        ),
                                    )}
                                </dd>
                            </div>
                            <Detail
                                label="Count"
                                value={vendingRequest.machine_count}
                            />
                            <Detail
                                label="Existing vending"
                                value={vendingRequest.existing_vending}
                            />
                            <Detail
                                label="Switch reason"
                                value={vendingRequest.switch_reason ?? '—'}
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

ShowVendingRequest.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Vending requests', href: vendingRequests.index() },
        { title: 'Details', href: '#' },
    ],
};
