import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { MachineIndustriesData } from '@/types/frontend';

export function MachinesIndustriesSection({
    data,
}: {
    data: MachineIndustriesData;
}) {
    return (
        <Band>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {data.items.map((item) => (
                    <a
                        key={item.slug}
                        href={item.href}
                        className="flex min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline transition hover:border-brand-ink"
                    >
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {item.label}
                        </h3>
                        <p className="text-sm text-brand-muted">{item.text}</p>
                    </a>
                ))}
            </div>
        </Band>
    );
}
