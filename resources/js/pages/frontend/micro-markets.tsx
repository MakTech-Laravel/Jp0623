import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { MicroMarketsCompareSection } from '@/components/micro-markets/compare-section';
import { MicroMarketsEquipmentSection } from '@/components/micro-markets/equipment-section';
import { MicroMarketsFaqSection } from '@/components/micro-markets/faq-section';
import { MicroMarketsHowItWorksSection } from '@/components/micro-markets/how-it-works-section';
import { MicroMarketsOfferingsSection } from '@/components/micro-markets/offerings-section';
import type { MicroMarketsPageProps } from '@/types/frontend';

export default function MicroMarkets({
    hero,
    howItWorks,
    equipment,
    offerings,
    compare,
    faqs,
}: MicroMarketsPageProps) {
    return (
        <>
            <Head title="Micro Markets">
                <meta
                    head-key="description"
                    name="description"
                    content="A small self-checkout store in your break room with fresh food, snacks and drinks. Free equipment and setup."
                />
            </Head>

            <PageHero
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                art={hero.art}
            />
            <MicroMarketsHowItWorksSection data={howItWorks} />
            <MicroMarketsEquipmentSection data={equipment} />
            <MicroMarketsOfferingsSection data={offerings} />
            <MicroMarketsCompareSection data={compare} />
            <MicroMarketsFaqSection data={faqs} />
        </>
    );
}
