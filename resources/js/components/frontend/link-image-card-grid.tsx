import {
    CatalogImage,
    MACHINE_IMAGE_FALLBACK,
} from '@/components/frontend/catalog-image';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import { cn } from '@/lib/utils';

export type LinkImageCardItem = {
    title: string;
    text: string;
    image: string;
    href: string;
    tag?: string;
};

type LinkImageCardGridProps = {
    items: LinkImageCardItem[];
    eyebrow?: string;
    title?: string;
    alt?: boolean;
    columns?: '2' | '3';
};

export function LinkImageCardGrid({
    items,
    eyebrow,
    title,
    alt = false,
    columns = '3',
}: LinkImageCardGridProps) {
    return (
        <Band alt={alt}>
            {title ? <SectionHead eyebrow={eyebrow} title={title} /> : null}
            <div
                className={cn(
                    'grid gap-4',
                    columns === '2'
                        ? 'md:grid-cols-2'
                        : 'sm:grid-cols-2 lg:grid-cols-3',
                )}
            >
                {items.map((item, index) => (
                    <Reveal key={item.title} delay={index * 0.05}>
                        <a
                            href={item.href}
                            className={cn(
                                'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                                brandCardHoverClass,
                            )}
                        >
                            <div className="grid place-items-center rounded-lg bg-brand-sunk p-3.5">
                                <CatalogImage
                                    src={item.image}
                                    alt={item.title}
                                    fallback={MACHINE_IMAGE_FALLBACK}
                                />
                            </div>
                            <h3 className="font-display text-xl font-bold text-brand-ink">
                                {item.title}
                            </h3>
                            <p className="text-sm text-brand-muted">
                                {item.text}
                            </p>
                            {item.tag ? (
                                <span className="mt-auto self-start rounded bg-brand-sunk px-2 py-0.5 font-mono text-[11px] font-semibold tracking-wide text-brand-muted uppercase">
                                    {item.tag}
                                </span>
                            ) : null}
                        </a>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
