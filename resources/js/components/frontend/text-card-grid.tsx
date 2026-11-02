import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import { cn } from '@/lib/utils';

export type TextCardItem = {
    title: string;
    text: string;
    href?: string;
};

type TextCardGridProps = {
    items: TextCardItem[];
    eyebrow?: string;
    title?: string;
    alt?: boolean;
    columns?: '3' | '4';
};

export function TextCardGrid({
    items,
    eyebrow,
    title,
    alt = false,
    columns = '3',
}: TextCardGridProps) {
    return (
        <Band alt={alt}>
            {title ? <SectionHead eyebrow={eyebrow} title={title} /> : null}
            <div
                className={cn(
                    'grid gap-4 sm:grid-cols-2',
                    columns === '4' ? 'lg:grid-cols-4' : 'lg:grid-cols-3',
                )}
            >
                {items.map((item, index) => (
                    <Reveal key={item.title} delay={index * 0.05}>
                        {item.href ? (
                            <a
                                href={item.href}
                                className={cn(
                                    'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                                    brandCardHoverClass,
                                )}
                            >
                                <h3 className="font-display text-xl font-bold text-brand-ink">
                                    {item.title}
                                </h3>
                                <p className="text-sm text-brand-muted">
                                    {item.text}
                                </p>
                            </a>
                        ) : (
                            <div
                                className={cn(
                                    'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6',
                                    brandCardHoverClass,
                                )}
                            >
                                <h3 className="font-display text-xl font-bold text-brand-ink">
                                    {item.title}
                                </h3>
                                <p className="text-sm text-brand-muted">
                                    {item.text}
                                </p>
                            </div>
                        )}
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
