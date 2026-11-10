import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { FaqsData } from '@/types/frontend';

export function OfficeCoffeeFaqSection({ data }: { data: FaqsData }) {
    if (data.items.length === 0) {
        return null;
    }

    return (
        <Band alt>
            <SectionHead
                align="center"
                eyebrow={data.eyebrow}
                title={data.title}
            />
            <div className="mx-auto max-w-4xl">
                {data.items.map((item) => (
                    <details
                        key={item.q}
                        className="group border-b border-brand-line py-4"
                    >
                        <summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-display text-lg font-semibold text-brand-ink after:font-mono after:text-brand-muted after:content-['+'] group-open:after:content-['–'] [&::-webkit-details-marker]:hidden">
                            {item.q}
                        </summary>
                        <p className="mt-2.5 max-w-prose text-brand-muted">
                            {item.a}
                        </p>
                    </details>
                ))}
            </div>
        </Band>
    );
}
