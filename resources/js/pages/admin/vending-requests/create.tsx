import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Inbox } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { VendingRequestForm } from '@/components/admin/vending-request-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import vendingRequests from '@/routes/admin/vending-requests';

export default function CreateVendingRequest({
    defaultPrice,
    locationTypes,
    placements,
    machineInterests,
    machineCounts,
    existingVending,
    yesNoOptions,
}: {
    defaultPrice: number;
    locationTypes: string[];
    placements: string[];
    machineInterests: string[];
    machineCounts: string[];
    existingVending: string[];
    yesNoOptions: string[];
}) {
    return (
        <>
            <Head title="Create vending request" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create vending request"
                    description="Manually add a lead and set its marketplace price."
                    icon={Inbox}
                >
                    <Button variant="outline" asChild>
                        <Link href={vendingRequests.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <VendingRequestForm
                        action={vendingRequests.store()}
                        defaultPrice={defaultPrice}
                        locationTypes={locationTypes}
                        placements={placements}
                        machineInterests={machineInterests}
                        machineCounts={machineCounts}
                        existingVending={existingVending}
                        yesNoOptions={yesNoOptions}
                        onCancel={() =>
                            router.visit(vendingRequests.index().url)
                        }
                    />
                </div>
            </div>
        </>
    );
}

CreateVendingRequest.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Vending requests', href: vendingRequests.index() },
        { title: 'Create', href: vendingRequests.create() },
    ],
};
