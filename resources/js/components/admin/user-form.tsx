import type { UrlMethodPair } from '@inertiajs/core';
import { useForm, usePage } from '@inertiajs/react';
import { Loader2, Lock, UserPlus } from 'lucide-react';
import { motion } from 'motion/react';
import { toast } from 'sonner';
import FileUpload from '@/components/file-upload';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    OPERATOR_MACHINE_TYPES,
    OPERATOR_MACHINES_NEEDED,
} from '@/lib/operator-profile';
import { cn } from '@/lib/utils';
import { avatarUrl, SUPER_ADMIN_ROLE } from '@/types/admin';
import type { AdminOperatorProfile, RoleRef } from '@/types/admin';

interface UserFormDefaults {
    name: string;
    email: string;
    roles: string[];
    first_name?: string;
    last_name?: string;
    company_name?: string;
    owns_machines?: boolean;
    machine_types?: string[];
    machines_needed?: string;
    primary_city?: string;
}

interface UserFormProps {
    action: UrlMethodPair;
    roles?: RoleRef[];
    isEdit?: boolean;
    currentAvatar?: string | null;
    isLastSuperAdmin?: boolean;
    hideRoles?: boolean;
    /** When true, collect operator registration profile fields. */
    showOperatorProfile?: boolean;
    operatorProfile?: AdminOperatorProfile | null;
    defaults?: UserFormDefaults;
    onCancel?: () => void;
}

export function UserForm({
    action,
    roles = [],
    isEdit = false,
    currentAvatar = null,
    isLastSuperAdmin = false,
    hideRoles = false,
    showOperatorProfile = false,
    operatorProfile = null,
    defaults,
    onCancel,
}: UserFormProps) {
    const actorIsSuperAdmin =
        usePage().props.auth.user?.is_super_admin ?? false;

    const form = useForm(action, {
        name: defaults?.name ?? '',
        email: defaults?.email ?? '',
        password: '',
        roles: defaults?.roles ?? [],
        avatar: null as File | null,
        remove_avatar: false as boolean,
        first_name:
            defaults?.first_name ?? operatorProfile?.first_name ?? '',
        last_name: defaults?.last_name ?? operatorProfile?.last_name ?? '',
        company_name:
            defaults?.company_name ?? operatorProfile?.company_name ?? '',
        owns_machines:
            defaults?.owns_machines ?? operatorProfile?.owns_machines ?? false,
        machine_types:
            defaults?.machine_types ?? operatorProfile?.machine_types ?? [],
        machines_needed:
            defaults?.machines_needed ??
            operatorProfile?.machines_needed ??
            '1',
        primary_city:
            defaults?.primary_city ?? operatorProfile?.primary_city ?? '',
    });

    const selectableRoles = actorIsSuperAdmin
        ? roles
        : roles.filter((r) => r.name !== SUPER_ADMIN_ROLE);

    const toggleRole = (name: string) => {
        const hasRole = form.data.roles.includes(name);

        if (name === SUPER_ADMIN_ROLE && hasRole && isLastSuperAdmin) {
            toast.error(
                'Assign the super-admin role to another user before removing it from the last super administrator.',
            );

            return;
        }

        form.setData(
            'roles',
            hasRole
                ? form.data.roles.filter((r) => r !== name)
                : [...form.data.roles, name],
        );
    };

    const toggleMachineType = (type: string) => {
        const current = form.data.machine_types;
        form.setData(
            'machine_types',
            current.includes(type)
                ? current.filter((item) => item !== type)
                : [...current, type],
        );
    };

    const existingAvatar =
        isEdit && currentAvatar && !form.data.avatar && !form.data.remove_avatar
            ? [
                  {
                      id: 'current',
                      path: currentAvatar,
                      url: avatarUrl(currentAvatar) ?? '',
                      mime_type: 'image/*',
                  },
              ]
            : [];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.submit({
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
                }
            },
        });
    };

    return (
        <motion.form
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.25 }}
            onSubmit={handleSubmit}
            className="space-y-6"
        >
            {isLastSuperAdmin && (
                <Alert>
                    <Lock className="h-4 w-4" />
                    <AlertTitle>Last super administrator</AlertTitle>
                    <AlertDescription>
                        This is the only account with the super-admin role. To
                        change it, first assign the super-admin role to another
                        user.
                    </AlertDescription>
                </Alert>
            )}

            <div className="grid gap-2">
                <Label>Profile photo</Label>
                <FileUpload
                    accept="image/*"
                    maxSize={2}
                    value={form.data.avatar}
                    onChange={(file) => {
                        form.setData('avatar', (file as File | null) ?? null);
                        form.setData('remove_avatar', false);
                    }}
                    existingFiles={existingAvatar}
                    onRemoveExisting={() => form.setData('remove_avatar', true)}
                    placeholder="Drag & drop an avatar, or click to browse"
                    hint="PNG, JPG or WEBP"
                    error={form.errors.avatar}
                    classNames={{ wrapper: 'sm:max-w-md' }}
                />
            </div>

            {showOperatorProfile ? (
                <>
                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="first_name">First name</Label>
                            <Input
                                id="first_name"
                                value={form.data.first_name}
                                onChange={(e) =>
                                    form.setData('first_name', e.target.value)
                                }
                                onBlur={() => form.validate('first_name')}
                                aria-invalid={form.invalid('first_name')}
                                placeholder="Jane"
                                autoComplete="given-name"
                            />
                            <InputError message={form.errors.first_name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="last_name">Last name</Label>
                            <Input
                                id="last_name"
                                value={form.data.last_name}
                                onChange={(e) =>
                                    form.setData('last_name', e.target.value)
                                }
                                onBlur={() => form.validate('last_name')}
                                aria-invalid={form.invalid('last_name')}
                                placeholder="Doe"
                                autoComplete="family-name"
                            />
                            <InputError message={form.errors.last_name} />
                        </div>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                value={form.data.email}
                                onChange={(e) =>
                                    form.setData('email', e.target.value)
                                }
                                onBlur={() => form.validate('email')}
                                aria-invalid={form.invalid('email')}
                                placeholder="jane@example.com"
                                autoComplete="email"
                            />
                            <InputError message={form.errors.email} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="company_name">Company name</Label>
                            <Input
                                id="company_name"
                                value={form.data.company_name}
                                onChange={(e) =>
                                    form.setData('company_name', e.target.value)
                                }
                                onBlur={() => form.validate('company_name')}
                                aria-invalid={form.invalid('company_name')}
                                placeholder="Acme Vending"
                            />
                            <InputError message={form.errors.company_name} />
                        </div>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="primary_city">Primary city</Label>
                            <Input
                                id="primary_city"
                                value={form.data.primary_city}
                                onChange={(e) =>
                                    form.setData('primary_city', e.target.value)
                                }
                                onBlur={() => form.validate('primary_city')}
                                aria-invalid={form.invalid('primary_city')}
                                placeholder="Houston, TX"
                            />
                            <InputError message={form.errors.primary_city} />
                        </div>
                        <div className="grid gap-2">
                            <Label>Owns machines</Label>
                            <div className="flex flex-wrap gap-2">
                                {(['Yes', 'No'] as const).map((label) => {
                                    const value = label === 'Yes';
                                    const checked =
                                        form.data.owns_machines === value;

                                    return (
                                        <Label
                                            key={label}
                                            className={cn(
                                                'flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors',
                                                checked
                                                    ? 'border-primary bg-primary/10 text-primary'
                                                    : 'hover:bg-muted',
                                            )}
                                        >
                                            <Checkbox
                                                checked={checked}
                                                onCheckedChange={() =>
                                                    form.setData(
                                                        'owns_machines',
                                                        value,
                                                    )
                                                }
                                                className="size-3.5"
                                            />
                                            {label}
                                        </Label>
                                    );
                                })}
                            </div>
                            <InputError message={form.errors.owns_machines} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label>Machine types</Label>
                        <div className="mt-1 flex flex-wrap gap-2">
                            {OPERATOR_MACHINE_TYPES.map((type) => {
                                const checked =
                                    form.data.machine_types.includes(type);

                                return (
                                    <Label
                                        key={type}
                                        className={cn(
                                            'flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors',
                                            checked
                                                ? 'border-primary bg-primary/10 text-primary'
                                                : 'hover:bg-muted',
                                        )}
                                    >
                                        <Checkbox
                                            checked={checked}
                                            onCheckedChange={() =>
                                                toggleMachineType(type)
                                            }
                                            className="size-3.5"
                                        />
                                        {type}
                                    </Label>
                                );
                            })}
                        </div>
                        <InputError message={form.errors.machine_types} />
                    </div>

                    <div className="grid gap-2 sm:max-w-sm">
                        <Label htmlFor="machines_needed">
                            Machines needed
                        </Label>
                        <Select
                            value={form.data.machines_needed}
                            onValueChange={(value) =>
                                form.setData('machines_needed', value)
                            }
                        >
                            <SelectTrigger id="machines_needed">
                                <SelectValue placeholder="Select quantity" />
                            </SelectTrigger>
                            <SelectContent>
                                {OPERATOR_MACHINES_NEEDED.map((qty) => (
                                    <SelectItem key={qty} value={qty}>
                                        {qty}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.machines_needed} />
                    </div>
                </>
            ) : (
                <div className="grid gap-5 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Full name</Label>
                        <Input
                            id="name"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            onBlur={() => form.validate('name')}
                            aria-invalid={form.invalid('name')}
                            placeholder="Jane Doe"
                            autoComplete="name"
                        />
                        <InputError message={form.errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            value={form.data.email}
                            onChange={(e) =>
                                form.setData('email', e.target.value)
                            }
                            onBlur={() => form.validate('email')}
                            aria-invalid={form.invalid('email')}
                            placeholder="jane@example.com"
                            autoComplete="email"
                        />
                        <InputError message={form.errors.email} />
                    </div>
                </div>
            )}

            <div className="grid gap-2 sm:max-w-sm">
                <Label htmlFor="password">
                    Password
                    {isEdit && (
                        <span className="ml-1 text-xs font-normal text-muted-foreground">
                            (leave blank to keep current)
                        </span>
                    )}
                </Label>
                <Input
                    id="password"
                    type="password"
                    value={form.data.password}
                    onChange={(e) => form.setData('password', e.target.value)}
                    onBlur={() => form.validate('password')}
                    aria-invalid={form.invalid('password')}
                    placeholder="••••••••"
                    autoComplete="new-password"
                />
                <InputError message={form.errors.password} />
            </div>

            {!hideRoles && (
                <div className="grid gap-2">
                    <Label>Roles</Label>
                    <p className="text-xs text-muted-foreground">
                        Assign one or more roles. Permissions are inherited from
                        the selected roles.
                    </p>
                    <div className="mt-1 flex flex-wrap gap-2">
                        {selectableRoles.length === 0 && (
                            <span className="text-sm text-muted-foreground">
                                No roles available.
                            </span>
                        )}
                        {selectableRoles.map((role) => {
                            const checked = form.data.roles.includes(role.name);
                            const isProtected = role.name === SUPER_ADMIN_ROLE;
                            const locked =
                                isProtected && isLastSuperAdmin && checked;

                            return (
                                <Label
                                    key={role.id}
                                    className={cn(
                                        'flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium capitalize transition-colors',
                                        checked
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'hover:bg-muted',
                                        locked && 'cursor-not-allowed',
                                    )}
                                >
                                    <Checkbox
                                        checked={checked}
                                        onCheckedChange={() =>
                                            toggleRole(role.name)
                                        }
                                        className="size-3.5"
                                    />
                                    {role.name}
                                    {locked && (
                                        <Lock className="h-3 w-3 opacity-70" />
                                    )}
                                </Label>
                            );
                        })}
                    </div>
                    <InputError message={form.errors.roles} />
                </div>
            )}

            <div className="flex items-center gap-3 border-t pt-5">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? (
                        <Loader2 className="h-4 w-4 animate-spin" />
                    ) : (
                        <UserPlus className="h-4 w-4" />
                    )}
                    {isEdit ? 'Save changes' : 'Create user'}
                </Button>
                {onCancel && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={form.processing}
                    >
                        Cancel
                    </Button>
                )}
                {form.validating && (
                    <Badge variant="secondary" className="gap-1.5">
                        <Loader2 className="h-3 w-3 animate-spin" /> Validating…
                    </Badge>
                )}
            </div>
        </motion.form>
    );
}
