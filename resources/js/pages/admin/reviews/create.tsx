import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Star } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ReviewForm } from '@/components/admin/review-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import reviews from '@/routes/admin/reviews';

export default function CreateReview({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create review" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create review"
                    description="Add a customer review shown on the homepage."
                    icon={Star}
                >
                    <Button variant="outline" asChild>
                        <Link href={reviews.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to reviews
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <ReviewForm
                        action={reviews.store()}
                        defaults={{
                            quote: '',
                            role: '',
                            org: '',
                            rating: 5,
                            sort_order: defaultSortOrder,
                            is_active: true,
                        }}
                        onCancel={() => router.visit(reviews.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateReview.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Reviews', href: reviews.index() },
        { title: 'Create', href: reviews.create() },
    ],
};
