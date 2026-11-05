import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Package,
    Pencil,
} from 'lucide-react';
import { motion } from 'motion/react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import machines from '@/routes/admin/machines';
import type { AdminMachine } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

export default function ShowMachine({ machine }: { machine: AdminMachine }) {
    const { can } = usePermission();

    return (
        <>
            <Head title={machine.name} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader title="Machine details" icon={Package}>
                    <Button variant="outline" asChild>
                        <Link href={machines.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                    {can(PERMISSIONS.MACHINES.EDIT) && (
                        <Button asChild>
                            <Link href={machines.edit(machine.id).url}>
                                <Pencil className="h-4 w-4" /> Edit
                            </Link>
                        </Button>
                    )}
                </AdminPageHeader>

                <motion.div
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="grid gap-6 lg:grid-cols-3"
                >
                    <div className="overflow-hidden rounded-xl border bg-card shadow-sm lg:col-span-1">
                        <div className="flex aspect-square items-center justify-center bg-muted/40 p-6">
                            {machine.image_url ? (
                                <img
                                    src={machine.image_url}
                                    alt={machine.name}
                                    className="max-h-full max-w-full object-contain"
                                />
                            ) : (
                                <Package className="h-16 w-16 text-muted-foreground/40" />
                            )}
                        </div>
                        <div className="space-y-2 border-t p-4">
                            <h2 className="text-lg font-bold">{machine.name}</h2>
                            <p className="text-sm text-muted-foreground">
                                {machine.slug}
                            </p>
                            <div className="flex flex-wrap gap-1">
                                <Badge
                                    variant={
                                        machine.is_active
                                            ? 'secondary'
                                            : 'outline'
                                    }
                                >
                                    {machine.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                                {machine.is_featured && (
                                    <Badge variant="outline">Featured</Badge>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-xl border bg-card p-6 shadow-sm lg:col-span-2">
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Detail
                                label="Category"
                                value={machine.category?.name ?? '—'}
                            />
                            <Detail label="Kind" value={machine.kind} />
                            <Detail label="Status" value={machine.status} />
                            <Detail
                                label="Sort order"
                                value={String(machine.sort_order)}
                            />
                            <Detail
                                label="Capacity"
                                value={machine.capacity ?? '—'}
                            />
                            <Detail
                                label="Dimensions"
                                value={machine.dimensions ?? '—'}
                            />
                            <Detail
                                label="Best for"
                                value={machine.best_for ?? '—'}
                                className="sm:col-span-2"
                            />
                            <div className="sm:col-span-2">
                                <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Tags
                                </dt>
                                <dd className="mt-2 flex flex-wrap gap-1">
                                    {(machine.tags ?? []).length > 0 ? (
                                        (machine.tags ?? []).map((tag) => (
                                            <Badge key={tag} variant="outline">
                                                {tag}
                                            </Badge>
                                        ))
                                    ) : (
                                        <span className="text-sm">—</span>
                                    )}
                                </dd>
                            </div>
                            <div className="sm:col-span-2">
                                <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Description
                                </dt>
                                <dd className="mt-1 text-sm leading-relaxed">
                                    {machine.description ?? '—'}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </motion.div>
            </div>
        </>
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
            <dd className="mt-1 text-sm">{value}</dd>
        </div>
    );
}

ShowMachine.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Machines', href: machines.index() },
        { title: 'Details', href: '#' },
    ],
};
