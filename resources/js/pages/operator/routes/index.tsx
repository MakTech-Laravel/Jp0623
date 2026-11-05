import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
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
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';
import { dashboard } from '@/routes';
import { index as routesIndex } from '@/routes/routes';

interface RouteListing {
    id: string;
    city: string;
    about: string;
    locs: number;
    machines: number;
    rev: number;
    ask: number;
    fin: string;
    listed: string;
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export default function RoutesIndex({ routes }: { routes: RouteListing[] }) {
    const [query, setQuery] = useState('');
    const [maxAsk, setMaxAsk] = useState('99999999');
    const [selected, setSelected] = useState<RouteListing | null>(null);
    const [message, setMessage] = useState(
        "I'm interested in this route. Please send more details.",
    );

    const filtered = useMemo(() => {
        const max = Number(maxAsk);
        const q = query.trim().toLowerCase();

        return routes.filter((route) => {
            if (route.ask > max) {
                return false;
            }

            if (!q) {
                return true;
            }

            return (
                route.city.toLowerCase().includes(q) ||
                route.about.toLowerCase().includes(q) ||
                route.id.toLowerCase().includes(q)
            );
        });
    }, [routes, query, maxAsk]);

    const handleInquiry = () => {
        toast.success('Inquiry sent. The route broker will contact you.');
        setSelected(null);
        setMessage("I'm interested in this route. Please send more details.");
    };

    return (
        <>
            <Head title="Routes for sale" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Routes for sale
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Existing vending routes listed by owners and route
                        brokers.
                    </p>
                </div>

                <div className="flex flex-wrap gap-4 rounded-xl border bg-card p-4 shadow-sm">
                    <div className="min-w-[200px] flex-1 space-y-2">
                        <Label htmlFor="route-search">Search</Label>
                        <Input
                            id="route-search"
                            placeholder="City or route type"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                        />
                    </div>
                    <div className="min-w-[180px] space-y-2">
                        <Label>Max asking price</Label>
                        <Select value={maxAsk} onValueChange={setMaxAsk}>
                            <SelectTrigger className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="99999999">Any</SelectItem>
                                <SelectItem value="100000">
                                    Under $100k
                                </SelectItem>
                                <SelectItem value="150000">
                                    Under $150k
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Listed</TableHead>
                                    <TableHead>Route located in</TableHead>
                                    <TableHead>About</TableHead>
                                    <TableHead className="text-right">
                                        Locations
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Annual revenue
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Asking
                                    </TableHead>
                                    <TableHead />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {filtered.map((route) => (
                                    <TableRow key={route.id}>
                                        <TableCell className="font-mono text-xs">
                                            {route.listed}
                                        </TableCell>
                                        <TableCell>{route.city}</TableCell>
                                        <TableCell>{route.about}</TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {route.locs}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {money(route.rev)}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {money(route.ask)}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    setSelected(route)
                                                }
                                            >
                                                Details
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>

            <Dialog
                open={selected !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelected(null);
                    }
                }}
            >
                <DialogContent className="sm:max-w-lg">
                    {selected ? (
                        <>
                            <DialogHeader>
                                <DialogTitle>{selected.about}</DialogTitle>
                                <DialogDescription>
                                    {selected.id} · {selected.city} · listed{' '}
                                    {selected.listed}
                                </DialogDescription>
                            </DialogHeader>

                            <dl className="grid gap-3 text-sm sm:grid-cols-2">
                                <Detail
                                    label="Locations"
                                    value={String(selected.locs)}
                                />
                                <Detail
                                    label="Machines"
                                    value={String(selected.machines)}
                                />
                                <Detail
                                    label="Annual revenue"
                                    value={money(selected.rev)}
                                />
                                <Detail
                                    label="Asking price"
                                    value={money(selected.ask)}
                                />
                                <Detail
                                    label="Seller financing"
                                    value={selected.fin}
                                />
                                <Detail label="Listed by" value="Route broker" />
                            </dl>

                            <div className="space-y-2">
                                <Label htmlFor="inquiry">
                                    Message to the broker
                                </Label>
                                <Textarea
                                    id="inquiry"
                                    rows={3}
                                    value={message}
                                    onChange={(event) =>
                                        setMessage(event.target.value)
                                    }
                                />
                            </div>

                            <DialogFooter>
                                <Button
                                    variant="outline"
                                    onClick={() => setSelected(null)}
                                >
                                    Close
                                </Button>
                                <Button
                                    className="bg-brass text-brass-ink hover:bg-brass/90"
                                    onClick={handleInquiry}
                                >
                                    Send inquiry
                                </Button>
                            </DialogFooter>
                        </>
                    ) : null}
                </DialogContent>
            </Dialog>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </dt>
            <dd className="mt-1">{value}</dd>
        </div>
    );
}

RoutesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Routes for sale', href: routesIndex() },
    ],
};
