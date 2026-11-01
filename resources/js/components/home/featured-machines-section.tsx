import {
    CatalogImage,
    MACHINE_IMAGE_FALLBACK,
    machineImageSrc,
} from '@/components/frontend/catalog-image';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { FeaturedMachinesData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function FeaturedMachinesSection({
    data,
}: {
    data: FeaturedMachinesData;
}) {
    return (
        <Band>
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
            <div className="grid gap-4 md:grid-cols-3">
                {data.items.map((machine, index) => (
                    <Reveal key={machine.id} delay={index * 0.05}>
                        <a
                            href={machine.href}
                            className={cn(
                                'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                                brandCardHoverClass,
                            )}
                        >
                            <div className="grid place-items-center rounded-lg bg-brand-sunk p-3.5">
                                <CatalogImage
                                    src={machineImageSrc(
                                        machine.kind,
                                        machine.image,
                                    )}
                                    alt={machine.name}
                                    fallback={MACHINE_IMAGE_FALLBACK}
                                />
                            </div>
                            <h3 className="font-display text-xl font-bold text-brand-ink">
                                {machine.name}
                            </h3>
                            <div className="flex flex-wrap gap-1.5">
                                {machine.tags.map((tag) => (
                                    <span
                                        key={tag}
                                        className="rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase"
                                    >
                                        {tag}
                                    </span>
                                ))}
                            </div>
                            <p className="text-sm text-brand-muted">
                                {machine.desc}
                            </p>
                            <span className="mt-auto inline-flex self-start rounded-full border border-current px-3 py-1.5 text-sm font-semibold">
                                View details
                            </span>
                        </a>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
