import { useState } from 'react';
import { PackArt } from '@/components/frontend/machine-art';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { Band } from '@/components/frontend/wrap';
import type { ProductCatalogData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function CatalogSection({ data }: { data: ProductCatalogData }) {
    const [filter, setFilter] = useState(data.filters[0] ?? 'All');

    const visible = data.items.filter(
        (item) => filter === 'All' || item.name === filter,
    );

    return (
        <Band>
            <div className="mb-5 flex flex-wrap gap-2">
                {data.filters.map((chip) => (
                    <button
                        key={chip}
                        type="button"
                        aria-pressed={filter === chip}
                        onClick={() => setFilter(chip)}
                        className={cn(
                            'rounded-full border-2 border-brand-line bg-brand-surface px-3.5 py-1.5 text-sm font-medium text-brand-ink',
                            filter === chip &&
                                'border-brand-ink bg-brand-ink text-brand-bg',
                        )}
                    >
                        {chip}
                    </button>
                ))}
            </div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {visible.map((item, index) => (
                    <Reveal key={item.kind} delay={index * 0.05}>
                        <div
                            className={cn(
                                'flex h-full min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6',
                                brandCardHoverClass,
                            )}
                        >
                            <div className="flex items-center gap-3.5">
                                <PackArt kind={item.kind} />
                                <h3 className="font-display text-xl font-bold text-brand-ink">
                                    {item.name}
                                </h3>
                            </div>
                            <p className="text-sm text-brand-muted">
                                {item.desc}
                            </p>
                            <div className="flex flex-wrap gap-1.5">
                                {item.tags.map((tag) => (
                                    <span
                                        key={tag}
                                        className="rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase"
                                    >
                                        {tag}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
