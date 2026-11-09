import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    Calendar,
    IdCard,
    KeyRound,
    Mail,
    MapPin,
    Package,
    Pencil,
    ShieldCheck,
} from 'lucide-react';
import { motion } from 'motion/react';
import type { LucideIcon } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePermission } from '@/hooks/use-permissions';
import {
    managementLabels,
    managementRoutes,
    type UserManagementScope,
} from '@/lib/user-management';
import { dashboard } from '@/routes';
import { avatarUrl } from '@/types/admin';
import type { AdminOperatorProfile, AdminUserDetail } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString(undefined, {
        dateStyle: 'medium',
    });
}

export default function ShowUser({
    user,
    scope,
    operatorProfile,
}: {
    user: AdminUserDetail;
    scope: UserManagementScope;
    operatorProfile: AdminOperatorProfile | null;
}) {
    const { can } = usePermission();
    const url = avatarUrl(user.avatar);
    const routes = managementRoutes(scope);
    const labels = managementLabels(scope);

    return (
        <>
            <Head title={user.name} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title={`${labels.singular} profile`}
                    icon={IdCard}
                >
                    <Button variant="outline" asChild>
                        <Link href={routes.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                    {can(PERMISSIONS.USERS.EDIT) && (
                        <Button asChild>
                            <Link href={routes.edit(user.id).url}>
                                <Pencil className="h-4 w-4" /> Edit
                            </Link>
                        </Button>
                    )}
                </AdminPageHeader>

                <motion.div
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="grid gap-6 lg:grid-cols-3"
                >
                    <div className="overflow-hidden rounded-xl border bg-card shadow-sm lg:col-span-1">
                        <div className="flex flex-col items-center gap-3 border-b bg-muted/40 p-6 text-center">
                            <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-primary/20 bg-primary/10 text-2xl font-bold text-primary">
                                {url ? (
                                    <img
                                        src={url}
                                        alt={user.name}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    user.name.charAt(0).toUpperCase()
                                )}
                            </div>
                            <div>
                                <h2 className="text-lg font-bold">
                                    {user.name}
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    {user.email}
                                </p>
                            </div>
                        </div>
                        <dl className="divide-y text-sm">
                            <Row
                                icon={IdCard}
                                label="User ID"
                                value={`#${user.id}`}
                            />
                            <Row icon={Mail} label="Email" value={user.email} />
                            <Row
                                icon={ShieldCheck}
                                label="Verified"
                                value={
                                    user.email_verified_at ? 'Yes' : 'Pending'
                                }
                            />
                            <Row
                                icon={Calendar}
                                label="Joined"
                                value={new Date(
                                    user.created_at,
                                ).toLocaleDateString(undefined, {
                                    dateStyle: 'long',
                                })}
                            />
                        </dl>
                    </div>

                    <div className="space-y-6 lg:col-span-2">
                        {scope === 'user' && (
                            <div className="rounded-xl border bg-card p-6 shadow-sm">
                                <div className="mb-4 flex items-center gap-2">
                                    <Building2 className="h-4 w-4 text-primary" />
                                    <h3 className="text-sm font-semibold">
                                        Registration details
                                    </h3>
                                </div>
                                {operatorProfile ? (
                                    <dl className="grid gap-4 sm:grid-cols-2">
                                        <Detail
                                            label="First name"
                                            value={operatorProfile.first_name}
                                        />
                                        <Detail
                                            label="Last name"
                                            value={operatorProfile.last_name}
                                        />
                                        <Detail
                                            label="Company"
                                            value={operatorProfile.company_name}
                                        />
                                        <Detail
                                            label="Primary city"
                                            value={operatorProfile.primary_city}
                                            icon={MapPin}
                                        />
                                        <Detail
                                            label="Owns machines"
                                            value={
                                                operatorProfile.owns_machines
                                                    ? 'Yes'
                                                    : 'No'
                                            }
                                        />
                                        <Detail
                                            label="Machines needed"
                                            value={
                                                operatorProfile.machines_needed
                                            }
                                            icon={Package}
                                        />
                                        <div className="sm:col-span-2">
                                            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                Machine types
                                            </dt>
                                            <dd className="mt-2 flex flex-wrap gap-1.5">
                                                {operatorProfile.machine_types
                                                    .length === 0 ? (
                                                    <span className="text-sm text-muted-foreground">
                                                        —
                                                    </span>
                                                ) : (
                                                    operatorProfile.machine_types.map(
                                                        (type) => (
                                                            <Badge
                                                                key={type}
                                                                variant="secondary"
                                                            >
                                                                {type}
                                                            </Badge>
                                                        ),
                                                    )
                                                )}
                                            </dd>
                                        </div>
                                        <Detail
                                            label="Terms accepted"
                                            value={formatDate(
                                                operatorProfile.terms_accepted_at,
                                            )}
                                        />
                                        <Detail
                                            label="Email code confirmed"
                                            value={formatDate(
                                                operatorProfile.email_code_confirmed_at,
                                            )}
                                        />
                                    </dl>
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No registration profile on file for this
                                        account yet.
                                    </p>
                                )}
                            </div>
                        )}

                        <div className="rounded-xl border bg-card p-6 shadow-sm">
                            <div className="mb-4 flex items-center gap-2">
                                <KeyRound className="h-4 w-4 text-primary" />
                                <h3 className="text-sm font-semibold">
                                    Access
                                </h3>
                                {scope !== 'admin' && (
                                    <Badge
                                        variant="outline"
                                        className="ml-auto"
                                    >
                                        {user.permissions.length}
                                    </Badge>
                                )}
                            </div>
                            {scope === 'admin' ? (
                                <p className="text-sm text-muted-foreground">
                                    Administrators automatically receive all
                                    admin permissions.
                                </p>
                            ) : user.permissions.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    This user has no direct permissions. Access
                                    is granted through their assigned role.
                                </p>
                            ) : (
                                <div className="flex flex-wrap gap-1.5">
                                    {user.permissions.map((p) => (
                                        <Badge
                                            key={p.id}
                                            variant="secondary"
                                            className="font-mono text-xs font-normal"
                                        >
                                            {p.name}
                                        </Badge>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </motion.div>
            </div>
        </>
    );
}

function Row({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value: string;
}) {
    return (
        <div className="flex items-center justify-between gap-2 px-6 py-3">
            <dt className="flex items-center gap-2 text-muted-foreground">
                <Icon className="h-4 w-4 opacity-70" /> {label}
            </dt>
            <dd className="font-medium text-foreground">{value}</dd>
        </div>
    );
}

function Detail({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: string;
    icon?: LucideIcon;
}) {
    return (
        <div>
            <dt className="flex items-center gap-1.5 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {Icon && <Icon className="h-3.5 w-3.5 opacity-70" />}
                {label}
            </dt>
            <dd className="mt-1 text-sm font-medium">{value}</dd>
        </div>
    );
}

ShowUser.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Profile', href: '#' },
    ],
};
