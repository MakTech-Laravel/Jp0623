import { router } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    const [processing, setProcessing] = useState(false);

    if (!lead) {
        return null;
    }

    const handleBuy = () => {
        const leadId = Number(lead.id.split('-')[1]);

        router.post(
            purchase.url({ source: lead.source, lead: leadId }),
            {},
            {
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => {
                    toast.success(
                        `Lead ${lead.id} purchased. Contact details unlocked.`,
                    );
                    onOpenChange(false);
                },
                onError: () => toast.error('Could not buy this lead.'),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
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
                    Business name, address and contact details unlock after you
                    buy this lead.
                </p>

                <DialogFooter className="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        Cancel
                    </Button>
                    <Button
                        className="bg-brass text-brass-ink hover:bg-brass/90"
                        disabled={processing}
                        onClick={handleBuy}
                    >
                        Buy lead · {money(lead.price)}
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
