import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ArticlesData } from '@/types/frontend';

export function ResourcesSection({ data }: { data: ArticlesData }) {
    return (
        <Band>
            <SectionHead
                eyebrow={data.eyebrow}
                title={data.title}
                text={
                    <a
                        href={data.link.href}
                        className="font-medium text-brand-ink no-underline hover:underline hover:decoration-brass"
                    >
                        {data.link.label}
                    </a>
                }
            />
            <div className="grid gap-4 md:grid-cols-3">
                {data.items.map((article) => (
                    <a
                        key={article.id}
                        href={article.href}
                        className="flex min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline transition hover:border-brand-ink"
                    >
                        <span className="self-start rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase">
                            {article.cat}
                        </span>
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {article.title}
                        </h3>
                        <p className="text-sm text-brand-muted">
                            {article.excerpt}
                        </p>
                        <span className="mt-auto inline-flex self-start rounded-full border border-current px-3 py-1.5 text-sm font-semibold">
                            Read guide
                        </span>
                    </a>
                ))}
            </div>
        </Band>
    );
}
