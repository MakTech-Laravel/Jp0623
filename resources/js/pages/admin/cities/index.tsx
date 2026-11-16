import { Head, Link, router } from '@inertiajs/react';
import {
    Building2,
    Download,
    Pencil,
    Plus,
    Search,
    Trash2,
} from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { CityImportCard } from '@/components/admin/city-import-card';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { DataPagination } from '@/components/admin/data-pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import cities from '@/routes/admin/cities';
import type { AdminCity, Paginated } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

interface CitiesIndexProps {
    cities: Paginated<AdminCity>;
    filters: { search: string };
}

export default function CitiesIndex({
    cities: paginated,
    filters,
}: CitiesIndexProps) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const firstRender = useRef(true);

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                cities.index().url,
                { search: search || undefined },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [search]);

    return (
        <>
            <Head title="Cities" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Cities"
                    description="Cities shown in “Cities where we manage vending” on the home and cities pages."
                    icon={Building2}
                >
                    <div className="flex flex-wrap items-center gap-2">
                        {can(PERMISSIONS.CITIES.IMPORT) && (
                            <>
                                <Button variant="outline" asChild>
                                    <a
                                        href={
                                            cities.template({
                                                query: { format: 'csv' },
                                            }).url
                                        }
                                    >
                                        <Download className="h-4 w-4" /> CSV
                                        template
                                    </a>
                                </Button>
                                <Button variant="outline" asChild>
                                    <a
                                        href={
                                            cities.template({
                                                query: { format: 'xlsx' },
                                            }).url
                                        }
                                    >
                                        <Download className="h-4 w-4" /> Excel
                                        template
                                    </a>
                                </Button>
                            </>
                        )}
                        {can(PERMISSIONS.CITIES.CREATE) && (
                            <Button asChild>
                                <Link href={cities.create().url}>
                                    <Plus className="h-4 w-4" /> Add city
                                </Link>
                            </Button>
                        )}
                    </div>
                </AdminPageHeader>

                {can(PERMISSIONS.CITIES.IMPORT) && <CityImportCard />}

                <div className="relative w-full sm:max-w-xs">
                    <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search cities…"
                        className="pl-9"
                    />
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>City</TableHead>
                                    <TableHead>State</TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Order
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Status
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <AnimatePresence mode="popLayout">
                                    {paginated.data.map((item) => (
                                        <motion.tr
                                            key={item.id}
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
                                            <TableCell className="font-medium">
                                                {item.city}
                                            </TableCell>
                                            <TableCell>{item.state}</TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                {item.sort_order}
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                <Badge
                                                    variant={
                                                        item.is_active
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    {item.is_active
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    {can(
                                                        PERMISSIONS.CITIES.EDIT,
                                                    ) && (
                                                        <Button
                                                            asChild
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Link
                                                                href={
                                                                    cities.edit(
                                                                        item.id,
                                                                    ).url
                                                                }
                                                                title="Edit"
                                                            >
                                                                <Pencil className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can(
                                                        PERMISSIONS.CITIES
                                                            .DELETE,
                                                    ) && (
                                                        <ConfirmDeleteDialog
                                                            description={
                                                                <>
                                                                    Delete{' '}
                                                                    <strong>
                                                                        {
                                                                            item.city
                                                                        }
                                                                        ,{' '}
                                                                        {
                                                                            item.state
                                                                        }
                                                                    </strong>
                                                                    ? This
                                                                    cannot be
                                                                    undone.
                                                                </>
                                                            }
                                                            onConfirm={() =>
                                                                router.delete(
                                                                    cities.destroy(
                                                                        item.id,
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
                                <Building2 className="mx-auto h-10 w-10 text-muted-foreground/40" />
                                <h3 className="mt-4 text-sm font-semibold">
                                    No cities found
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

CitiesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Cities', href: cities.index() },
    ],
};
