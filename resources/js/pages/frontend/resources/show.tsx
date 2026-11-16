import { Head } from '@inertiajs/react';
import {
    CatalogImage,
    MACHINE_IMAGE_FALLBACK,
} from '@/components/frontend/catalog-image';
import { PageHero } from '@/components/frontend/page-hero';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import { cn } from '@/lib/utils';
import type { ArticleDetail, ArticleItem } from '@/types/frontend';

type ResourceShowProps = {
    article: ArticleDetail;
    related: ArticleItem[];
};

export default function ResourceShow({ article, related }: ResourceShowProps) {
    return (
        <>
            <Head title={article.title}>
                <meta
                    head-key="description"
                    name="description"
                    content={article.excerpt}
                />
            </Head>

            <PageHero
                eyebrow={article.cat}
                title={article.title}
                lead={article.excerpt}
                crumb={[
                    { label: 'Home', href: '/' },
                    { label: 'Resources', href: '/resources' },
                    { label: article.title, href: `/resources/${article.slug}` },
                ]}
            />

            <Band>
                <article className="mx-auto max-w-2xl">
                    {article.image ? (
                        <div className="mb-6">
                            <CatalogImage
                                src={article.image}
                                alt={article.title}
                                fallback={MACHINE_IMAGE_FALLBACK}
                                className="max-h-96 w-full"
                            />
                        </div>
                    ) : null}
                    <div className="space-y-4 text-base leading-relaxed text-brand-ink">
                        {article.paragraphs.map((paragraph, index) => (
                            <p key={index}>{paragraph}</p>
                        ))}
                    </div>
                    <a
                        href="/resources"
                        className="mt-8 inline-block font-medium text-brand-ink no-underline hover:underline hover:decoration-brass"
                    >
                        ← Back to all resources
                    </a>
                </article>
            </Band>

            {related.length > 0 && (
                <Band alt>
                    <SectionHead eyebrow="Keep reading" title="More guides." />
                    <div className="grid gap-4 md:grid-cols-2">
                        {related.map((item, index) => (
                            <Reveal key={item.id} delay={index * 0.05}>
                                <a
                                    href={item.href}
                                    className={cn(
                                        'flex h-full flex-col gap-2 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                                        brandCardHoverClass,
                                    )}
                                >
                                    <span className="font-mono text-xs tracking-wide text-brand-muted uppercase">
                                        {item.cat}
                                    </span>
                                    <h3 className="font-display text-xl font-bold text-brand-ink">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm text-brand-muted">
                                        {item.excerpt}
                                    </p>
                                </a>
                            </Reveal>
                        ))}
                    </div>
                </Band>
            )}
        </>
    );
}
