import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Newspaper } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ArticleForm } from '@/components/admin/article-form';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import articles from '@/routes/admin/articles';
import type { AdminArticle } from '@/types/admin';

export default function EditArticle({ article }: { article: AdminArticle }) {
    return (
        <>
            <Head title={`Edit ${article.title}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Edit article"
                    description={`Update ${article.title}.`}
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
                        action={articles.update(article.id)}
                        isEdit
                        currentImageUrl={article.image_url}
                        defaults={{
                            category: article.category,
                            title: article.title,
                            slug: article.slug,
                            excerpt: article.excerpt ?? '',
                            body: article.body ?? '',
                            is_active: article.is_active,
                            sort_order: article.sort_order,
                        }}
                        onCancel={() => router.visit(articles.index().url)}
                    />
                </div>
            </div>
        </>
    );
}

EditArticle.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Articles', href: articles.index() },
        { title: 'Edit', href: '#' },
    ],
};
