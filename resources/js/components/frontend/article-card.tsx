import { Reveal, brandCardHoverClass } from '@/components/frontend/reveal';
import { cn } from '@/lib/utils';
import type { ArticleItem } from '@/types/frontend';

export function ArticleCard({
    article,
    index = 0,
}: {
    article: ArticleItem;
    index?: number;
}) {
    return (
        <Reveal delay={index * 0.05}>
            <a
                href={article.href}
                className={cn(
                    'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                    brandCardHoverClass,
                )}
            >
                <span className="self-start rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase">
                    {article.cat}
                </span>
                <h3 className="font-display text-xl font-bold text-brand-ink">
                    {article.title}
                </h3>
                <p className="text-sm text-brand-muted">{article.excerpt}</p>
                <span className="mt-auto inline-flex self-start rounded-full border border-current px-3 py-1.5 text-sm font-semibold">
                    Read guide
                </span>
            </a>
        </Reveal>
    );
}
