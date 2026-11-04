import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { ArticleGridSection } from '@/components/resources/article-grid-section';
import type { ResourcesIndexPageProps } from '@/types/frontend';

export default function Resources({
    hero,
    filters,
    items,
}: ResourcesIndexPageProps) {
    return (
        <>
            <Head title="Resource Hub">
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>
            <PageHero title={hero.title} lead={hero.lead} />
            <ArticleGridSection filters={filters} items={items} />
        </>
    );
}
