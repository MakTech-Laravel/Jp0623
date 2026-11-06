import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { Band } from '@/components/frontend/wrap';

type LegalPageProps = {
    page: string;
    hero: { title: string; lead: string };
    banner: string;
    sections: { heading: string; body: string }[];
};

export default function LegalPage({ hero, banner, sections }: LegalPageProps) {
    return (
        <>
            <Head title={hero.title}>
                <meta
                    head-key="description"
                    name="description"
                    content={`${hero.title}. ${hero.lead}`}
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead} />

            <Band>
                <div className="prose mx-auto max-w-3xl">
                    <p className="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-semibold text-amber-900">
                        {banner}
                    </p>
                    {sections.map((section) => (
                        <section key={section.heading} className="mt-8">
                            <h2 className="font-display text-2xl font-bold text-brand-ink">
                                {section.heading}
                            </h2>
                            <p className="mt-2 text-brand-muted">{section.body}</p>
                        </section>
                    ))}
                </div>
            </Band>
        </>
    );
}
