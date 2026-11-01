import { MachineArt } from '@/components/frontend/machine-art';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { MicroMarketsCompareData } from '@/types/frontend';

export function MicroMarketsCompareSection({
    data,
}: {
    data: MicroMarketsCompareData;
}) {
    return (
        <Band alt>
            <SectionHead title={data.title} />
            <div className="grid gap-4 md:grid-cols-2">
                {data.items.map((item) => (
                    <a
                        key={item.title}
                        href={item.href}
                        className="flex min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline transition hover:border-brand-ink"
                    >
                        <div className="grid place-items-center rounded-lg bg-brand-sunk p-3.5">
                            <MachineArt
                                kind={item.art}
                                className="h-auto max-h-44 w-auto max-w-full"
                            />
                        </div>
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {item.title}
                        </h3>
                        <p className="text-sm text-brand-muted">{item.text}</p>
                    </a>
                ))}
            </div>
        </Band>
    );
}
