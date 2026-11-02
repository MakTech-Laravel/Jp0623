import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { OfficeCoffeeServiceData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function OfficeCoffeeServiceSection({
    data,
}: {
    data: OfficeCoffeeServiceData;
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
                            <h3 className="font-display text-xl font-bold text-brand-ink">
                                {item.title}
                            </h3>
                            <p className="text-sm text-brand-muted">
                                {item.text}
                            </p>
                        </a>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
