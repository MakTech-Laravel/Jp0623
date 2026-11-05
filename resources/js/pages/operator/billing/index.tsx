import { Head } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { index as billingIndex } from '@/routes/billing';

interface PaymentMethod {
    brand: string;
    last4: string;
    expires: string;
}

interface Invoice {
    id: string;
    location: string | null;
    date: string;
    status: string;
    description: string;
    amount: number;
    qty: number;
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function statusBadge(status: string) {
    if (status === 'Paid') {
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
        <Badge variant="outline" className="text-muted-foreground">
            {status}
        </Badge>
    );
}

export default function BillingIndex({
    paymentMethod,
    invoices,
}: {
    paymentMethod: PaymentMethod;
    invoices: Invoice[];
}) {
    const [cardOpen, setCardOpen] = useState(false);
    const [invoice, setInvoice] = useState<Invoice | null>(null);
    const [card, setCard] = useState(paymentMethod);

    const handleUpdateCard = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const form = new FormData(event.currentTarget);
        const last4 = String(form.get('number') ?? '').slice(-4) || card.last4;
        const expires = String(form.get('expires') ?? card.expires);

        setCard({
            brand: 'Visa',
            last4,
            expires,
        });
        toast.success('Payment method updated (demo).');
        setCardOpen(false);
    };

    return (
        <>
            <Head title="Billing & invoices" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Billing &amp; invoices
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Card on file: {card.brand} ending in {card.last4}{' '}
                        (example).
                    </p>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-card px-4 py-4 shadow-sm">
                    <p className="text-sm">
                        Payment method:{' '}
                        <strong>
                            {card.brand} •••• {card.last4}
                        </strong>
                        , expires {card.expires}
                    </p>
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={() => setCardOpen(true)}
                    >
                        Update payment method
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>ID</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead className="text-right">
                                        Amount
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Qty
                                    </TableHead>
                                    <TableHead />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="font-mono text-xs">
                                            {row.id}
                                        </TableCell>
                                        <TableCell>
                                            {row.location ?? '—'}
                                        </TableCell>
                                        <TableCell className="font-mono text-xs">
                                            {row.date}
                                        </TableCell>
                                        <TableCell>
                                            {statusBadge(row.status)}
                                        </TableCell>
                                        <TableCell>{row.description}</TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {money(row.amount)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {row.qty}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => setInvoice(row)}
                                            >
                                                View
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <Dialog open={cardOpen} onOpenChange={setCardOpen}>
                <DialogContent className="sm:max-w-md">
                    <form onSubmit={handleUpdateCard} className="space-y-4">
                        <DialogHeader>
                            <DialogTitle>Update payment method</DialogTitle>
                            <DialogDescription>
                                Demo only — no card is charged.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-2">
                            <Label htmlFor="card-number">Card number</Label>
                            <Input
                                id="card-number"
                                name="number"
                                required
                                placeholder="4242 4242 4242 4242"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label htmlFor="card-expires">Expires</Label>
                                <Input
                                    id="card-expires"
                                    name="expires"
                                    required
                                    placeholder="08/28"
                                    defaultValue={card.expires}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="card-cvc">CVC</Label>
                                <Input
                                    id="card-cvc"
                                    name="cvc"
                                    required
                                    placeholder="123"
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setCardOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                className="bg-brass text-brass-ink hover:bg-brass/90"
                            >
                                Save card
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={invoice !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setInvoice(null);
                    }
                }}
            >
                <DialogContent className="sm:max-w-md">
                    {invoice ? (
                        <>
                            <DialogHeader>
                                <DialogTitle>Invoice {invoice.id}</DialogTitle>
                                <DialogDescription>
                                    {invoice.date} · {invoice.status}
                                </DialogDescription>
                            </DialogHeader>
                            <dl className="space-y-3 text-sm">
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        Location
                                    </dt>
                                    <dd>{invoice.location ?? '—'}</dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        Description
                                    </dt>
                                    <dd className="text-right">
                                        {invoice.description}
                                    </dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        Amount
                                    </dt>
                                    <dd className="tabular-nums">
                                        {money(invoice.amount)}
                                    </dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt className="text-muted-foreground">
                                        Qty
                                    </dt>
                                    <dd>{invoice.qty}</dd>
                                </div>
                            </dl>
                            <DialogFooter>
                                <Button
                                    variant="outline"
                                    onClick={() => setInvoice(null)}
                                >
                                    Close
                                </Button>
                            </DialogFooter>
                        </>
                    ) : null}
                </DialogContent>
            </Dialog>
        </>
    );
}

BillingIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Billing & invoices', href: billingIndex() },
    ],
};
