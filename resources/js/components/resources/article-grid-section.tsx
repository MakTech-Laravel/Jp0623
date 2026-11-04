import { useState } from 'react';
import { ArticleCard } from '@/components/frontend/article-card';
import { Band } from '@/components/frontend/wrap';
import { cn } from '@/lib/utils';
import type { ArticleItem } from '@/types/frontend';

export function ArticleGridSection({
    filters,
    items,
}: {
    filters: string[];
    items: ArticleItem[];
}) {
    const [filter, setFilter] = useState(filters[0] ?? 'All');

    const visible = items.filter(
        (item) => filter === 'All' || item.cat === filter,
    );

    return (
        <Band>
            <div className="mb-5 flex flex-wrap gap-2">
                {filters.map((chip) => (
                    <button
                        key={chip}
                        type="button"
                        aria-pressed={filter === chip}
                        onClick={() => setFilter(chip)}
                        className={cn(
                            'rounded-full border-2 border-brand-line bg-brand-surface px-3.5 py-1.5 text-sm font-medium text-brand-ink',
                            filter === chip &&
                                'border-brand-ink bg-brand-ink text-brand-bg',
                        )}
                    >
                        {chip}
                    </button>
                ))}
            </div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {visible.map((article, index) => (
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
