import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { MachinesFaqSection } from '@/components/machines/faq-section';
import { MachinesIndustriesSection } from '@/components/machines/industries-section';
import { LineupSection } from '@/components/machines/lineup-section';
import { TechSection } from '@/components/machines/tech-section';
import type { MachinesPageProps } from '@/types/frontend';

export default function Machines({
    hero,
    lineup,
    tech,
    industries,
    faqs,
}: MachinesPageProps) {
    return (
        <>
            <Head title="Vending Machines">
                <meta
                    head-key="description"
                    name="description"
                    content="Modern snack, drink and combo vending machines placed, stocked and serviced at no cost to your business."
                />
            </Head>

            <PageHero
                eyebrow={hero.eyebrow}
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                art={hero.art}
            />
            <LineupSection data={lineup} />
            <TechSection data={tech} />
            <MachinesIndustriesSection data={industries} />
            <MachinesFaqSection data={faqs} />
        </>
    );
}
