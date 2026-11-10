import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { HomeFaqForm } from '@/components/admin/home-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import homeFaqs from '@/routes/admin/home-faqs';
import type { AdminHomeFaq } from '@/types/admin';

export default function EditHomeFaq({ faq }: { faq: AdminHomeFaq }) {
    return (
        <>
            <Head title={`Edit ${faq.question}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit home FAQ"
                    description="Update this homepage FAQ item."
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
                        action={homeFaqs.update(faq.id)}
                        isEdit
                        defaults={{
                            question: faq.question,
                            answer: faq.answer,
                            sort_order: faq.sort_order,
                            is_active: faq.is_active,
                        }}
                        onCancel={() => router.visit(homeFaqs.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditHomeFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Home FAQs', href: homeFaqs.index() },
        { title: 'Edit', href: '#' },
    ],
};
