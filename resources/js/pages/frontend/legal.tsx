import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { Band } from '@/components/frontend/wrap';

type LegalPageProps = {
    page: string;
    hero: { title: string; lead: string | null };
    sections: { heading: string | null; body: string }[];
};

export default function LegalPage({ hero, sections }: LegalPageProps) {
    return (
        <>
            <Head title={hero.title}>
                <meta
                    head-key="description"
                    name="description"
                    content={
                        hero.lead ? `${hero.title}. ${hero.lead}` : hero.title
                    }
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead ?? undefined} />

            <Band>
                <div className="prose mx-auto max-w-3xl">
                    {sections.map((section, index) => (
                        <section key={index} className="mt-8 first:mt-0">
                            {section.heading && (
                                <h2 className="font-display text-2xl font-bold text-brand-ink">
                                    {section.heading}
                                </h2>
                            )}
                            {section.body
                                .split(/\n{2,}/)
                                .filter((paragraph) => paragraph.trim() !== '')
                                .map((paragraph, paragraphIndex) => (
                                    <p
                                        key={paragraphIndex}
                                        className="mt-2 text-brand-muted"
                                    >
                                        {paragraph}
                                    </p>
                                ))}
                        </section>
                    ))}
                </div>
            </Band>
        </>
    );
}
