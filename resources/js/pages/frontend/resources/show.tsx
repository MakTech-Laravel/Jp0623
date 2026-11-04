import { Head } from '@inertiajs/react';
import { ArticleBodySection } from '@/components/resources/article-body-section';
import { RelatedSection } from '@/components/resources/related-section';
import type { ArticleShowPageProps } from '@/types/frontend';

export default function ArticleShow({
    crumb,
    article,
    cta,
    related,
}: ArticleShowPageProps) {
    return (
        <>
            <Head title={article.title}>
                <meta
                    head-key="description"
                    name="description"
                    content={article.excerpt}
                />
            </Head>
            <ArticleBodySection crumb={crumb} article={article} cta={cta} />
            <RelatedSection items={related} />
        </>
    );
}
