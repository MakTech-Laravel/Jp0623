import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { MachineFaqForm } from '@/components/admin/machine-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import machineFaqs from '@/routes/admin/machine-faqs';

export default function CreateMachineFaq({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create machine FAQ" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create machine FAQ"
                    description="Add a question and answer for the machines FAQ section."
                    icon={CircleHelp}
                >
                    <Button variant="outline" asChild>
                        <Link href={machineFaqs.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to FAQs
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <MachineFaqForm
                        action={machineFaqs.store()}
                        defaults={{
                            question: '',
                            answer: '',
                            sort_order: defaultSortOrder,
                            is_active: true,
                        }}
                        onCancel={() => router.visit(machineFaqs.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateMachineFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Machine FAQs', href: machineFaqs.index() },
        { title: 'Create', href: machineFaqs.create() },
    ],
};
