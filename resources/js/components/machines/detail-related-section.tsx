import { MachineArt } from '@/components/frontend/machine-art';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { MachineDetailRelatedData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function DetailRelatedSection({
    data,
}: {
    data: MachineDetailRelatedData;
}) {
    if (data.items.length === 0) {
        return null;
    }

    return (
        <Band alt>
            <SectionHead title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                                <MachineArt
                                    kind={machine.kind}
                                    className="h-auto max-h-44 w-auto max-w-full"
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
