import { Head, Link, router } from '@inertiajs/react';
import {
    Eye,
    Package,
    Pencil,
    Plus,
    Search,
    ShoppingBasket,
    Trash2,
} from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
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
import products from '@/routes/admin/products';
import type { AdminProduct, Paginated } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

interface ProductsIndexProps {
    products: Paginated<AdminProduct>;
    filters: { search: string };
}

export default function ProductsIndex({
    products: paginated,
    filters,
}: ProductsIndexProps) {
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
                products.index().url,
                {
                    search: search || undefined,
                },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [search]);

    return (
        <>
            <Head title="Products" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Products"
                    description="Manage the snacks and beverage product catalog."
                    icon={ShoppingBasket}
                >
                    {can(PERMISSIONS.PRODUCTS.CREATE) && (
                        <Button asChild>
                            <Link href={products.create().url}>
                                <Plus className="h-4 w-4" /> Add product
                            </Link>
                        </Button>
                    )}
                </AdminPageHeader>

                <div className="relative w-full sm:max-w-xs">
                    <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search products…"
                        className="pl-9"
                    />
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Product</TableHead>
                                    <TableHead className="hidden sm:table-cell">
                                        Kind
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
                                    {paginated.data.map((product) => (
                                        <motion.tr
                                            key={product.id}
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
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted/40">
                                                        {product.image_url ? (
                                                            <img
                                                                src={
                                                                    product.image_url
                                                                }
                                                                alt={
                                                                    product.name
                                                                }
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <Package className="h-5 w-5 text-muted-foreground" />
                                                        )}
                                                    </div>
                                                    <div>
                                                        <p className="font-medium">
                                                            {product.name}
                                                        </p>
                                                        <p className="font-mono text-xs text-foreground/70">
                                                            {product.slug}
                                                        </p>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell className="hidden sm:table-cell">
                                                {product.kind}
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                <div className="flex flex-wrap gap-1">
                                                    <Badge
                                                        variant={
                                                            product.is_active
                                                                ? 'secondary'
                                                                : 'outline'
                                                        }
                                                    >
                                                        {product.is_active
                                                            ? 'Active'
                                                            : 'Inactive'}
                                                    </Badge>
                                                    {product.is_featured && (
                                                        <Badge variant="outline">
                                                            Featured
                                                        </Badge>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    {can(
                                                        PERMISSIONS.PRODUCTS
                                                            .VIEW,
                                                    ) && (
                                                        <Button
                                                            asChild
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Link
                                                                href={products.show(
                                                                    product.id,
                                                                ).url}
                                                                title="View"
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can(
                                                        PERMISSIONS.PRODUCTS
                                                            .EDIT,
                                                    ) && (
                                                        <Button
                                                            asChild
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Link
                                                                href={products.edit(
                                                                    product.id,
                                                                ).url}
                                                                title="Edit"
                                                            >
                                                                <Pencil className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can(
                                                        PERMISSIONS.PRODUCTS
                                                            .DELETE,
                                                    ) && (
                                                        <ConfirmDeleteDialog
                                                            description={
                                                                <>
                                                                    Delete{' '}
                                                                    <strong>
                                                                        {
                                                                            product.name
                                                                        }
                                                                    </strong>
                                                                    ? This
                                                                    cannot be
                                                                    undone.
                                                                </>
                                                            }
                                                            onConfirm={() =>
                                                                router.delete(
                                                                    products.destroy(
                                                                        product.id,
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
                                <ShoppingBasket className="mx-auto h-10 w-10 text-muted-foreground/40" />
                                <h3 className="mt-4 text-sm font-semibold">
                                    No products found
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

ProductsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Products', href: products.index() },
    ],
};
