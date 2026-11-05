import { Head, Link } from '@inertiajs/react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as subscriptionIndex } from '@/routes/subscription';

interface SubscriptionPageProps {
    subscription: {
        planPrice: string;
        status: string;
        daysLeft: number;
        renews: string;
        includes: string[];
    };
}

export default function SubscriptionIndex({
    subscription,
}: SubscriptionPageProps) {
    return (
        <>
            <Head title="Subscription" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Subscription
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Your membership unlocks the lead marketplace and
                        alerts.
                    </p>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <div className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="text-lg font-semibold">Current plan</h2>
                        <p className="mt-3 text-4xl font-extrabold tracking-tight">
                            {subscription.planPrice}{' '}
                            <span className="text-base font-medium text-muted-foreground">
                                / month
                            </span>
                        </p>
                        <p className="mt-3 text-sm">
                            Status:{' '}
                            <span className="inline-flex rounded bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:text-amber-200">
                                Free trial · {subscription.daysLeft} days left
                            </span>
                        </p>
                        <div className="mt-5 flex flex-wrap gap-2">
                            <Button
                                className="bg-brass text-brass-ink hover:bg-brass/90"
                                onClick={() =>
                                    toast.success(
                                        'Subscribe flow coming soon (demo).',
                                    )
                                }
                            >
                                Subscribe now
                            </Button>
                            <Button
                                variant="outline"
                                onClick={() =>
                                    toast.message(
                                        'Cancel flow coming soon (demo).',
                                    )
                                }
                            >
                                Cancel subscription
                            </Button>
                        </div>
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
                        <p className="mt-4 text-xs text-muted-foreground">
                            Trial renews / ends {subscription.renews}.
                        </p>
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
        { title: 'Subscription', href: subscriptionIndex() },
    ],
};
