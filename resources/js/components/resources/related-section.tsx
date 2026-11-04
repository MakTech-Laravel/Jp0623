import { ArticleCard } from '@/components/frontend/article-card';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ArticleItem } from '@/types/frontend';

export function RelatedSection({ items }: { items: ArticleItem[] }) {
    return (
        <Band alt>
            <SectionHead title="Related articles." />
            <div className="grid gap-4 md:grid-cols-3">
                {items.map((article, index) => (
                    <ArticleCard
                        key={article.id}
                        article={article}
                        index={index}
                    />
                ))}
            </div>
        </Band>
    );
}
