import { ArticleCard } from '@/components/frontend/article-card';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ArticlesData } from '@/types/frontend';

export function ResourcesSection({ data }: { data: ArticlesData }) {
    return (
        <Band>
            <SectionHead
                eyebrow={data.eyebrow}
                title={data.title}
                action={
                    <a
                        href={data.link.href}
                        className="font-medium text-brand-ink no-underline hover:underline hover:decoration-brass"
                    >
                        {data.link.label}
                    </a>
                }
            />
            <div className="grid gap-4 md:grid-cols-3">
                {data.items.map((article, index) => (
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
