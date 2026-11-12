import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Pencil } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { UserForm } from '@/components/admin/user-form';
import { Button } from '@/components/ui/button';
import {
    managementLabels,
    managementRoutes,
    type UserManagementScope,
} from '@/lib/user-management';
import { dashboard } from '@/routes';
import type { AdminOperatorProfile, AdminUser } from '@/types/admin';

interface EditUserProps {
    user: AdminUser;
    scope: UserManagementScope;
    userRoles: string[];
    isLastSuperAdmin: boolean;
    operatorProfile: AdminOperatorProfile | null;
}

export default function EditUser({
    user,
    scope,
    userRoles,
    isLastSuperAdmin,
    operatorProfile,
}: EditUserProps) {
    const routes = managementRoutes(scope);
    const labels = managementLabels(scope);
    const showOperatorProfile = scope === 'user';

    return (
        <>
            <Head title={`${labels.editTitle} ${user.name}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title={labels.editTitle}
                    description={`Update ${user.name}'s account.`}
                    icon={Pencil}
                >
                    <Button variant="outline" asChild>
                        <Link href={routes.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <UserForm
                        action={routes.update(user.id)}
                        hideRoles
                        isEdit
                        currentAvatar={user.avatar}
                        isLastSuperAdmin={isLastSuperAdmin}
                        showOperatorProfile={showOperatorProfile}
                        operatorProfile={operatorProfile}
                        defaults={{
                            name: user.name,
                            email: user.email,
                            roles: userRoles,
                            first_name: operatorProfile?.first_name,
                            last_name: operatorProfile?.last_name,
                            company_name: operatorProfile?.company_name,
                            owns_machines: operatorProfile?.owns_machines,
                            machine_types: operatorProfile?.machine_types,
                            machines_needed: operatorProfile?.machines_needed,
                            primary_city: operatorProfile?.primary_city,
                        }}
                        onCancel={() => router.visit(routes.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditUser.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Edit', href: '#' },
    ],
};
