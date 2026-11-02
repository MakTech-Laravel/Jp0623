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
import type { OfficeCoffeeMachinesData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function OfficeCoffeeMachinesSection({
    data,
}: {
    data: OfficeCoffeeMachinesData;
}) {
    return (
        <Band>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {data.items.map((item, index) => (
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
                            <span className="mt-auto self-start rounded bg-brand-sunk px-2 py-0.5 font-mono text-[11px] font-semibold tracking-wide text-brand-muted uppercase">
                                {item.tag}
                            </span>
                        </a>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
