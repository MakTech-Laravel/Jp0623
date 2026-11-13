import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { useSquareCard } from '@/hooks/use-square-card';
import { dashboard } from '@/routes';
import {
    checkout as subscriptionCheckout,
    index as subscriptionIndex,
} from '@/routes/subscription';

type SubscriptionPageProps = {
    settings: {
        mode: 'free' | 'paid';
        monthly_price: number;
        is_paid: boolean;
    };
    membership: {
        status: string;
        monthly_price: number | null;
        card_brand: string | null;
        card_last4: string | null;
        current_period_end: string | null;
        next_billing_at: string | null;
        needs_payment: boolean;
        allows_access: boolean;
    };
    square: {
        applicationId: string;
        locationId: string;
        sdkUrl: string;
        currency: string;
    } | null;
};

const INCLUDED_FEATURES = [
    'Nationwide lead marketplace',
    'Nearby lead SMS alerts',
    'Billing history and receipts',
] as const;

function statusLabel(status: string): string {
    switch (status) {
        case 'active_free':
            return 'Free member';
        case 'active':
            return 'Active';
        case 'required':
            return 'Payment required';
        case 'past_due':
            return 'Past due';
        case 'canceled':
            return 'Canceled';
        default:
            return status;
    }
}

function IncludedList() {
    return (
        <ul className="space-y-2 text-sm">
            {INCLUDED_FEATURES.map((item) => (
                <li key={item} className="flex gap-2">
                    <span className="text-brass">✓</span>
                    <span>{item}</span>
                </li>
            ))}
        </ul>
    );
}

export default function SubscriptionIndex({
    settings,
    membership,
    square,
}: SubscriptionPageProps) {
    const { auth } = usePage().props;
    const [processing, setProcessing] = useState(false);
    const [paymentError, setPaymentError] = useState<string | null>(null);
    const showCheckout = settings.is_paid && membership.needs_payment;
    const {
        setContainer,
        ready,
        error: cardError,
        tokenize,
    } = useSquareCard(square ?? usePage().props.square ?? null, showCheckout);

    const planPrice = settings.is_paid
        ? `$${settings.monthly_price}/mo`
        : 'Free';

    async function handlePay() {
        if (processing) {
            return;
        }

        setProcessing(true);
        setPaymentError(null);

        try {
            const token = await tokenize({
                amount: settings.monthly_price.toFixed(2),
                currencyCode: square?.currency ?? 'USD',
                intent: 'CHARGE',
                customerInitiated: true,
                sellerKeyedIn: false,
                billingContact: auth.user?.email
                    ? { email: auth.user.email }
                    : undefined,
            });

            router.post(
                subscriptionCheckout().url,
                { source_id: token },
                {
                    preserveScroll: true,
                    onError: (errors) => {
                        setPaymentError(
                            errors.source_id ??
                                'Could not complete membership payment.',
                        );
                    },
                    onFinish: () => setProcessing(false),
                },
            );
        } catch (error) {
            setPaymentError(
                error instanceof Error
                    ? error.message
                    : 'Check your card details and try again.',
            );
            setProcessing(false);
        }
    }

    return (
        <>
            <Head title="Membership" />

            <div className="mx-auto w-full max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Membership
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        {settings.is_paid
                            ? 'A monthly membership is required to buy leads in the marketplace.'
                            : 'Your free membership unlocks the lead marketplace and alerts.'}
                    </p>
                </div>

                {showCheckout ? (
                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                        <div className="rounded-xl border bg-card p-6 shadow-sm">
                            <h2 className="text-lg font-semibold">
                                Current plan
                            </h2>
                            <p className="mt-3 text-4xl font-extrabold tracking-tight">
                                {planPrice}
                            </p>
                            <p className="mt-3 text-sm">
                                Status:{' '}
                                <span className="inline-flex rounded bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-900 dark:text-amber-100">
                                    {statusLabel(membership.status)}
                                </span>
                            </p>
                            {membership.card_last4 ? (
                                <p className="mt-3 text-sm text-muted-foreground">
                                    Card on file:{' '}
                                    {membership.card_brand ?? 'Card'} ••••{' '}
                                    {membership.card_last4}
                                </p>
                            ) : null}
                            <div className="mt-6 border-t pt-5">
                                <h3 className="mb-3 text-sm font-semibold">
                                    What&apos;s included
                                </h3>
                                <IncludedList />
                            </div>
                        </div>

                        <div className="rounded-xl border bg-card p-6 shadow-sm lg:justify-self-start lg:w-full lg:max-w-md">
                            <h2 className="text-lg font-semibold">
                                Activate membership
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Pay ${settings.monthly_price} for the first
                                month. Your card is saved for automatic
                                renewals.
                            </p>
                            <div className="mt-4 space-y-2">
                                <p className="text-sm font-medium">
                                    Card details
                                </p>
                                <div
                                    ref={setContainer}
                                    className="min-h-[90px] rounded-lg border bg-background p-3"
                                />
                            </div>
                            {(cardError || paymentError) && (
                                <p className="mt-3 text-sm text-destructive">
                                    {paymentError ?? cardError}
                                </p>
                            )}
                            <Button
                                className="mt-4 w-full"
                                disabled={!ready || processing}
                                onClick={handlePay}
                            >
                                {processing
                                    ? 'Processing…'
                                    : `Pay $${settings.monthly_price}`}
                            </Button>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        <div className="rounded-xl border bg-card p-6 shadow-sm">
                            <h2 className="text-lg font-semibold">
                                Current plan
                            </h2>
                            <p className="mt-3 text-4xl font-extrabold tracking-tight">
                                {planPrice}
                            </p>
                            <p className="mt-3 text-sm">
                                Status:{' '}
                                <span className="inline-flex rounded bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-200">
                                    {statusLabel(membership.status)}
                                </span>
                            </p>
                            {membership.card_last4 ? (
                                <p className="mt-3 text-sm text-muted-foreground">
                                    Card on file:{' '}
                                    {membership.card_brand ?? 'Card'} ••••{' '}
                                    {membership.card_last4}
                                </p>
                            ) : null}
                            {membership.current_period_end ? (
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Current period ends{' '}
                                    {new Date(
                                        membership.current_period_end,
                                    ).toLocaleDateString()}
                                </p>
                            ) : null}
                            {!settings.is_paid ? (
                                <p className="mt-3 text-sm text-muted-foreground">
                                    No membership fees. You only pay when you
                                    buy a lead.
                                </p>
                            ) : null}
                        </div>

                        <div className="rounded-xl border bg-card p-6 shadow-sm">
                            <h2 className="mb-3 text-lg font-semibold">
                                What&apos;s included
                            </h2>
                            <IncludedList />
                        </div>
                    </div>
                )}

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
