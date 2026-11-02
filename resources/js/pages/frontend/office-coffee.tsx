import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { OfficeCoffeeMachinesSection } from '@/components/office-coffee/machines-section';
import { OfficeCoffeeRequestSection } from '@/components/office-coffee/request-section';
import { OfficeCoffeeServiceSection } from '@/components/office-coffee/service-section';
import { OfficeCoffeeTypesSection } from '@/components/office-coffee/types-section';
import type { OfficeCoffeePageProps } from '@/types/frontend';

export default function OfficeCoffee({
    hero,
    machines,
    types,
    service,
    requestForm,
}: OfficeCoffeePageProps) {
    return (
        <>
            <Head title="Office Coffee">
                <meta
                    head-key="description"
                    name="description"
                    content="Free office coffee brewers, delivery and installation, with supplies restocked on your schedule."
                />
            </Head>

            <PageHero
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                image={hero.image}
            />
            <OfficeCoffeeMachinesSection data={machines} />
            <OfficeCoffeeTypesSection data={types} />
            <OfficeCoffeeServiceSection data={service} />
            <OfficeCoffeeRequestSection data={requestForm} />
        </>
    );
}
