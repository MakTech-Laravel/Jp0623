import { Head, Link, router } from '@inertiajs/react';
import { Eye, Mail, Search, Trash2 } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { DataPagination } from '@/components/admin/data-pagination';
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
import contactMessages from '@/routes/admin/contact-messages';
import type { AdminContactMessage, Paginated } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

interface ContactMessagesIndexProps {
    messages: Paginated<AdminContactMessage>;
    filters: { search: string; status: string | null };
}

export default function ContactMessagesIndex({
    messages: paginated,
    filters,
}: ContactMessagesIndexProps) {
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
                contactMessages.index().url,
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
            <Head title="Contact messages" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Contact messages"
                    description="Inbound messages from the public contact form."
                    icon={Mail}
                />

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div className="relative w-full sm:max-w-xs">
                        <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search messages…"
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
                                    <TableHead>From</TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        Topic
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Status
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
                                    {paginated.data.map((message) => (
                                        <motion.tr
                                            key={message.id}
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
                                                    {message.name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {message.email}
                                                    {message.company
                                                        ? ` · ${message.company}`
                                                        : ''}
                                                </p>
                                            </TableCell>
                                            <TableCell className="hidden sm:table-cell">
                                                <p className="text-sm">
                                                    {message.topic}
                                                </p>
                                                <p className="mt-1 line-clamp-1 text-xs text-muted-foreground">
                                                    {message.message}
                                                </p>
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                <Badge
                                                    variant={
                                                        message.status === 'new'
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    {message.status === 'new'
                                                        ? 'New'
                                                        : 'Reviewed'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="hidden text-sm text-muted-foreground lg:table-cell">
                                                {new Date(
                                                    message.created_at,
                                                ).toLocaleString()}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    {can(
                                                        PERMISSIONS
                                                            .CONTACT_MESSAGES
                                                            .VIEW,
                                                    ) && (
                                                        <Button
                                                            asChild
                                                            variant="ghost"
                                                            size="icon"
                                                            aria-label="View message"
                                                        >
                                                            <Link
                                                                href={contactMessages.show(
                                                                    message.id,
                                                                ).url}
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can(
                                                        PERMISSIONS
                                                            .CONTACT_MESSAGES
                                                            .DELETE,
                                                    ) && (
                                                        <ConfirmDeleteDialog
                                                            description={
                                                                <>
                                                                    Delete
                                                                    message from{' '}
                                                                    <strong>
                                                                        {
                                                                            message.name
                                                                        }
                                                                    </strong>
                                                                    ?
                                                                </>
                                                            }
                                                            onConfirm={() =>
                                                                router.delete(
                                                                    contactMessages.destroy(
                                                                        message.id,
                                                                    ).url,
                                                                )
                                                            }
                                                        >
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="text-destructive"
                                                                aria-label="Delete message"
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
                    </div>

                    {paginated.data.length === 0 && (
                        <div className="flex flex-col items-center justify-center gap-2 px-6 py-16 text-center">
                            <Mail className="h-10 w-10 text-muted-foreground/50" />
                            <h3 className="text-sm font-medium text-muted-foreground">
                                No contact messages yet
                            </h3>
                        </div>
                    )}

                    <DataPagination meta={paginated} />
                </div>
            </div>
        </>
    );
}

ContactMessagesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Contact messages', href: contactMessages.index() },
    ],
};
