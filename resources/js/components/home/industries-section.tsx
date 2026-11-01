import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { IndustriesData } from '@/types/frontend';

export function IndustriesSection({ data }: { data: IndustriesData }) {
    return (
        <Band>
            <SectionHead
                eyebrow={data.eyebrow}
                title={data.title}
                text={data.text}
            />
            <div className="flex flex-wrap gap-2.5">
                {data.items.map((item) => (
                    <a
                        key={item.slug}
                        href={item.href}
                        className="rounded-full border border-brand-line bg-brand-surface px-4 py-2 font-medium text-brand-ink no-underline transition hover:border-brand-ink"
                    >
                        {item.label}
                    </a>
                ))}
            </div>
        </Band>
    );
}
