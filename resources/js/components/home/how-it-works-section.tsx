import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { HowItWorksData } from '@/types/frontend';

export function HowItWorksSection({ data }: { data: HowItWorksData }) {
    return (
        <Band>
            <SectionHead
                eyebrow={data.eyebrow}
                title={data.title}
                text={data.text}
            />
            <div className="grid border-t-2 border-brand-ink md:grid-cols-3">
                {data.steps.map((step, index) => (
                    <div
                        key={step.title}
                        className="pt-5 pr-0 md:pr-6 md:pb-0"
                    >
                        <span className="inline-block rounded bg-brass px-2 py-0.5 font-mono text-xs font-semibold tracking-wide text-brass-ink uppercase">
                            Step {index + 1}
                        </span>
                        <h3 className="mt-3.5 font-display text-xl font-bold text-brand-ink">
                            {step.title}
                        </h3>
                        <p className="mt-2 text-brand-muted">{step.text}</p>
                    </div>
                ))}
            </div>
        </Band>
    );
}
