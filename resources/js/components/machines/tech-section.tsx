import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { MachineTechData } from '@/types/frontend';

export function TechSection({ data }: { data: MachineTechData }) {
    return (
        <Band alt>
            <SectionHead title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {data.items.map((item) => (
                    <div
                        key={item.title}
                        className="flex min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6"
                    >
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {item.title}
                        </h3>
                        <p className="text-sm text-brand-muted">{item.text}</p>
                    </div>
                ))}
            </div>
        </Band>
    );
}
