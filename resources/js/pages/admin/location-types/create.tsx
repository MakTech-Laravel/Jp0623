import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, MapPin } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { LocationTypeForm } from '@/components/admin/location-type-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import locationTypes from '@/routes/admin/location-types';

export default function CreateLocationType({
    defaultSortOrder,
    kindOptions,
}: {
    defaultSortOrder: number;
    kindOptions: string[];
}) {
    return (
        <>
            <Head title="Create location type" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create location type"
                    description="Add an option for the Type of location field. It can also appear as an industry on the website."
                    icon={MapPin}
                >
                    <Button variant="outline" asChild>
                        <Link href={locationTypes.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to location
                            types
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <LocationTypeForm
                        action={locationTypes.store()}
                        kindOptions={kindOptions}
                        defaults={{
                            name: '',
                            slug: '',
                            description: '',
                            kind: '',
                            sort_order: defaultSortOrder,
                            is_active: true,
                            show_on_industries: true,
                        }}
                        onCancel={() => router.visit(locationTypes.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateLocationType.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Location types', href: locationTypes.index() },
        { title: 'Create', href: locationTypes.create() },
    ],
};
