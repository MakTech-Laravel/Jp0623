import { Head } from '@inertiajs/react';
import {
    CatalogImage,
    PRODUCT_IMAGE_FALLBACK,
} from '@/components/frontend/catalog-image';
import { PageHero } from '@/components/frontend/page-hero';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { cn } from '@/lib/utils';

type SchoolsPageProps = {
    hero: {
        title: string;
        lead: string;
        cta: { label: string; href: string };
        image: string;
    };
    audiences: {
        title: string;
        items: { title: string; text: string }[];
    };
    products: {
        eyebrow: string;
        title: string;
        items: { title: string; image: string }[];
    };
    guide: {
        eyebrow: string;
        title: string;
        item: {
            id: string;
            cat: string;
            title: string;
            excerpt: string;
            href: string;
        };
    };
};

export default function SchoolsPage({
    hero,
    audiences,
    products,
    guide,
}: SchoolsPageProps) {
    return (
        <>
            <Head title="School Vending">
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                image={hero.image}
            />

            <Band>
                <SectionHead title={audiences.title} />
                <div className="grid gap-4 md:grid-cols-2">
                    {audiences.items.map((item, index) => (
                        <Reveal key={item.title} delay={index * 0.05}>
                            <div
                                className={cn(
                                    'h-full rounded-brand border border-brand-line bg-brand-surface p-6',
                                    brandCardHoverClass,
                                )}
                            >
                                <h3 className="font-display text-xl font-bold text-brand-ink">
                                    {item.title}
                                </h3>
                                <p className="mt-2 text-sm text-brand-muted">
                                    {item.text}
                                </p>
                            </div>
                        </Reveal>
                    ))}
                </div>
            </Band>

            <Band alt>
                <SectionHead
                    eyebrow={products.eyebrow}
                    title={products.title}
                />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {products.items.map((item, index) => (
                        <Reveal key={item.title} delay={index * 0.05}>
                            <div
                                className={cn(
                                    'flex h-full flex-col items-center gap-3 rounded-brand border border-brand-line bg-brand-surface p-5 text-center',
                                    brandCardHoverClass,
                                )}
                            >
                                <CatalogImage
                                    src={item.image}
                                    alt={item.title}
                                    fallback={PRODUCT_IMAGE_FALLBACK}
                                />
                                <h3 className="font-display text-base font-bold text-brand-ink">
                                    {item.title}
                                </h3>
                            </div>
                        </Reveal>
                    ))}
                </div>
            </Band>

            <Band>
                <SectionHead eyebrow={guide.eyebrow} title={guide.title} />
                <a
                    href={guide.item.href === '#' ? '/resources' : guide.item.href}
                    className={cn(
                        'block max-w-xl rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                        brandCardHoverClass,
                    )}
                >
                    <span className="font-mono text-xs tracking-wide text-brand-muted uppercase">
                        {guide.item.cat}
                    </span>
                    <h3 className="mt-2 font-display text-xl font-bold text-brand-ink">
                        {guide.item.title}
                    </h3>
                    <p className="mt-2 text-sm text-brand-muted">
                        {guide.item.excerpt}
                    </p>
                </a>
            </Band>
        </>
    );
}
