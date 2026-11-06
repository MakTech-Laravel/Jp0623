import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ShoppingBasket } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ProductForm } from '@/components/admin/product-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import products from '@/routes/admin/products';
import type { AdminProduct } from '@/types/admin';

export default function EditProduct({ product }: { product: AdminProduct }) {
    return (
        <>
            <Head title={`Edit ${product.name}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit product"
                    description={`Update ${product.name}.`}
                    icon={ShoppingBasket}
                >
                    <Button variant="outline" asChild>
                        <Link href={products.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to products
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <ProductForm
                        action={products.update(product.id)}
                        isEdit
                        currentImageUrl={product.image_url}
                        defaults={{
                            name: product.name,
                            slug: product.slug,
                            kind: product.kind,
                            tags: (product.tags ?? []).join(', '),
                            description: product.description ?? '',
                            is_featured: product.is_featured,
                            is_active: product.is_active,
                            sort_order: product.sort_order,
                        }}
                        onCancel={() => router.visit(products.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Products', href: products.index() },
        { title: 'Edit', href: '#' },
    ],
};
