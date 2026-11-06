import { Head, Link } from '@inertiajs/react';
import { BrandButton } from '@/components/frontend/brand-button';
import { PageHero } from '@/components/frontend/page-hero';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { FaqSection } from '@/components/home/faq-section';
import { cn } from '@/lib/utils';

type OperatorsPageProps = {
    hero: {
        eyebrow: string;
        title: string;
        lead: string;
        primaryCta: { label: string; href: string };
        secondaryCta: { label: string; href: string };
    };
    pricing: {
        eyebrow: string;
        price: string;
        period: string;
        text: string;
        cta: { label: string; href: string };
    };
    steps: {
        eyebrow: string;
        title: string;
        items: { title: string; text: string }[];
    };
    tools: {
        eyebrow: string;
        title: string;
        items: { title: string; text: string }[];
    };
    faqs: {
        eyebrow: string;
        title: string;
        items: { q: string; a: string }[];
    };
};

export default function OperatorsPage({
    hero,
    pricing,
    steps,
    tools,
    faqs,
}: OperatorsPageProps) {
    return (
        <>
            <Head title="For Operators">
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero
                eyebrow={hero.eyebrow}
                title={hero.title}
                lead={hero.lead}
                cta={hero.primaryCta}
                actions={
                    <BrandButton href={hero.secondaryCta.href} variant="ghost">
                        {hero.secondaryCta.label}
                    </BrandButton>
                }
            />

            <Band>
                <div className="mx-auto max-w-md rounded-brand border border-brand-line bg-brand-surface p-8 text-center shadow-brand-card">
                    <p className="font-mono text-xs tracking-widest text-brand-muted uppercase">
                        {pricing.eyebrow}
                    </p>
                    <p className="mt-3 font-display text-5xl font-extrabold text-brand-ink">
                        {pricing.price}
                        <span className="text-lg font-semibold text-brand-muted">
                            {pricing.period}
                        </span>
                    </p>
                    <p className="mt-3 text-sm text-brand-muted">{pricing.text}</p>
                    <div className="mt-5">
                        <BrandButton href={pricing.cta.href}>
                            {pricing.cta.label}
                        </BrandButton>
                    </div>
                </div>
            </Band>

            <Band alt>
                <SectionHead eyebrow={steps.eyebrow} title={steps.title} />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {steps.items.map((item, index) => (
                        <Reveal key={item.title} delay={index * 0.05}>
                            <div
                                className={cn(
                                    'h-full rounded-brand border border-brand-line bg-brand-surface p-5',
                                    brandCardHoverClass,
                                )}
                            >
                                <p className="font-mono text-xs text-brass">
                                    Step {index + 1}
                                </p>
                                <h3 className="mt-2 font-display text-lg font-bold text-brand-ink">
                                    {item.title}
                                </h3>
                                <p className="mt-2 text-sm text-brand-muted">
                                    {item.text}
                                </p>
                            </div>
                        </Reveal>
                    ))}
                </div>
            </Band>

            <Band>
                <SectionHead eyebrow={tools.eyebrow} title={tools.title} />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {tools.items.map((item, index) => (
                        <Reveal key={item.title} delay={index * 0.05}>
                            <div
                                className={cn(
                                    'h-full rounded-brand border border-brand-line bg-brand-surface p-5',
                                    brandCardHoverClass,
                                )}
                            >
                                <h3 className="font-display text-lg font-bold text-brand-ink">
                                    {item.title}
                                </h3>
                                <p className="mt-2 text-sm text-brand-muted">
                                    {item.text}
                                </p>
                            </div>
                        </Reveal>
                    ))}
                </div>
                <p className="mt-6 text-sm text-brand-muted">
                    Already an operator?{' '}
                    <Link
                        href="/login"
                        className="font-semibold text-brand-ink underline-offset-4 hover:underline hover:decoration-brass"
                    >
                        Sign in to browse routes for sale
                    </Link>
                    .
                </p>
            </Band>

            <FaqSection data={faqs} />
        </>
    );
}
