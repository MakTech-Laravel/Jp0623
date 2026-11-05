import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, FolderPlus } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { MachineCategoryForm } from '@/components/admin/machine-category-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import machineCategories from '@/routes/admin/machine-categories';

export default function CreateMachineCategory() {
    return (
        <>
            <Head title="Create category" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create category"
                    description="Add a machine category before creating machines."
                    icon={FolderPlus}
                >
                    <Button variant="outline" asChild>
                        <Link href={machineCategories.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to categories
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <MachineCategoryForm
                        action={machineCategories.store()}
                        onCancel={() =>
                            router.visit(machineCategories.index().url)
                        }
                    />
                </div>
            </div>
        </>
    );
}

CreateMachineCategory.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Categories', href: machineCategories.index() },
        { title: 'Create', href: machineCategories.create() },
    ],
};
