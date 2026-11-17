import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Star } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ReviewForm } from '@/components/admin/review-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import reviews from '@/routes/admin/reviews';
import type { AdminReview } from '@/types/admin';

export default function EditReview({ review }: { review: AdminReview }) {
    return (
        <>
            <Head title={`Edit review by ${review.role}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit review"
                    description="Update this homepage customer review."
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
                        action={reviews.update(review.id)}
                        isEdit
                        defaults={{
                            quote: review.quote,
                            role: review.role,
                            org: review.org,
                            rating: review.rating,
                            sort_order: review.sort_order,
                            is_active: review.is_active,
                        }}
                        onCancel={() => router.visit(reviews.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditReview.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Reviews', href: reviews.index() },
        { title: 'Edit', href: '#' },
    ],
};
