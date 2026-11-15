import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Coffee } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { CoffeeTypeForm } from '@/components/admin/coffee-type-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import coffeeTypes from '@/routes/admin/coffee-types';
import type { AdminCoffeeType } from '@/types/admin';

export default function EditCoffeeType({
    coffeeType,
}: {
    coffeeType: AdminCoffeeType;
}) {
    return (
        <>
            <Head title={`Edit ${coffeeType.label}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit coffee type"
                    description={`Update ${coffeeType.label}.`}
                    icon={Coffee}
                >
                    <Button variant="outline" asChild>
                        <Link href={coffeeTypes.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to coffee
                            types
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <CoffeeTypeForm
                        action={coffeeTypes.update(coffeeType.id)}
                        isEdit
                        defaults={{
                            label: coffeeType.label,
                            sort_order: coffeeType.sort_order,
                            is_active: coffeeType.is_active,
                        }}
                        onCancel={() => router.visit(coffeeTypes.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditCoffeeType.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Coffee types', href: coffeeTypes.index() },
        { title: 'Edit', href: '#' },
    ],
};
