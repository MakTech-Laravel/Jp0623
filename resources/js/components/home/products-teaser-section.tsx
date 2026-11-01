import { PackArt } from '@/components/frontend/machine-art';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ProductsData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function ProductsTeaserSection({ data }: { data: ProductsData }) {
    return (
        <Band alt>
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
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                {data.items.map((product, index) => (
                    <Reveal key={product.kind} delay={index * 0.05}>
                        <a
                            href={product.href}
                            className={cn(
                                'flex h-full min-w-0 flex-col items-center gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 text-center no-underline',
                                brandCardHoverClass,
                            )}
                        >
                            <PackArt kind={product.kind} />
                            <h3 className="font-display text-base font-bold text-brand-ink">
                                {product.name}
                            </h3>
                        </a>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
