import { BrandButton } from '@/components/frontend/brand-button';
import { Band } from '@/components/frontend/wrap';
import type { ArticleShowPageProps, NavLink } from '@/types/frontend';

export function ArticleBodySection({
    crumb,
    article,
    cta,
}: {
    crumb: NavLink[];
    article: ArticleShowPageProps['article'];
    cta: ArticleShowPageProps['cta'];
}) {
    return (
        <Band className="pt-10 md:pt-10">
            <p className="mb-3 text-sm text-brand-muted">
                {crumb.map((item, index) => (
                    <span key={item.label}>
                        {index > 0 ? ' / ' : null}
                        {index < crumb.length - 1 ? (
                            <a
                                href={item.href}
                                className="text-brand-muted no-underline hover:underline hover:decoration-brass"
                            >
                                {item.label}
                            </a>
                        ) : (
                            <span>{item.label}</span>
                        )}
                    </span>
                ))}
            </p>
            <article className="max-w-[68ch]">
                <span className="rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase">
                    {article.cat}
                </span>
                <h1 className="mt-3 mb-2 font-display text-3xl leading-tight font-extrabold tracking-tight text-brand-ink md:text-5xl">
                    {article.title}
                </h1>
                <p className="text-brand-muted">
                    {article.readTime} · {article.updated}
                </p>
                <p className="mt-5 text-xl text-brand-ink">{article.excerpt}</p>
                {article.body.map((paragraph) => (
                    <p key={paragraph} className="mt-3.5 text-brand-ink">
                        {paragraph}
                    </p>
                ))}
                <div className="mt-6 rounded-brand border border-brand-line bg-brand-sunk p-5">
                    <h3 className="font-display text-xl font-bold text-brand-ink">
                        {cta.title}
                    </h3>
                    <p className="mt-1 mb-3 text-brand-muted">{cta.text}</p>
                    <BrandButton href={cta.button.href}>
                        {cta.button.label}
                    </BrandButton>
                </div>
            </article>
        </Band>
    );
}
