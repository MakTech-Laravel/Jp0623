import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Scale } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { LegalPageForm } from '@/components/admin/legal-page-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import legalPages from '@/routes/admin/legal-pages';
import type { AdminLegalPage } from '@/types/admin';

export default function EditLegalPage({
    legalPage,
}: {
    legalPage: AdminLegalPage;
}) {
    return (
        <>
            <Head title={`Edit ${legalPage.title}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title={`Edit ${legalPage.title}`}
                    description="Update the content shown on the public page."
                    icon={Scale}
                >
                    <Button variant="outline" asChild>
                        <Link href={legalPages.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to legal
                            pages
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <LegalPageForm
                        action={legalPages.update(legalPage.id)}
                        defaults={{
                            title: legalPage.title,
                            last_updated_on: legalPage.last_updated_on,
                            sections: legalPage.sections,
                        }}
                        onCancel={() => router.visit(legalPages.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditLegalPage.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Legal pages', href: legalPages.index() },
        { title: 'Edit', href: '#' },
    ],
};
