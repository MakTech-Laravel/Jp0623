import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { HomeFaqForm } from '@/components/admin/home-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import homeFaqs from '@/routes/admin/home-faqs';

export default function CreateHomeFaq({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create home FAQ" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create home FAQ"
                    description="Add a question and answer for the homepage FAQ section."
                    icon={CircleHelp}
                >
                    <Button variant="outline" asChild>
                        <Link href={homeFaqs.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to FAQs
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <HomeFaqForm
                        action={homeFaqs.store()}
                        defaults={{
                            question: '',
                            answer: '',
                            sort_order: defaultSortOrder,
                            is_active: true,
                        }}
                        onCancel={() => router.visit(homeFaqs.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateHomeFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Home FAQs', href: homeFaqs.index() },
        { title: 'Create', href: homeFaqs.create() },
    ],
};
