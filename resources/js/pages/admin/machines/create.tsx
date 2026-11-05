import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, PackagePlus } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { MachineForm } from '@/components/admin/machine-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import machines from '@/routes/admin/machines';
import type { MachineCategoryOption } from '@/types/admin';

export default function CreateMachine({
    categories,
}: {
    categories: MachineCategoryOption[];
}) {
    return (
        <>
            <Head title="Create machine" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create machine"
                    description="Add a machine to the catalog. A category is required."
                    icon={PackagePlus}
                >
                    <Button variant="outline" asChild>
                        <Link href={machines.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to machines
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <MachineForm
                        action={machines.store()}
                        categories={categories}
                        onCancel={() => router.visit(machines.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateMachine.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Machines', href: machines.index() },
        { title: 'Create', href: machines.create() },
    ],
};
