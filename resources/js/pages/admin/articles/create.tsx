import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Newspaper } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ArticleForm } from '@/components/admin/article-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import articles from '@/routes/admin/articles';

export default function CreateArticle({
    defaultSortOrder,
}: {
    defaultSortOrder: number;
}) {
    return (
        <>
            <Head title="Create article" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Create article"
                    description="Add an article to the resource hub."
                    icon={Newspaper}
                >
                    <Button variant="outline" asChild>
                        <Link href={articles.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back to articles
                        </Link>
                    </Button>
                </AdminPageHeader>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <ArticleForm
                        action={articles.store()}
                        defaultSortOrder={defaultSortOrder}
                        onCancel={() => router.visit(articles.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

CreateArticle.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Articles', href: articles.index() },
        { title: 'Create', href: articles.create() },
    ],
};
