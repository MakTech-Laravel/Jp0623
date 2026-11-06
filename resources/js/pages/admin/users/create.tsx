import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, UserPlus } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { UserForm } from '@/components/admin/user-form';
import { Button } from '@/components/ui/button';
import {
    managementLabels,
    managementRoutes,
    type UserManagementScope,
} from '@/lib/user-management';
import { dashboard } from '@/routes';

export default function CreateUser({ scope }: { scope: UserManagementScope }) {
    const routes = managementRoutes(scope);
    const labels = managementLabels(scope);
    const showOperatorProfile = scope === 'user';

    return (
        <>
            <Head title={labels.createTitle} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title={labels.createTitle}
                    description={labels.createDescription}
                    icon={UserPlus}
                >
                    <Button variant="outline" asChild>
                        <Link href={routes.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="max-w-3xl rounded-xl border bg-card p-6 shadow-sm">
                    <UserForm
                        action={routes.store()}
                        hideRoles
                        showOperatorProfile={showOperatorProfile}
                        onCancel={() => router.visit(routes.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateUser.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Create', href: '#' },
    ],
};
