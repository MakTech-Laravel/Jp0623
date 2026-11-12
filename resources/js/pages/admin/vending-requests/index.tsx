import { Head, Link, router } from '@inertiajs/react';
import { Eye, Inbox, Search, Trash2 } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { DataPagination } from '@/components/admin/data-pagination';
import {
    OPERATOR_SMS_COLUMN_HINT,
    OPERATOR_SMS_COLUMN_LABEL,
    OperatorSmsStatusBadge,
} from '@/components/admin/operator-sms-status-badge';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import vendingRequests from '@/routes/admin/vending-requests';
import type { AdminVendingRequest, Paginated } from '@/types/admin';
import { formatRequestLocation } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

interface VendingRequestsIndexProps {
    requests: Paginated<AdminVendingRequest>;
    filters: { search: string; status: string | null };
}

export default function VendingRequestsIndex({
    requests: paginated,
    filters,
}: VendingRequestsIndexProps) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const firstRender = useRef(true);

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                vendingRequests.index().url,
                {
                    search: search || undefined,
                    status: status === 'all' ? undefined : status,
                },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [search, status]);

    return (
        <>
            <Head title="Vending requests" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Vending requests"
                    description="Inbound free vending requests from the public site."
                    icon={Inbox}
                />

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div className="relative w-full sm:max-w-xs">
                        <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search requests…"
                            className="pl-9"
                        />
                    </div>

                    <Select value={status} onValueChange={setStatus}>
                        <SelectTrigger className="w-full sm:w-44">
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem value="new">New</SelectItem>
                            <SelectItem value="reviewed">Reviewed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Business</TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        Contact
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Status
                                    </TableHead>
                                    <TableHead
                                        className="hidden md:table-cell"
                                        title={OPERATOR_SMS_COLUMN_HINT}
                                    >
                                        {OPERATOR_SMS_COLUMN_LABEL}
                                    </TableHead>
                                    <TableHead className="hidden lg:table-cell">
                                        Submitted
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <AnimatePresence mode="popLayout">
                                    {paginated.data.map((request) => (
                                        <motion.tr
                                            key={request.id}
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
                                                <p className="font-medium">
                                                    {request.business_name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {request.location_type} ·{' '}
                                                    {formatRequestLocation(
                                                        request,
                                                    )}
                                                </p>
                                            </TableCell>
                                            <TableCell className="hidden sm:table-cell">
                                                <p className="text-sm">
                                                    {request.contact_name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {request.email}
                                                </p>
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                <Badge
                                                    variant={
                                                        request.status === 'new'
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    {request.status === 'new'
                                                        ? 'New'
                                                        : 'Reviewed'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                <OperatorSmsStatusBadge
                                                    status={
                                                        request.operator_sms_status
                                                    }
                                                    note={
                                                        request.operator_sms_note
                                                    }
                                                    providerStatus={
                                                        request.operator_sms_provider_status
                                                    }
                                                    sentCount={
                                                        request.operator_sms_sent_count
                                                    }
                                                />
                                            </TableCell>
                                            <TableCell className="hidden text-sm text-muted-foreground lg:table-cell">
                                                {new Date(
                                                    request.created_at,
                                                ).toLocaleString()}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    {can(
                                                        PERMISSIONS
                                                            .VENDING_REQUESTS
                                                            .VIEW,
                                                    ) && (
                                                        <Button
                                                            asChild
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Link
                                                                href={
                                                                    vendingRequests.show(
                                                                        request.id,
                                                                    ).url
                                                                }
                                                                title="View"
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can(
                                                        PERMISSIONS
                                                            .VENDING_REQUESTS
                                                            .DELETE,
                                                    ) && (
                                                        <ConfirmDeleteDialog
                                                            description={
                                                                <>
                                                                    Delete
                                                                    request from{' '}
                                                                    <strong>
                                                                        {
                                                                            request.business_name
                                                                        }
                                                                    </strong>
                                                                    ? This
                                                                    cannot be
                                                                    undone.
                                                                </>
                                                            }
                                                            onConfirm={() =>
                                                                router.delete(
                                                                    vendingRequests.destroy(
                                                                        request.id,
                                                                    ).url,
                                                                    {
                                                                        preserveScroll: true,
                                                                    },
                                                                )
                                                            }
                                                        >
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="text-muted-foreground hover:text-destructive"
                                                                title="Delete"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </ConfirmDeleteDialog>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </motion.tr>
                                    ))}
                                </AnimatePresence>
                            </TableBody>
                        </Table>

                        {paginated.data.length === 0 && (
                            <div className="px-4 py-16 text-center">
                                <Inbox className="mx-auto h-10 w-10 text-muted-foreground/40" />
                                <h3 className="mt-4 text-sm font-semibold">
                                    No requests found
                                </h3>
                            </div>
                        )}
                    </div>

                    <DataPagination meta={paginated} />
                </div>
            </div>
        </>
    );
}

VendingRequestsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Vending requests', href: vendingRequests.index() },
    ],
};
