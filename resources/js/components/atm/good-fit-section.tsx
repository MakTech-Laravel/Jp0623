import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { AtmGoodFitData } from '@/types/frontend';

export function AtmGoodFitSection({ data }: { data: AtmGoodFitData }) {
    return (
        <Band alt>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="flex flex-wrap gap-2.5">
                {data.items.map((item) => (
                    <span
                        key={item}
                        className="rounded-full border border-brand-line bg-brand-surface px-4 py-2 font-medium text-brand-ink"
                    >
                        {item}
                    </span>
                ))}
            </div>
        </Band>
    );
}
