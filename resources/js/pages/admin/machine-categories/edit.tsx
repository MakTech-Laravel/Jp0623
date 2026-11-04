import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, FolderPen } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { MachineCategoryForm } from '@/components/admin/machine-category-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import machineCategories from '@/routes/admin/machine-categories';
import type { AdminMachineCategory } from '@/types/admin';

export default function EditMachineCategory({
    category,
}: {
    category: AdminMachineCategory;
}) {
    return (
        <>
            <Head title={`Edit ${category.name}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit category"
                    description={`Update ${category.name}.`}
                    icon={FolderPen}
                >
                    <Button variant="outline" asChild>
                        <Link href={machineCategories.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to categories
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <MachineCategoryForm
                        action={machineCategories.update(category.id)}
                        isEdit
                        defaults={{
                            name: category.name,
                            slug: category.slug,
                            description: category.description ?? '',
                            sort_order: category.sort_order,
                            is_active: category.is_active,
                        }}
                        onCancel={() =>
                            router.visit(machineCategories.index().url)
                        }
                    />
                </div>
            </div>
        </>
    );
}

EditMachineCategory.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Categories', href: machineCategories.index() },
        { title: 'Edit', href: '#' },
    ],
};
