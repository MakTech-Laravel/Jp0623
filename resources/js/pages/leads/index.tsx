import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { LeadBuyDialog } from '@/components/operator/lead-buy-dialog';
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
import { index as leadsIndex } from '@/routes/leads';
import type { OperatorLead } from '@/types/operator-dashboard';

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export default function LeadsIndex({
    leads,
    primaryCity,
}: {
    leads: OperatorLead[];
    primaryCity: string;
}) {
    const [selectedLead, setSelectedLead] = useState<OperatorLead | null>(null);

    return (
        <>
            <Head title="Location leads" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        All location leads
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Leads near {primaryCity}. Click a location to see
                        details and buy the lead.
                    </p>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Listed</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>City / ZIP</TableHead>
                                    <TableHead className="text-right">
                                        Distance
                                    </TableHead>
                                    <TableHead className="text-right">
                                        People
                                    </TableHead>
                                    <TableHead>Wants</TableHead>
                                    <TableHead className="text-right">
                                        Price
                                    </TableHead>
                                    <TableHead />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {leads.map((lead) => (
                                    <TableRow key={lead.id}>
                                        <TableCell className="font-mono text-xs">
                                            {lead.listed}
                                        </TableCell>
                                        <TableCell>
                                            <p className="font-medium">
                                                {lead.type}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {lead.id}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            {lead.city}, {lead.st}{' '}
                                            {lead.zip.slice(0, 3)}xx
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {lead.dist} mi
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {lead.people}
                                        </TableCell>
                                        <TableCell>{lead.wants}</TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {money(lead.price)}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                size="sm"
                                                className="bg-brass text-brass-ink hover:bg-brass/90"
                                                onClick={() =>
                                                    setSelectedLead(lead)
                                                }
                                            >
                                                View &amp; buy
                                            </Button>
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

            <LeadBuyDialog
                lead={selectedLead}
                open={selectedLead !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedLead(null);
                    }
                }}
            />
        </>
    );
}

LeadsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Location leads', href: leadsIndex() },
    ],
};
