import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Star, Trash2 } from 'lucide-react';
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
import reviewRoutes from '@/routes/admin/reviews';
import type { AdminReview, Paginated } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

interface ReviewsIndexProps {
    reviews: Paginated<AdminReview>;
    filters: { search: string };
}

export default function ReviewsIndex({
    reviews: paginated,
    filters,
}: ReviewsIndexProps) {
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
                reviewRoutes.index().url,
                { search: search || undefined },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 350);

        return () => clearTimeout(timeout);
    }, [search]);

    return (
        <>
            <Head title="Reviews" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Reviews"
                    description="Manage the customer reviews shown on the public homepage."
                    icon={Star}
                >
                    {can(PERMISSIONS.REVIEWS.CREATE) && (
                        <Button asChild>
                            <Link href={reviewRoutes.create().url}>
                                <Plus className="h-4 w-4" /> Add review
                            </Link>
                        </Button>
                    )}
                </AdminPageHeader>

                <div className="relative w-full sm:max-w-xs">
                    <Search className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search reviews…"
                        className="pl-9"
                    />
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Review</TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Rating
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Sort
                                    </TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <AnimatePresence mode="popLayout">
                                    {paginated.data.map((review) => (
                                        <motion.tr
                                            key={review.id}
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
                                                    {review.role}
                                                    <span className="font-normal text-muted-foreground">
                                                        {' '}
                                                        · {review.org}
                                                    </span>
                                                </p>
                                                <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                                    {review.quote}
                                                </p>
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                {'★'.repeat(review.rating)}
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                {review.sort_order}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        review.is_active
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    {review.is_active
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    {can(
                                                        PERMISSIONS.REVIEWS
                                                            .EDIT,
                                                    ) && (
                                                        <Button
                                                            asChild
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <Link
                                                                href={
                                                                    reviewRoutes.edit(
                                                                        review.id,
                                                                    ).url
                                                                }
                                                                title="Edit"
                                                            >
                                                                <Pencil className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can(
                                                        PERMISSIONS.REVIEWS
                                                            .DELETE,
                                                    ) && (
                                                        <ConfirmDeleteDialog
                                                            description={
                                                                <>
                                                                    Delete the
                                                                    review by{' '}
                                                                    <strong>
                                                                        {
                                                                            review.role
                                                                        }
                                                                    </strong>
                                                                    ?
                                                                </>
                                                            }
                                                            onConfirm={() =>
                                                                router.delete(
                                                                    reviewRoutes.destroy(
                                                                        review.id,
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
                                <Star className="mx-auto h-10 w-10 text-muted-foreground/40" />
                                <h3 className="mt-4 text-sm font-semibold">
                                    No reviews found
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

ReviewsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Reviews', href: reviewRoutes.index() },
    ],
};
