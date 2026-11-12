import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { PurchasedLead } from '@/types/operator-dashboard';

interface PurchasedLeadDetailsDialogProps {
    lead: PurchasedLead | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onReportIssue?: (lead: PurchasedLead) => void;
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export function PurchasedLeadDetailsDialog({
    lead,
    open,
    onOpenChange,
    onReportIssue,
}: PurchasedLeadDetailsDialogProps) {
    if (!lead) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{lead.name ?? lead.id}</DialogTitle>
                    <DialogDescription>
                        {lead.type} · {lead.id} · Purchased {lead.purchased_at}
                    </DialogDescription>
                </DialogHeader>

                <dl className="grid gap-3 text-sm sm:grid-cols-2">
                    <Detail
                        label="City / state / ZIP"
                        value={`${lead.city}, ${lead.st} ${lead.zip}`}
                    />
                    <Detail label="Price" value={money(lead.price)} />
                    <Detail
                        label="Address"
                        value={lead.address ?? '—'}
                        className="sm:col-span-2"
                    />
                    <Detail label="Contact" value={lead.contact_name ?? '—'} />
                    <Detail label="Phone" value={lead.contact_phone ?? '—'} />
                    <Detail
                        label="Email"
                        value={lead.email ?? '—'}
                        className="sm:col-span-2"
                    />
                    <Detail
                        label="Payment"
                        value={
                            lead.card_brand && lead.card_last4
                                ? `Paid with ${lead.card_brand} ending ${lead.card_last4}`
                                : 'Paid'
                        }
                        className="sm:col-span-2"
                    />
                    <Detail
                        label="Credit used"
                        value={
                            lead.credit_used === null
                                ? 'No'
                                : money(lead.credit_used)
                        }
                    />
                    <Detail
                        label="Status"
                        value={
                            lead.status === 'issue_open' ? 'Issue open' : 'OK'
                        }
                    />
                </dl>

                <DialogFooter className="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        Close
                    </Button>
                    {lead.receipt_url ? (
                        <Button variant="outline" asChild>
                            <a
                                href={lead.receipt_url}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View receipt
                            </a>
                        </Button>
                    ) : null}
                    {lead.status === 'ok' && onReportIssue ? (
                        <Button
                            variant="outline"
                            onClick={() => {
                                onOpenChange(false);
                                onReportIssue(lead);
                            }}
                        >
                            Report issue
                        </Button>
                    ) : null}
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
