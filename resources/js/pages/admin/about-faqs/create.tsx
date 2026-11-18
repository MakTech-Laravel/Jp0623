import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { AboutFaqForm } from '@/components/admin/about-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import aboutFaqs from '@/routes/admin/about-faqs';

export default function CreateAboutFaq({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create about FAQ" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create about FAQ"
                    description="Add a question and answer for the About page FAQ section."
                    icon={CircleHelp}
                >
                    <Button variant="outline" asChild>
                        <Link href={aboutFaqs.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to FAQs
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <AboutFaqForm
                        action={aboutFaqs.store()}
                        defaults={{
                            question: '',
                            answer: '',
                            sort_order: defaultSortOrder,
                            is_active: true,
                        }}
                        onCancel={() => router.visit(aboutFaqs.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateAboutFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'About FAQs', href: aboutFaqs.index() },
        { title: 'Create', href: aboutFaqs.create() },
    ],
};
