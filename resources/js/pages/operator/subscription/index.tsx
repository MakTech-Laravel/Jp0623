import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as subscriptionIndex } from '@/routes/subscription';

interface SubscriptionPageProps {
    subscription: {
        planPrice: string;
        status: string;
        includes: string[];
    };
}

export default function SubscriptionIndex({
    subscription,
}: SubscriptionPageProps) {
    return (
        <>
            <Head title="Membership" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Membership
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Your free membership unlocks the lead marketplace and
                        alerts.
                    </p>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="text-lg font-semibold">Current plan</h2>
                        <p className="mt-3 text-4xl font-extrabold tracking-tight">
                            {subscription.planPrice}
                        </p>
                        <p className="mt-3 text-sm">
                            Status:{' '}
                            <span className="inline-flex rounded bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-200">
                                Free member
                            </span>
                        </p>
                        <p className="mt-3 text-sm text-muted-foreground">
                            No membership fees. You only pay when you buy a
                            lead.
                        </p>
                    </div>

                    <div className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="mb-3 text-lg font-semibold">
                            What&apos;s included
                        </h2>
                        <ul className="space-y-2 text-sm">
                            {subscription.includes.map((item) => (
                                <li key={item} className="flex gap-2">
                                    <span className="text-brass">✓</span>
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                <Button variant="outline" asChild>
                    <Link href={dashboard()}>Back to dashboard</Link>
                </Button>
            </div>
        </>
    );
}

SubscriptionIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Membership', href: subscriptionIndex() },
    ],
};
