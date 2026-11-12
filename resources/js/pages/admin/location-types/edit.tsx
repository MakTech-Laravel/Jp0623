import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, MapPin } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { LocationTypeForm } from '@/components/admin/location-type-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import locationTypes from '@/routes/admin/location-types';
import type { AdminLocationType } from '@/types/admin';

export default function EditLocationType({
    locationType,
}: {
    locationType: AdminLocationType;
}) {
    return (
        <>
            <Head title={`Edit ${locationType.name}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit location type"
                    description="Update this Type of location option."
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
                        action={locationTypes.update(locationType.id)}
                        isEdit
                        defaults={{
                            name: locationType.name,
                            sort_order: locationType.sort_order,
                            is_active: locationType.is_active,
                        }}
                        onCancel={() => router.visit(locationTypes.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditLocationType.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Location types', href: locationTypes.index() },
        { title: 'Edit', href: '#' },
    ],
};
