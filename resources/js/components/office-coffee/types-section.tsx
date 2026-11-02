import { Reveal } from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { OfficeCoffeeTypesData } from '@/types/frontend';

export function OfficeCoffeeTypesSection({
    data,
}: {
    data: OfficeCoffeeTypesData;
}) {
    return (
        <Band alt>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <Reveal>
                <div className="flex flex-wrap gap-2.5">
                    {data.items.map((item) => (
                        <a
                            key={item.label}
                            href={item.href}
                            className="rounded-full border border-brand-line bg-brand-surface px-4 py-2.5 text-sm font-medium text-brand-ink no-underline transition hover:border-brand-ink"
                        >
                            {item.label}
                        </a>
                    ))}
                </div>
            </Reveal>
        </Band>
    );
}
