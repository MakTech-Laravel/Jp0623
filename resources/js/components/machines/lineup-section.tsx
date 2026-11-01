import { useState } from 'react';
import { MachineArt } from '@/components/frontend/machine-art';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { MachineLineupData } from '@/types/frontend';
import { cn } from '@/lib/utils';

export function LineupSection({ data }: { data: MachineLineupData }) {
    const [filter, setFilter] = useState(data.filters[0] ?? 'All');

    const visible = data.items.filter(
        (machine) => filter === 'All' || machine.type === filter,
    );

    return (
        <Band>
            <SectionHead title={data.title} text={data.text} />
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
                {visible.map((machine) => (
                    <a
                        key={machine.id}
                        href={machine.href}
                        className="flex min-w-0 flex-col gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 no-underline transition hover:border-brand-ink"
                    >
                        <div className="grid place-items-center rounded-lg bg-brand-sunk p-3.5">
                            <MachineArt
                                kind={machine.kind}
                                className="h-auto max-h-44 w-auto max-w-full"
                            />
                        </div>
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {machine.name}
                        </h3>
                        <div className="flex flex-wrap gap-1.5">
                            {machine.tags.map((tag) => (
                                <span
                                    key={tag}
                                    className="rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase"
                                >
                                    {tag}
                                </span>
                            ))}
                        </div>
                        <p className="text-sm text-brand-muted">
                            {machine.desc}
                        </p>
                        <span className="mt-auto inline-flex self-start rounded-full border border-current px-3 py-1.5 text-sm font-semibold">
                            View details
                        </span>
                    </a>
                ))}
            </div>
        </Band>
    );
}
