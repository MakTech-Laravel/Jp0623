import { MachineArt } from '@/components/frontend/machine-art';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ServicesData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function ServicesSection({ data }: { data: ServicesData }) {
    return (
        <Band alt>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {data.items.map((item, index) => (
                    <Reveal key={item.title} delay={index * 0.05}>
                        <a
                            href={item.href}
                            className={cn(
                                'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline',
                                brandCardHoverClass,
                            )}
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
