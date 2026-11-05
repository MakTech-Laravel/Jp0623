import { Reveal } from '@/components/frontend/reveal';
import { Band } from '@/components/frontend/wrap';
import type { AboutStoryData } from '@/types/frontend';

export function AboutStorySection({ data }: { data: AboutStoryData }) {
    return (
        <Band>
            <div className="grid items-start gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-12">
                <Reveal>
                    <div className="max-w-prose">
                        <h2 className="font-display text-2xl font-extrabold tracking-tight text-brand-ink md:text-3xl">
                            {data.title}
                        </h2>
                        {data.paragraphs.map((paragraph) => (
                            <p
                                key={paragraph}
                                className="mt-3.5 text-brand-ink"
                            >
                                {paragraph}
                            </p>
                        ))}
                        <h2 className="mt-8 font-display text-2xl font-extrabold tracking-tight text-brand-ink md:text-3xl">
                            {data.valuesTitle}
                        </h2>
                        <ul className="mt-3.5 flex list-none flex-col gap-2 p-0">
                            {data.values.map((item) => (
                                <li
                                    key={item}
                                    className="flex gap-2 text-brand-ink"
                                >
                                    <span
                                        aria-hidden
                                        className="font-bold text-good"
                                    >
                                        ✓
                                    </span>
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </Reveal>

                <Reveal delay={0.05}>
                    <div className="grid gap-3 sm:grid-cols-2">
                        {data.kpis.map((kpi) => (
                            <div
                                key={kpi.label}
                                className="min-w-0 rounded-brand border border-brand-line bg-brand-surface p-4"
                            >
                                <span className="text-sm text-brand-muted">
                                    {kpi.label}
                                </span>
                                <b className="mt-1 block font-display text-2xl font-bold tabular-nums text-brand-ink">
                                    {kpi.value}
                                </b>
                            </div>
                        ))}
                    </div>
                </Reveal>
            </div>
        </Band>
    );
}
