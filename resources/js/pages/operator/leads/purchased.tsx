import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { PurchasedLeadDetailsDialog } from '@/components/operator/purchased-lead-details-dialog';
import { PurchasedLeadIssueDialog } from '@/components/operator/purchased-lead-issue-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { purchased as leadsPurchased } from '@/routes/leads';
import type { PurchasedLead } from '@/types/operator-dashboard';

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export default function PurchasedLeads({
    purchasedLeads: initialLeads,
}: {
    purchasedLeads: PurchasedLead[];
    primaryCity: string;
}) {
    const [leads, setLeads] = useState(initialLeads);
    const [detailsLead, setDetailsLead] = useState<PurchasedLead | null>(null);
    const [issueLead, setIssueLead] = useState<PurchasedLead | null>(null);

    const openDetails = (lead: PurchasedLead) => {
        if (lead.payment_status === 'paid') {
            setDetailsLead(lead);
        }
    };

    const openIssue = (lead: PurchasedLead) => {
        setIssueLead(lead);
    };

    const markIssueOpen = (leadId: string) => {
        setLeads((current) =>
            current.map((lead) =>
                lead.id === leadId
                    ? { ...lead, status: 'issue_open' as const }
                    : lead,
            ),
        );
    };

    return (
        <>
            <Head title="Purchased location leads" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Purchased location leads
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Select a location to view full details. If something
                        went wrong, report an issue within 7 days.
                    </p>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Purchased</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>City / state / ZIP</TableHead>
                                    <TableHead>Address</TableHead>
                                    <TableHead>Contact</TableHead>
                                    <TableHead className="text-right">
                                        Price
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Credit used
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Action
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {leads.map((lead) => (
                                    <TableRow
                                        key={lead.id}
                                        className="cursor-pointer"
                                        onClick={() => openDetails(lead)}
                                    >
                                        <TableCell className="font-mono text-xs">
                                            {lead.purchased_at}
                                        </TableCell>
                                        <TableCell>
                                            <button
                                                type="button"
                                                className="text-left"
                                                onClick={(event) => {
                                                    event.stopPropagation();
                                                    openDetails(lead);
                                                }}
                                            >
                                                <p className="font-medium hover:underline">
                                                    {lead.name ??
                                                        'Details unlock after payment'}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {lead.type} · {lead.id}
                                                </p>
                                            </button>
                                        </TableCell>
                                        <TableCell>
                                            {[
                                                [lead.city, lead.st]
                                                    .filter(Boolean)
                                                    .join(', '),
                                                lead.zip,
                                            ]
                                                .filter(Boolean)
                                                .join(' ') || '—'}
                                        </TableCell>
                                        <TableCell>
                                            {lead.address ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <p>{lead.contact_name ?? '—'}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {lead.contact_phone}
                                            </p>
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {money(lead.price)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {lead.credit_used === null
                                                ? 'No'
                                                : money(lead.credit_used)}
                                        </TableCell>
                                        <TableCell
                                            className="text-right"
                                            onClick={(event) =>
                                                event.stopPropagation()
                                            }
                                        >
                                            {lead.payment_status ===
                                            'pending' ? (
                                                <Badge
                                                    variant="outline"
                                                    data-test="payment-processing-badge"
                                                    className="border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200"
                                                >
                                                    Payment processing
                                                </Badge>
                                            ) : lead.status === 'issue_open' ? (
                                                <Badge
                                                    variant="outline"
                                                    className="border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200"
                                                >
                                                    Issue open
                                                </Badge>
                                            ) : (
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        openIssue(lead)
                                                    }
                                                >
                                                    Report issue
                                                </Button>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <Button variant="outline" asChild>
                    <Link href={dashboard()}>Back to dashboard</Link>
                </Button>
            </div>

            <PurchasedLeadDetailsDialog
                lead={detailsLead}
                open={detailsLead !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setDetailsLead(null);
                    }
                }}
                onReportIssue={openIssue}
            />

            <PurchasedLeadIssueDialog
                lead={issueLead}
                open={issueLead !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setIssueLead(null);
                    }
                }}
                onSubmitted={markIssueOpen}
            />
        </>
    );
}

PurchasedLeads.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Purchased location leads', href: leadsPurchased() },
    ],
};
