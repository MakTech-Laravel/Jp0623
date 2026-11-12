import { Head, router, useForm } from '@inertiajs/react';
import { Search, Send } from 'lucide-react';
import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { OperatorSmsTabs } from '@/components/admin/operator-sms-tabs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { usePermission } from '@/hooks/use-permissions';
import operatorSms from '@/routes/admin/operator-sms';
import { PERMISSIONS } from '@/types/permissions';

interface OperatorRow {
    id: number;
    user_id: number;
    name: string;
    email: string | null;
    company_name: string;
    phone: string;
    city: string | null;
    state: string | null;
    primary_city: string | null;
    latitude: number | null;
    longitude: number | null;
    distance_miles: number | null;
}

interface ReferenceOption {
    key: string;
    label: string;
    type: string;
    id: number;
    latitude: number;
    longitude: number;
    business_name: string;
    location: string;
}

interface RangeOption {
    value: string;
    label: string;
}

interface Filters {
    search: string;
    range: string;
    min_miles: number | null;
    max_miles: number | null;
    latitude: number | null;
    longitude: number | null;
    reference_key: string;
}

interface ManualTemplate {
    id: number;
    name: string;
    body: string;
}

interface OperatorSmsIndexProps {
    operators: OperatorRow[];
    total: number;
    truncated: boolean;
    referenceOptions: ReferenceOption[];
    rangeOptions: RangeOption[];
    manualTemplates: ManualTemplate[];
    twilioEnabled: boolean;
    filters: Filters;
}

export default function OperatorSmsIndex({
    operators,
    total,
    truncated,
    referenceOptions,
    rangeOptions,
    manualTemplates,
    twilioEnabled,
    filters,
}: OperatorSmsIndexProps) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const [range, setRange] = useState(filters.range ?? 'all');
    const [minMiles, setMinMiles] = useState(
        filters.min_miles !== null && filters.min_miles !== undefined
            ? String(filters.min_miles)
            : '',
    );
    const [maxMiles, setMaxMiles] = useState(
        filters.max_miles !== null && filters.max_miles !== undefined
            ? String(filters.max_miles)
            : '',
    );
    const [referenceKey, setReferenceKey] = useState(
        filters.reference_key || 'none',
    );
    const [selected, setSelected] = useState<Set<number>>(new Set());
    const [selectedTemplateId, setSelectedTemplateId] = useState('custom');
    const firstRender = useRef(true);

    const selectedReference = useMemo(
        () => referenceOptions.find((option) => option.key === referenceKey),
        [referenceKey, referenceOptions],
    );

    const form = useForm({
        operator_ids: [] as number[],
        message: '',
    });

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            const hasReference = referenceKey !== 'none';
            const latitude = hasReference
                ? (selectedReference?.latitude ?? filters.latitude)
                : undefined;
            const longitude = hasReference
                ? (selectedReference?.longitude ?? filters.longitude)
                : undefined;

            router.get(
                operatorSms.index().url,
                {
                    search: search || undefined,
                    range: hasReference && range !== 'all' ? range : undefined,
                    min_miles:
                        hasReference && range === 'custom' && minMiles !== ''
                            ? Number(minMiles)
                            : undefined,
                    max_miles:
                        hasReference && range === 'custom' && maxMiles !== ''
                            ? Number(maxMiles)
                            : undefined,
                    latitude: latitude ?? undefined,
                    longitude: longitude ?? undefined,
                    reference_key: hasReference ? referenceKey : undefined,
                },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [
        search,
        range,
        minMiles,
        maxMiles,
        referenceKey,
        selectedReference,
        filters.latitude,
        filters.longitude,
    ]);

    useEffect(() => {
        setSelected(new Set());
    }, [operators]);

    const allSelected =
        operators.length > 0 && operators.every((row) => selected.has(row.id));

    const toggleAll = (checked: boolean) => {
        if (!checked) {
            setSelected(new Set());

            return;
        }

        setSelected(new Set(operators.map((row) => row.id)));
    };

    const toggleOne = (id: number, checked: boolean) => {
        setSelected((current) => {
            const next = new Set(current);

            if (checked) {
                next.add(id);
            } else {
                next.delete(id);
            }

            return next;
        });
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            operator_ids: Array.from(selected),
        }));

        form.post(operatorSms.send().url, {
            preserveScroll: true,
            onSuccess: () => {
                form.setData('message', '');
                setSelected(new Set());
            },
        });
    };

    const hasCenter =
        referenceKey !== 'none' &&
        (selectedReference?.latitude ?? filters.latitude) != null &&
        (selectedReference?.longitude ?? filters.longitude) != null;

    return (
        <>
            <Head title="Operator SMS" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Operator SMS"
                    description="Filter operators by distance from a lead, search by name/email, then send a manual SMS."
                    icon={Send}
                />

                <OperatorSmsTabs active="send" />

                {!twilioEnabled && (
                    <div className="rounded-xl border border-amber-300/60 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-100">
                        Twilio is disabled or incomplete in `.env`. Messages
                        will queue but will not send until credentials are set.
                    </div>
                )}

                <div className="rounded-xl border border-sky-300/50 bg-sky-50 px-4 py-3 text-sm text-sky-950 dark:border-sky-500/30 dark:bg-sky-950/30 dark:text-sky-100">
                    Step 1: choose a <strong>Reference location (lead)</strong>.
                    Step 2: Distance range unlocks and filters operators by
                    miles from that lead.
                </div>

                <div className="grid gap-3 rounded-xl border bg-card p-4 shadow-sm lg:grid-cols-4">
                    <div className="space-y-2 lg:col-span-2">
                        <Label>Reference location (lead)</Label>
                        <Select
                            value={referenceKey}
                            onValueChange={(value) => {
                                setReferenceKey(value);
                                if (value === 'none') {
                                    setRange('all');
                                }
                            }}
                        >
                            <SelectTrigger className="border-primary/40">
                                <SelectValue placeholder="Select a lead location" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">
                                    No reference (search only)
                                </SelectItem>
                                {referenceOptions.map((option) => (
                                    <SelectItem
                                        key={option.key}
                                        value={option.key}
                                    >
                                        {option.label}
                                        {option.location
                                            ? ` · ${option.location}`
                                            : ''}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {referenceOptions.length === 0 && (
                            <p className="text-xs text-destructive">
                                No leads with coordinates found. Submit a
                                vending/coffee request with an address first.
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label>
                            Distance range
                            {!hasCenter ? ' (locked)' : ''}
                        </Label>
                        <Select
                            value={range}
                            onValueChange={setRange}
                            disabled={!hasCenter}
                        >
                            <SelectTrigger
                                className={
                                    hasCenter
                                        ? 'border-primary/40'
                                        : 'opacity-70'
                                }
                            >
                                <SelectValue placeholder="All distances" />
                            </SelectTrigger>
                            <SelectContent>
                                {rangeOptions.map((option) => (
                                    <SelectItem
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {!hasCenter && (
                            <p className="text-xs text-muted-foreground">
                                Select a reference lead above to unlock this.
                            </p>
                        )}
                    </div>

                    <div className="relative space-y-2">
                        <Label>Search name / email</Label>
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Name, email, company…"
                                className="pl-9"
                            />
                        </div>
                    </div>

                    {range === 'custom' && hasCenter && (
                        <>
                            <div className="space-y-2">
                                <Label>Min miles</Label>
                                <Input
                                    type="number"
                                    min={0}
                                    value={minMiles}
                                    onChange={(e) =>
                                        setMinMiles(e.target.value)
                                    }
                                    placeholder="e.g. 100"
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Max miles</Label>
                                <Input
                                    type="number"
                                    min={0}
                                    value={maxMiles}
                                    onChange={(e) =>
                                        setMaxMiles(e.target.value)
                                    }
                                    placeholder="e.g. 200"
                                />
                            </div>
                        </>
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                    <span>
                        Showing {operators.length} of {total} operators
                    </span>
                    {truncated && (
                        <Badge variant="secondary">
                            Capped at first 500 matches
                        </Badge>
                    )}
                    {!hasCenter && (
                        <Badge variant="outline">
                            Pick a lead to enable mile ranges
                        </Badge>
                    )}
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead className="w-12">
                                        <Checkbox
                                            checked={allSelected}
                                            onCheckedChange={(value) =>
                                                toggleAll(value === true)
                                            }
                                            aria-label="Select all operators"
                                        />
                                    </TableHead>
                                    <TableHead>Operator</TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Email
                                    </TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        Phone
                                    </TableHead>
                                    <TableHead className="hidden lg:table-cell">
                                        Location
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Miles
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {operators.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No operators match these filters.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    operators.map((operator) => (
                                        <TableRow key={operator.id}>
                                            <TableCell>
                                                <Checkbox
                                                    checked={selected.has(
                                                        operator.id,
                                                    )}
                                                    onCheckedChange={(value) =>
                                                        toggleOne(
                                                            operator.id,
                                                            value === true,
                                                        )
                                                    }
                                                    aria-label={`Select ${operator.name}`}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <p className="font-medium">
                                                    {operator.name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {operator.company_name}
                                                </p>
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                {operator.email ?? '—'}
                                            </TableCell>
                                            <TableCell className="hidden sm:table-cell">
                                                {operator.phone}
                                            </TableCell>
                                            <TableCell className="hidden lg:table-cell">
                                                {operator.city && operator.state
                                                    ? `${operator.city}, ${operator.state}`
                                                    : (operator.primary_city ??
                                                      '—')}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {operator.distance_miles !==
                                                null
                                                    ? operator.distance_miles
                                                    : '—'}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>

                {can(PERMISSIONS.OPERATOR_SMS.SEND) && (
                    <form
                        onSubmit={submit}
                        className="space-y-4 rounded-xl border bg-card p-4 shadow-sm"
                    >
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 className="font-medium">Compose SMS</h2>
                                <p className="text-sm text-muted-foreground">
                                    {selected.size} selected
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    toggleAll(
                                        !allSelected && operators.length > 0,
                                    )
                                }
                            >
                                {allSelected
                                    ? 'Clear selection'
                                    : 'Select all shown'}
                            </Button>
                        </div>

                        <div className="space-y-2">
                            <Label>Ready-made template</Label>
                            <Select
                                value={selectedTemplateId}
                                onValueChange={(value) => {
                                    setSelectedTemplateId(value);
                                    if (value === 'custom') {
                                        return;
                                    }

                                    const template = manualTemplates.find(
                                        (item) => String(item.id) === value,
                                    );

                                    if (template) {
                                        form.setData('message', template.body);
                                    }
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose a template" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="custom">
                                        Custom message
                                    </SelectItem>
                                    {manualTemplates.map((template) => (
                                        <SelectItem
                                            key={template.id}
                                            value={String(template.id)}
                                        >
                                            {template.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {manualTemplates.length === 0 && (
                                <p className="text-xs text-muted-foreground">
                                    No manual templates yet. Add some on the SMS
                                    templates tab.
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="sms-message">Message</Label>
                            <Textarea
                                id="sms-message"
                                value={form.data.message}
                                onChange={(e) => {
                                    setSelectedTemplateId('custom');
                                    form.setData('message', e.target.value);
                                }}
                                rows={4}
                                maxLength={1600}
                                placeholder="Write the SMS operators will receive…"
                            />
                            {form.errors.message && (
                                <p className="text-sm text-destructive">
                                    {form.errors.message}
                                </p>
                            )}
                            {form.errors.operator_ids && (
                                <p className="text-sm text-destructive">
                                    {form.errors.operator_ids}
                                </p>
                            )}
                        </div>

                        <Button
                            type="submit"
                            disabled={
                                form.processing ||
                                selected.size === 0 ||
                                form.data.message.trim() === ''
                            }
                        >
                            <Send className="h-4 w-4" />
                            Send to {selected.size || 0} operator
                            {selected.size === 1 ? '' : 's'}
                        </Button>
                    </form>
                )}
            </div>
        </>
    );
}
