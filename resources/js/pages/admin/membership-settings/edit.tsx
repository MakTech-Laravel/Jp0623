import { Head, useForm } from '@inertiajs/react';
import { CreditCard, Save } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes';
import membershipSettings from '@/routes/admin/membership-settings';

type MembershipSettingsEditProps = {
    settings: {
        mode: 'free' | 'paid';
        monthly_price: number;
        grace_days: number;
    };
    counts: {
        required: number;
        past_due: number;
        active: number;
        active_free: number;
    };
};

export default function EditMembershipSettings({
    settings,
    counts,
}: MembershipSettingsEditProps) {
    const form = useForm(membershipSettings.update(), {
        mode: settings.mode,
        monthly_price: settings.monthly_price,
        grace_days: settings.grace_days,
    });

    function handleSubmit(event: React.FormEvent) {
        event.preventDefault();
        form.submit({ preserveScroll: true });
    }

    return (
        <>
            <Head title="Membership settings" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Membership settings"
                    description="Control whether operator membership is free or billed monthly."
                    icon={CreditCard}
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Stat label="Active free" value={counts.active_free} />
                    <Stat label="Active paid" value={counts.active} />
                    <Stat label="Required" value={counts.required} />
                    <Stat label="Past due" value={counts.past_due} />
                </div>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <form onSubmit={handleSubmit} className="max-w-lg space-y-6">
                        <div className="grid gap-2">
                            <Label>Membership mode</Label>
                            <Select
                                value={form.data.mode}
                                onValueChange={(value: 'free' | 'paid') =>
                                    form.setData('mode', value)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="free">Free</SelectItem>
                                    <SelectItem value="paid">
                                        Paid monthly
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.mode} />
                            <p className="text-xs text-muted-foreground">
                                Switching to paid requires every operator to
                                subscribe. Switching to free stops new charges.
                            </p>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="monthly_price">
                                Monthly price (USD)
                            </Label>
                            <Input
                                id="monthly_price"
                                type="number"
                                min={1}
                                value={form.data.monthly_price}
                                onChange={(e) =>
                                    form.setData(
                                        'monthly_price',
                                        Number(e.target.value) || 0,
                                    )
                                }
                            />
                            <InputError message={form.errors.monthly_price} />
                        </div>

                        <Button type="submit" disabled={form.processing}>
                            <Save className="h-4 w-4" />
                            {form.processing ? 'Saving…' : 'Save settings'}
                        </Button>
                    </form>
                </div>
            </div>
        </>
    );
}

function Stat({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-xl border bg-card p-4 shadow-sm">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-1 text-2xl font-semibold">{value}</p>
        </div>
    );
}

EditMembershipSettings.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Membership settings', href: membershipSettings.edit() },
    ],
};
