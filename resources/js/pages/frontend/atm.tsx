import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { SectionHead } from '@/components/frontend/section-head';
import { ShortRequestSection } from '@/components/frontend/short-request-section';
import { Band } from '@/components/frontend/wrap';
import {
    Reveal,
    brandCardHoverClass,
} from '@/components/frontend/reveal';
import { cn } from '@/lib/utils';

type AtmPageProps = {
    hero: {
        title: string;
        lead: string;
        cta: { label: string; href: string };
    };
    benefits: {
        eyebrow: string;
        title: string;
        items: { title: string; text: string }[];
    };
    fits: {
        eyebrow: string;
        title: string;
        items: string[];
    };
    requestForm: {
        title: string;
        text: string;
        successMessage: string;
        submitLabel: string;
        subjectLabel: string;
    };
};

export default function AtmPage({
    hero,
    benefits,
    fits,
    requestForm,
}: AtmPageProps) {
    return (
        <>
            <Head title="ATM Machines">
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
            />

            <Band>
                <SectionHead
                    eyebrow={benefits.eyebrow}
                    title={benefits.title}
                />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {benefits.items.map((item, index) => (
                        <Reveal key={item.title} delay={index * 0.05}>
                            <div
                                className={cn(
                                    'h-full rounded-brand border border-brand-line bg-brand-surface p-6',
                                    brandCardHoverClass,
                                )}
                            >
                                <h3 className="font-display text-xl font-bold text-brand-ink">
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

            <Band alt>
                <SectionHead eyebrow={fits.eyebrow} title={fits.title} />
                <div className="flex flex-wrap gap-2">
                    {fits.items.map((item) => (
                        <span
                            key={item}
                            className="rounded-full border border-brand-line bg-brand-bg px-3 py-1.5 text-sm font-medium text-brand-ink"
                        >
                            {item}
                        </span>
                    ))}
                </div>
            </Band>

            <ShortRequestSection data={requestForm} id="atm-form" alt={false} />
        </>
    );
}
