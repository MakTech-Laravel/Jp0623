import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Coffee } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { CoffeeServiceRequestForm } from '@/components/admin/coffee-service-request-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import coffeeServiceRequests from '@/routes/admin/coffee-service-requests';

export default function CreateCoffeeServiceRequest({
    defaultPrice,
}: {
    defaultPrice: number;
}) {
    return (
        <>
            <Head title="Create coffee request" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create coffee request"
                    description="Manually add an office coffee lead and set its marketplace price."
                    icon={Coffee}
                >
                    <Button variant="outline" asChild>
                        <Link href={coffeeServiceRequests.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <CoffeeServiceRequestForm
                        action={coffeeServiceRequests.store()}
                        defaultPrice={defaultPrice}
                        onCancel={() =>
                            router.visit(coffeeServiceRequests.index().url)
                        }
                    />
                </div>
            </div>
        </>
    );
}

CreateCoffeeServiceRequest.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        {
            title: 'Coffee requests',
            href: coffeeServiceRequests.index(),
        },
        { title: 'Create', href: coffeeServiceRequests.create() },
    ],
};
