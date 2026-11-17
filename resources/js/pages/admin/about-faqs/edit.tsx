import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { AboutFaqForm } from '@/components/admin/about-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import aboutFaqs from '@/routes/admin/about-faqs';
import type { AdminAboutFaq } from '@/types/admin';

export default function EditAboutFaq({ faq }: { faq: AdminAboutFaq }) {
    return (
        <>
            <Head title={`Edit ${faq.question}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit about FAQ"
                    description="Update this about-page FAQ item."
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
                        action={aboutFaqs.update(faq.id)}
                        isEdit
                        defaults={{
                            question: faq.question,
                            answer: faq.answer,
                            sort_order: faq.sort_order,
                            is_active: faq.is_active,
                        }}
                        onCancel={() => router.visit(aboutFaqs.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditAboutFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'About FAQs', href: aboutFaqs.index() },
        { title: 'Edit', href: '#' },
    ],
};
