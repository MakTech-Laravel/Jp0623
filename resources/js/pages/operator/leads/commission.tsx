import { Head } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { commission as leadsCommission } from '@/routes/leads';
import type { OperatorKpi } from '@/types/operator-dashboard';

interface SubmittedLead {
    submitted: string;
    location: string;
    listed_price: number | null;
    status: string;
}

interface CommissionRow {
    location: string;
    sold: string;
    sold_price: number;
    commission: number;
    status: string;
}

interface PayoutRow {
    paid_date: string;
    amount: number;
    paypal: string;
    notes: string;
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US', { minimumFractionDigits: value % 1 ? 2 : 0, maximumFractionDigits: 2 })}`;
}

function statusBadge(status: string) {
    if (status === 'Sold' || status === 'Paid') {
        return (
            <Badge
                variant="outline"
                className="border-emerald-500/40 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200"
            >
                {status}
            </Badge>
        );
    }

    if (status === 'Not approved') {
        return (
            <Badge
                variant="outline"
                className="border-destructive/40 bg-destructive/10 text-destructive"
            >
                {status}
            </Badge>
        );
    }

    return (
        <Badge
            variant="outline"
            className="border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200"
        >
            {status}
        </Badge>
    );
}

export default function LeadCommission({
    kpis,
    paypalEmail,
    submittedLeads,
    commissions,
    payouts,
}: {
    kpis: OperatorKpi[];
    paypalEmail: string;
    submittedLeads: SubmittedLead[];
    commissions: CommissionRow[];
    payouts: PayoutRow[];
}) {
    const [email, setEmail] = useState(paypalEmail);

    const handleSavePaypal = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        toast.success('PayPal email saved.');
    };

    return (
        <>
            <Head title="Commission earned" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Commission earned
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Commissions are paid monthly to the PayPal account
                        below. Check the email carefully: PayPal payments
                        can&apos;t be reversed.
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {kpis.map((kpi) => (
                        <div
                            key={kpi.label}
                            className="rounded-xl border bg-card p-4 shadow-sm"
                        >
                            <p className="text-sm text-muted-foreground">
                                {kpi.label}
                            </p>
                            <p className="mt-1 text-2xl font-bold tabular-nums">
                                {kpi.value}
                            </p>
                        </div>
                    ))}
                </div>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <form
                        onSubmit={handleSavePaypal}
                        className="flex flex-wrap items-end gap-3"
                    >
                        <div className="min-w-[240px] flex-1 space-y-2">
                            <Label htmlFor="paypal">PayPal email</Label>
                            <Input
                                id="paypal"
                                type="email"
                                required
                                value={email}
                                onChange={(event) =>
                                    setEmail(event.target.value)
                                }
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

                <Section title="Leads submitted">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/50 hover:bg-muted/50">
                                <TableHead>Submitted</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead className="text-right">
                                    Listed price
                                </TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {submittedLeads.map((row) => (
                                <TableRow
                                    key={`${row.location}-${row.submitted}`}
                                >
                                    <TableCell className="font-mono text-xs">
                                        {row.submitted}
                                    </TableCell>
                                    <TableCell>{row.location}</TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {row.listed_price === null
                                            ? '—'
                                            : money(row.listed_price)}
                                    </TableCell>
                                    <TableCell>
                                        {statusBadge(row.status)}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Section>

                <Section title="Commissions earned">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/50 hover:bg-muted/50">
                                <TableHead>Location</TableHead>
                                <TableHead>Sold</TableHead>
                                <TableHead className="text-right">
                                    Sold price
                                </TableHead>
                                <TableHead className="text-right">
                                    Commission (30%)
                                </TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {commissions.map((row) => (
                                <TableRow key={row.location}>
                                    <TableCell>{row.location}</TableCell>
                                    <TableCell className="font-mono text-xs">
                                        {row.sold}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {money(row.sold_price)}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {money(row.commission)}
                                    </TableCell>
                                    <TableCell>
                                        {statusBadge(row.status)}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Section>

                <Section title="Payout history">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/50 hover:bg-muted/50">
                                <TableHead>Paid date</TableHead>
                                <TableHead className="text-right">
                                    Amount
                                </TableHead>
                                <TableHead>PayPal</TableHead>
                                <TableHead>Notes</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {payouts.map((row) => (
                                <TableRow key={row.paid_date}>
                                    <TableCell className="font-mono text-xs">
                                        {row.paid_date}
                                    </TableCell>
                                    <TableCell className="text-right tabular-nums">
                                        {money(row.amount)}
                                    </TableCell>
                                    <TableCell>{row.paypal}</TableCell>
                                    <TableCell>{row.notes}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Section>
            </div>
        </>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
            <div className="border-b px-4 py-3">
                <h2 className="text-lg font-semibold">{title}</h2>
            </div>
            <div className="overflow-x-auto">{children}</div>
        </div>
    );
}

LeadCommission.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Commission earned', href: leadsCommission() },
    ],
};
