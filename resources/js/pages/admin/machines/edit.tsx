import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Package } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { MachineForm } from '@/components/admin/machine-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import machines from '@/routes/admin/machines';
import type { AdminMachine, MachineCategoryOption } from '@/types/admin';

export default function EditMachine({
    machine,
    categories,
}: {
    machine: AdminMachine;
    categories: MachineCategoryOption[];
}) {
    return (
        <>
            <Head title={`Edit ${machine.name}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit machine"
                    description={`Update ${machine.name}.`}
                    icon={Package}
                >
                    <Button variant="outline" asChild>
                        <Link href={machines.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to machines
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <MachineForm
                        action={machines.update(machine.id)}
                        categories={categories}
                        isEdit
                        currentImageUrl={machine.image_url}
                        defaults={{
                            machine_category_id: machine.machine_category_id,
                            name: machine.name,
                            slug: machine.slug,
                            kind: machine.kind,
                            tags: (machine.tags ?? []).join(', '),
                            description: machine.description ?? '',
                            capacity: machine.capacity ?? '',
                            dimensions: machine.dimensions ?? '',
                            best_for: machine.best_for ?? '',
                            status: machine.status,
                            is_featured: machine.is_featured,
                            is_active: machine.is_active,
                            sort_order: machine.sort_order,
                        }}
                        onCancel={() => router.visit(machines.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditMachine.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Machines', href: machines.index() },
        { title: 'Edit', href: '#' },
    ],
};
