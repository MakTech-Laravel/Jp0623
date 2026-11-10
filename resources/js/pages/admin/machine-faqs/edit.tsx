import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CircleHelp } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { MachineFaqForm } from '@/components/admin/machine-faq-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import machineFaqs from '@/routes/admin/machine-faqs';
import type { AdminMachineFaq } from '@/types/admin';

export default function EditMachineFaq({ faq }: { faq: AdminMachineFaq }) {
    return (
        <>
            <Head title={`Edit ${faq.question}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit machine FAQ"
                    description="Update this machines-page FAQ item."
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
                        action={machineFaqs.update(faq.id)}
                        isEdit
                        defaults={{
                            question: faq.question,
                            answer: faq.answer,
                            sort_order: faq.sort_order,
                            is_active: faq.is_active,
                        }}
                        onCancel={() => router.visit(machineFaqs.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditMachineFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Machine FAQs', href: machineFaqs.index() },
        { title: 'Edit', href: '#' },
    ],
};
