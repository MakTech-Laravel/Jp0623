import { router, usePage } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useSquareCard } from '@/hooks/use-square-card';
import { formatDistance, leadArea } from '@/lib/lead';
import { purchase } from '@/routes/leads';
import type { OperatorLead } from '@/types/operator-dashboard';

interface LeadBuyDialogProps {
    lead: OperatorLead | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export function LeadBuyDialog({
    lead,
    open,
    onOpenChange,
}: LeadBuyDialogProps) {
    const { square, auth } = usePage().props;
    const [processing, setProcessing] = useState(false);
    const [paymentError, setPaymentError] = useState<string | null>(null);
    const {
        setContainer,
        ready,
        error: cardError,
        tokenize,
    } = useSquareCard(square, open && lead !== null);

    const handleOpenChange = (nextOpen: boolean) => {
        if (!nextOpen) {
            setPaymentError(null);
            setProcessing(false);
        }

        onOpenChange(nextOpen);
    };

    if (!lead) {
        return null;
    }

    const handlePay = async () => {
        if (processing) {
            return;
        }

        setProcessing(true);
        setPaymentError(null);

        let token: string;

        try {
            token = await tokenize({
                amount: lead.price.toFixed(2),
                currencyCode: square?.currency ?? 'USD',
                intent: 'CHARGE',
                customerInitiated: true,
                sellerKeyedIn: false,
                billingContact: auth.user?.email
                    ? { email: auth.user.email }
                    : undefined,
            });
        } catch (error) {
            setPaymentError(
                error instanceof Error
                    ? error.message
                    : 'Check your card details and try again.',
            );
            setProcessing(false);

            return;
        }

        const leadId = Number(lead.id.split('-')[1]);

        router.post(
            purchase.url({ source: lead.source, lead: leadId }),
            { source_id: token },
            {
                preserveScroll: true,
                onSuccess: () => handleOpenChange(false),
                onError: (errors) => {
                    setPaymentError(
                        errors.payment ??
                            errors.source_id ??
                            'Could not complete the purchase.',
                    );
                    router.reload({
                        only: ['leads', 'newestLeads', 'kpis'],
                    });
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    const paymentsAvailable = square !== null;

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{lead.type}</DialogTitle>
                    <DialogDescription>
                        {lead.id} · {lead.cat} · {money(lead.price)}
                    </DialogDescription>
                </DialogHeader>

                <dl className="grid gap-3 text-sm sm:grid-cols-2">
                    <Detail label="Location" value={leadArea(lead)} />
                    <Detail
                        label="Distance"
                        value={formatDistance(lead.dist)}
                    />
                    <Detail
                        label="People"
                        value={lead.people !== null ? String(lead.people) : '—'}
                    />
                    <Detail label="Wants" value={lead.wants || '—'} />
                    <Detail label="Placement" value={lead.io ?? '—'} />
                    <Detail label="Listed" value={lead.listed || '—'} />
                </dl>

                <p className="flex items-start gap-2 rounded-lg bg-muted p-3 text-sm text-muted-foreground">
                    <Lock className="mt-0.5 size-4 shrink-0" />
                    Business name, address and contact details unlock after your
                    payment is confirmed. Each lead is sold to one operator
                    only.
                </p>

                {paymentsAvailable ? (
                    <div className="space-y-2">
                        <p className="text-sm font-medium">Card details</p>
                        <div
                            ref={setContainer}
                            data-test="square-card-container"
                            className="min-h-[90px] rounded-lg border bg-background p-3"
                        />
                        {cardError ? (
                            <p className="text-sm text-destructive">
                                {cardError}
                            </p>
                        ) : null}
                    </div>
                ) : (
                    <p className="rounded-lg border border-amber-500/40 bg-amber-500/10 p-3 text-sm text-amber-800 dark:text-amber-200">
                        Payments are not available right now. Please try again
                        later.
                    </p>
                )}

                {paymentError ? (
                    <p
                        role="alert"
                        data-test="payment-error"
                        className="text-sm font-medium text-destructive"
                    >
                        {paymentError}
                    </p>
                ) : null}

                <DialogFooter className="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        disabled={processing}
                        onClick={() => handleOpenChange(false)}
                    >
                        Cancel
                    </Button>
                    <Button
                        className="bg-brass text-brass-ink hover:bg-brass/90"
                        disabled={processing || !paymentsAvailable || !ready}
                        onClick={() => {
                            void handlePay();
                        }}
                    >
                        {processing
                            ? 'Processing…'
                            : `Pay ${money(lead.price)} and unlock`}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function Detail({
    label,
    value,
    className,
}: {
    label: string;
    value: string;
    className?: string;
}) {
    return (
        <div className={className}>
            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </dt>
            <dd className="mt-1">{value}</dd>
        </div>
    );
}
