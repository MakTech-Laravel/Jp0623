import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { OfficeCoffeeFaqForm } from '@/components/admin/office-coffee-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import officeCoffeeFaqs from '@/routes/admin/office-coffee-faqs';

export default function CreateOfficeCoffeeFaq({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create office coffee FAQ" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create office coffee FAQ"
                    description="Add a question and answer for the office coffee FAQ section."
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
                        action={officeCoffeeFaqs.store()}
                        defaults={{
                            question: '',
                            answer: '',
                            sort_order: defaultSortOrder,
                            is_active: true,
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

CreateOfficeCoffeeFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Office coffee FAQs', href: officeCoffeeFaqs.index() },
        { title: 'Create', href: officeCoffeeFaqs.create() },
    ],
};
