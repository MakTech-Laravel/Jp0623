import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ShoppingBasket } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ProductForm } from '@/components/admin/product-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import products from '@/routes/admin/products';

export default function CreateProduct() {
    return (
        <>
            <Head title="Create product" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create product"
                    description="Add a product category card to the catalog."
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
                        action={products.store()}
                        onCancel={() => router.visit(products.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Products', href: products.index() },
        { title: 'Create', href: products.create() },
    ],
};
