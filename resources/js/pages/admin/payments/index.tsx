import { Head, router } from '@inertiajs/react';
import { Receipt, Search } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { DataPagination } from '@/components/admin/data-pagination';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import payments from '@/routes/admin/payments';
import type { Paginated } from '@/types/admin';

interface AdminPayment {
    id: string;
    invoice_id: string;
    source: 'lead' | 'membership';
    source_label: string;
    operator_name: string | null;
    operator_email: string | null;
    lead_public_id: string;
    lead_name: string | null;
    lead_source: string;
    amount: number;
    currency: string;
    status: string;
    payment_provider: string;
    card_brand: string | null;
    card_last4: string | null;
    square_payment_id: string | null;
    receipt_url: string | null;
    paid_at: string | null;
    paid_at_label: string | null;
}

interface PaymentsIndexProps {
    payments: Paginated<AdminPayment>;
    filters: {
        search: string;
        status: string | null;
        source: string | null;
    };
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function statusBadge(status: string) {
    if (status === 'paid') {
        return (
            <Badge
                variant="outline"
                className="border-emerald-500/40 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200"
            >
                Paid
            </Badge>
        );
    }

    if (status === 'pending') {
        return (
            <Badge
                variant="outline"
                className="border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200"
            >
                Pending
            </Badge>
        );
    }

    if (status === 'failed') {
        return (
            <Badge
                variant="outline"
                className="border-destructive/40 bg-destructive/10 text-destructive"
            >
                Failed
            </Badge>
        );
    }

    return (
        <Badge variant="outline" className="capitalize text-muted-foreground">
            {status}
        </Badge>
    );
}

export default function PaymentsIndex({
    payments: paginated,
    filters,
}: PaymentsIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [source, setSource] = useState(filters.source ?? 'all');
    const firstRender = useRef(true);

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                payments.index().url,
                {
                    search: search || undefined,
                    status: status === 'all' ? undefined : status,
                    source: source === 'all' ? undefined : source,
                },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [search, status, source]);

    return (
        <>
            <Head title="Payments" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Payments"
                    description="Lead purchases and membership charges from Square."
                    icon={Receipt}
                />

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div className="relative w-full sm:max-w-xs">
                        <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search LP-/MB-id, email, payment id…"
                            className="pl-9"
                        />
                    </div>

                    <Select value={source} onValueChange={setSource}>
                        <SelectTrigger className="w-full sm:w-44">
                            <SelectValue placeholder="All sources" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All sources</SelectItem>
                            <SelectItem value="lead">Lead purchase</SelectItem>
                            <SelectItem value="membership">
                                Membership
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select value={status} onValueChange={setStatus}>
                        <SelectTrigger className="w-full sm:w-44">
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem value="paid">Paid</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                            <SelectItem value="void">Void</SelectItem>
                            <SelectItem value="refunded">Refunded</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Invoice</TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        Source
                                    </TableHead>
                                    <TableHead>Operator</TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Detail
                                    </TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Amount
                                    </TableHead>
                                    <TableHead className="hidden lg:table-cell">
                                        Card
                                    </TableHead>
                                    <TableHead className="hidden xl:table-cell">
                                        Paid
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Receipt
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <AnimatePresence mode="popLayout">
                                    {paginated.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={9}
                                                className="py-10 text-center text-muted-foreground"
                                            >
                                                No payments found.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        paginated.data.map((payment) => (
                                            <motion.tr
                                                key={payment.id}
                                                layout
                                                initial={{ opacity: 0, y: 6 }}
                                                animate={{ opacity: 1, y: 0 }}
                                                exit={{ opacity: 0 }}
                                                transition={{
                                                    type: 'spring',
                                                    stiffness: 350,
                                                    damping: 28,
                                                }}
                                                className="border-b transition-colors hover:bg-muted/30"
                                            >
                                                <TableCell>
                                                    <p className="font-mono text-xs font-medium">
                                                        {payment.invoice_id}
                                                    </p>
                                                    {payment.square_payment_id ? (
                                                        <p className="mt-0.5 max-w-[12rem] truncate font-mono text-[11px] text-muted-foreground">
                                                            {
                                                                payment.square_payment_id
                                                            }
                                                        </p>
                                                    ) : (
                                                        <p className="mt-0.5 text-[11px] capitalize text-muted-foreground">
                                                            {
                                                                payment.payment_provider
                                                            }
                                                        </p>
                                                    )}
                                                </TableCell>
                                                <TableCell className="hidden sm:table-cell">
                                                    <Badge variant="outline">
                                                        {payment.source_label}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <p className="text-sm font-medium">
                                                        {payment.operator_name ??
                                                            '—'}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {payment.operator_email ??
                                                            '—'}
                                                    </p>
                                                </TableCell>
                                                <TableCell className="hidden md:table-cell">
                                                    <p className="text-sm">
                                                        {payment.lead_name ??
                                                            '—'}
                                                    </p>
                                                    {payment.source ===
                                                    'lead' ? (
                                                        <p className="text-xs text-muted-foreground">
                                                            {
                                                                payment.lead_public_id
                                                            }{' '}
                                                            ·{' '}
                                                            {
                                                                payment.lead_source
                                                            }
                                                        </p>
                                                    ) : (
                                                        <p className="text-xs text-muted-foreground">
                                                            Membership charge
                                                        </p>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {statusBadge(
                                                        payment.status,
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right tabular-nums">
                                                    {money(payment.amount)}
                                                </TableCell>
                                                <TableCell className="hidden text-sm lg:table-cell">
                                                    {payment.card_brand &&
                                                    payment.card_last4
                                                        ? `${payment.card_brand} ${payment.card_last4}`
                                                        : '—'}
                                                </TableCell>
                                                <TableCell className="hidden font-mono text-xs xl:table-cell">
                                                    {payment.paid_at_label ??
                                                        '—'}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    {payment.receipt_url ? (
                                                        <a
                                                            href={
                                                                payment.receipt_url
                                                            }
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="text-sm text-brass underline-offset-2 hover:underline"
                                                        >
                                                            View
                                                        </a>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">
                                                            —
                                                        </span>
                                                    )}
                                                </TableCell>
                                            </motion.tr>
                                        ))
                                    )}
                                </AnimatePresence>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <DataPagination meta={paginated} />
            </div>
        </>
    );
}

PaymentsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Payments', href: payments.index() },
    ],
};
