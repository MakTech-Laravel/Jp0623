import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Coffee } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { CoffeeMachineForm } from '@/components/admin/coffee-machine-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import coffeeMachines from '@/routes/admin/coffee-machines';
import type { AdminCoffeeMachine } from '@/types/admin';

export default function EditCoffeeMachine({
    coffeeMachine,
}: {
    coffeeMachine: AdminCoffeeMachine;
}) {
    return (
        <>
            <Head title={`Edit ${coffeeMachine.title}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit coffee machine"
                    description={`Update ${coffeeMachine.title}.`}
                    icon={Coffee}
                >
                    <Button variant="outline" asChild>
                        <Link href={coffeeMachines.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to coffee
                            machines
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <CoffeeMachineForm
                        action={coffeeMachines.update(coffeeMachine.id)}
                        isEdit
                        currentImageUrl={coffeeMachine.image_url}
                        defaults={{
                            title: coffeeMachine.title,
                            text: coffeeMachine.text ?? '',
                            tag: coffeeMachine.tag ?? '',
                            is_active: coffeeMachine.is_active,
                            sort_order: coffeeMachine.sort_order,
                        }}
                        onCancel={() =>
                            router.visit(coffeeMachines.index().url)
                        }
                    />
                </div>
            </div>
        </>
    );
}

EditCoffeeMachine.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Coffee Machines', href: coffeeMachines.index() },
        { title: 'Edit', href: '#' },
    ],
};
