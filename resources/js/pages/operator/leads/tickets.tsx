import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { tickets as leadsTickets } from '@/routes/leads';

interface Ticket {
    id: string;
    lead: string;
    issue: string;
    opened: string;
    status: string;
    outcome: string | null;
}

function statusBadge(status: string) {
    if (status === 'Resolved' || status === 'Refunded') {
        return (
            <Badge
                variant="outline"
                className="border-emerald-500/40 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200"
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

export default function LeadTickets({ tickets }: { tickets: Ticket[] }) {
    return (
        <>
            <Head title="Lead issues & tickets" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Lead issues &amp; tickets
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Track issues you&apos;ve reported and any refunds or
                        credits.
                    </p>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Ticket</TableHead>
                                    <TableHead>Lead</TableHead>
                                    <TableHead>Issue</TableHead>
                                    <TableHead>Opened</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Outcome</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {tickets.map((ticket) => (
                                    <TableRow key={ticket.id}>
                                        <TableCell className="font-mono text-xs">
                                            {ticket.id}
                                        </TableCell>
                                        <TableCell>{ticket.lead}</TableCell>
                                        <TableCell>{ticket.issue}</TableCell>
                                        <TableCell className="font-mono text-xs">
                                            {ticket.opened}
                                        </TableCell>
                                        <TableCell>
                                            {statusBadge(ticket.status)}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {ticket.outcome ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </>
    );
}

LeadTickets.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Lead issues & tickets', href: leadsTickets() },
    ],
};
