import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Coffee } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { CoffeeTypeForm } from '@/components/admin/coffee-type-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import coffeeTypes from '@/routes/admin/coffee-types';

export default function CreateCoffeeType({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create coffee type" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create coffee type"
                    description="Add an item to the “What we stock.” list on the office coffee page."
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
                        action={coffeeTypes.store()}
                        defaults={{
                            label: '',
                            sort_order: defaultSortOrder,
                            is_active: true,
                        }}
                        onCancel={() => router.visit(coffeeTypes.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateCoffeeType.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Coffee types', href: coffeeTypes.index() },
        { title: 'Create', href: coffeeTypes.create() },
    ],
};
