import { Reveal, brandCardHoverClass } from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import { cn } from '@/lib/utils';
import type { SchoolsAudiencesData } from '@/types/frontend';

export function SchoolsAudiencesSection({
    data,
}: {
    data: SchoolsAudiencesData;
}) {
    return (
        <Band>
            <SectionHead title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2">
                {data.items.map((item, index) => (
                    <Reveal key={item.title} delay={index * 0.05}>
                        <div
                            className={cn(
                                'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6',
                                brandCardHoverClass,
                            )}
                        >
                            <h3 className="font-display text-xl font-bold text-brand-ink">
                                {item.title}
                            </h3>
                            <p className="text-[15px] text-brand-muted">
                                {item.text}
                            </p>
                        </div>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
