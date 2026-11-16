import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Building2 } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { CityForm } from '@/components/admin/city-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import cities from '@/routes/admin/cities';

export default function CreateCity({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create city" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create city"
                    description="Add a city to the cities we manage vending in."
                    icon={Building2}
                >
                    <Button variant="outline" asChild>
                        <Link href={cities.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to cities
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <CityForm
                        action={cities.store()}
                        defaultSortOrder={defaultSortOrder}
                        onCancel={() => router.visit(cities.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateCity.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Cities', href: cities.index() },
        { title: 'Create', href: cities.create() },
    ],
};
