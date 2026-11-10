import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { OfficeCoffeeFaqForm } from '@/components/admin/office-coffee-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import officeCoffeeFaqs from '@/routes/admin/office-coffee-faqs';
import type { AdminOfficeCoffeeFaq } from '@/types/admin';

export default function EditOfficeCoffeeFaq({
    faq,
}: {
    faq: AdminOfficeCoffeeFaq;
}) {
    return (
        <>
            <Head title={`Edit ${faq.question}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit office coffee FAQ"
                    description="Update this office coffee FAQ item."
                    icon={CircleHelp}
                >
                    <Button variant="outline" asChild>
                        <Link href={officeCoffeeFaqs.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to FAQs
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <OfficeCoffeeFaqForm
                        action={officeCoffeeFaqs.update(faq.id)}
                        isEdit
                        defaults={{
                            question: faq.question,
                            answer: faq.answer,
                            sort_order: faq.sort_order,
                            is_active: faq.is_active,
                        }}
                        onCancel={() =>
                            router.visit(officeCoffeeFaqs.index().url)
                        }
                    />
                </div>
            </div>
        </>
    );
}

EditOfficeCoffeeFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Office coffee FAQs', href: officeCoffeeFaqs.index() },
        { title: 'Edit', href: '#' },
    ],
};
