import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Pencil, ShoppingBasket } from 'lucide-react';
import { motion } from 'motion/react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import products from '@/routes/admin/products';
import type { AdminProduct } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

export default function ShowProduct({ product }: { product: AdminProduct }) {
    const { can } = usePermission();

    return (
        <>
            <Head title={product.name} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader title="Product details" icon={ShoppingBasket}>
                    <Button variant="outline" asChild>
                        <Link href={products.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                    {can(PERMISSIONS.PRODUCTS.EDIT) && (
                        <Button asChild>
                            <Link href={products.edit(product.id).url}>
                                <Pencil className="h-4 w-4" /> Edit
                            </Link>
                        </Button>
                    )}
                </AdminPageHeader>

                <motion.div
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="grid gap-6 lg:grid-cols-3"
                >
                    <div className="overflow-hidden rounded-xl border bg-card shadow-sm lg:col-span-1">
                        <div className="flex aspect-square items-center justify-center bg-muted/40 p-6">
                            {product.image_url ? (
                                <img
                                    src={product.image_url}
                                    alt={product.name}
                                    className="max-h-full max-w-full object-contain"
                                />
                            ) : (
                                <ShoppingBasket className="h-16 w-16 text-muted-foreground/40" />
                            )}
                        </div>
                        <div className="space-y-2 border-t p-4">
                            <h2 className="text-lg font-bold">{product.name}</h2>
                            <p className="text-sm text-muted-foreground">
                                {product.slug}
                            </p>
                            <div className="flex flex-wrap gap-1">
                                <Badge
                                    variant={
                                        product.is_active
                                            ? 'secondary'
                                            : 'outline'
                                    }
                                >
                                    {product.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                                {product.is_featured && (
                                    <Badge variant="outline">Featured</Badge>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-xl border bg-card p-6 shadow-sm lg:col-span-2">
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <Detail label="Kind" value={product.kind} />
                            <Detail
                                label="Sort order"
                                value={String(product.sort_order)}
                            />
                            <div className="sm:col-span-2">
                                <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Tags
                                </dt>
                                <dd className="mt-2 flex flex-wrap gap-1">
                                    {(product.tags ?? []).length > 0 ? (
                                        (product.tags ?? []).map((tag) => (
                                            <Badge key={tag} variant="outline">
                                                {tag}
                                            </Badge>
                                        ))
                                    ) : (
                                        <span className="text-sm">—</span>
                                    )}
                                </dd>
                            </div>
                            <div className="sm:col-span-2">
                                <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Description
                                </dt>
                                <dd className="mt-1 text-sm leading-relaxed">
                                    {product.description ?? '—'}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </motion.div>
            </div>
        </>
    );
}

function Detail({
    label,
    value,
    className,
}: {
    label: string;
    value: string;
    className?: string;
}) {
    return (
        <div className={className}>
            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </dt>
            <dd className="mt-1 text-sm">{value}</dd>
        </div>
    );
}

ShowProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Products', href: products.index() },
        { title: 'Details', href: '#' },
    ],
};
