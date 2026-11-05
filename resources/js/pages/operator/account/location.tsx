import { Head } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { location as accountLocation } from '@/routes/account';

export default function AccountLocation({
    primaryCity,
    changesRemaining,
    changesTotal,
}: {
    primaryCity: string;
    changesRemaining: number;
    changesTotal: number;
}) {
    const [city, setCity] = useState(primaryCity);
    const [remaining, setRemaining] = useState(changesRemaining);

    const handleSave = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const nextRemaining = Math.max(remaining - 1, 0);
        setRemaining(nextRemaining);
        toast.success(
            `Primary city saved. Changes left this period: ${nextRemaining} of ${changesTotal}.`,
        );
    };

    return (
        <>
            <Head title="Location settings" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Location settings
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        You get lead alerts within 50 miles of your primary
                        city.
                    </p>
                </div>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <form onSubmit={handleSave} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="primary-city">
                                Primary city I offer vending in
                            </Label>
                            <Input
                                id="primary-city"
                                required
                                value={city}
                                onChange={(event) => setCity(event.target.value)}
                            />
                        </div>
                        <p className="text-sm text-muted-foreground">
                            You may change your primary city up to {changesTotal}{' '}
                            times in 90 days. Changes remaining:{' '}
                            <strong>
                                {remaining} of {changesTotal}
                            </strong>
                            .
                        </p>
                        <div className="space-y-2">
                            <Label htmlFor="addon-areas">
                                Additional service areas (Multi-market add-on)
                            </Label>
                            <Input
                                id="addon-areas"
                                placeholder="e.g. Austin, TX"
                                disabled
                            />
                        </div>
                        <Button
                            type="submit"
                            className="bg-brass text-brass-ink hover:bg-brass/90"
                        >
                            Save
                        </Button>
                    </form>
                </div>
            </div>
        </>
    );
}

AccountLocation.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Location settings', href: accountLocation() },
    ],
};
