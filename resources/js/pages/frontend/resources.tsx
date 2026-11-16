import { Head } from '@inertiajs/react';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { PageHero } from '@/components/frontend/page-hero';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ArticlesData } from '@/types/frontend';
import { cn } from '@/lib/utils';

type ResourcesPageProps = {
    hero: { title: string; lead: string };
    articles: ArticlesData;
};

export default function ResourcesPage({ hero, articles }: ResourcesPageProps) {
    return (
        <>
            <Head title="Resources">
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead} />

            {articles.items.length > 0 && (
                <Band>
                    <SectionHead
                        eyebrow={articles.eyebrow}
                        title={articles.title}
                    />
                    <div className="grid gap-4 md:grid-cols-3">
                        {articles.items.map((article, index) => (
                            <Reveal key={article.id} delay={index * 0.05}>
                                <a
                                    href={article.href}
                                    className={cn(
                                        'flex h-full flex-col gap-2 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                                        brandCardHoverClass,
                                    )}
                                >
                                    <span className="font-mono text-xs tracking-wide text-brand-muted uppercase">
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
                            </Reveal>
                        ))}
                    </div>
                </Band>
            )}
        </>
    );
}
