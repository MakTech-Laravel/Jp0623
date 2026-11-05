import { Head, Link } from '@inertiajs/react';
import { Play } from 'lucide-react';
import { useState } from 'react';
import { LeadBuyDialog } from '@/components/operator/lead-buy-dialog';
import { TutorialDialog } from '@/components/operator/tutorial-dialog';
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
import type {
    OperatorDashboardProps,
    OperatorLead,
} from '@/types/operator-dashboard';

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export default function OperatorDashboard({
    firstName,
    primaryCity,
    kpis,
    newestLeads,
    activity,
}: OperatorDashboardProps) {
    const [selectedLead, setSelectedLead] = useState<OperatorLead | null>(null);
    const [tutorialOpen, setTutorialOpen] = useState(false);

    return (
        <>
            <Head title="Dashboard" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="font-display text-3xl font-extrabold tracking-tight">
                        Hi {firstName}, welcome back
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Here&apos;s what&apos;s happening with your leads,
                        routes and account.
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {kpis.map((kpi) => (
                        <div
                            key={kpi.label}
                            className="min-w-0 rounded-xl border bg-card p-4 shadow-sm"
                        >
                            <p className="text-sm text-muted-foreground">
                                {kpi.label}
                            </p>
                            <p className="font-display mt-1 text-2xl font-bold tabular-nums">
                                {kpi.value}
                            </p>
                            {kpi.hint && (
                                <p className="mt-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                    {kpi.hint}
                                </p>
                            )}
                        </div>
                    ))}
                </div>

                <div className="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
                    <div className="rounded-xl border bg-card p-5 shadow-sm">
                        <h2 className="mb-3 text-lg font-semibold">
                            Newest leads near {primaryCity}
                        </h2>
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Listed</TableHead>
                                        <TableHead>Location</TableHead>
                                        <TableHead>City</TableHead>
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
                                    {newestLeads.map((lead) => (
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
                        <p className="mt-3 text-sm">
                            <Link
                                href={leadsIndex()}
                                className="font-medium text-petrol underline-offset-4 hover:underline dark:text-brass"
                            >
                                See all location leads →
                            </Link>
                        </p>
                    </div>

                    <div className="space-y-4">
                        <div className="rounded-xl border bg-card p-5 shadow-sm">
                            <h2 className="mb-3 text-lg font-semibold">
                                How leads work
                            </h2>
                            <button
                                type="button"
                                onClick={() => setTutorialOpen(true)}
                                className="flex aspect-video w-full items-center justify-center rounded-xl bg-gradient-to-br from-petrol to-petrol-2 text-on-petrol transition hover:brightness-110"
                            >
                                <span className="inline-flex items-center gap-2 rounded-full bg-brass px-4 py-2 text-sm font-semibold text-brass-ink">
                                    <Play className="h-4 w-4" /> Watch tutorial
                                    (3:12)
                                </span>
                            </button>
                        </div>

                        <div className="rounded-xl border bg-card p-5 shadow-sm">
                            <h2 className="mb-3 text-lg font-semibold">
                                Recent activity
                            </h2>
                            <ul className="divide-y">
                                {activity.map((item) => (
                                    <li
                                        key={`${item.text}-${item.when}`}
                                        className="py-2.5 text-sm first:pt-0 last:pb-0"
                                    >
                                        {item.text}
                                        <span className="mt-0.5 block text-muted-foreground">
                                            {item.when}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
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
            <TutorialDialog
                open={tutorialOpen}
                onOpenChange={setTutorialOpen}
            />
        </>
    );
}

OperatorDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
