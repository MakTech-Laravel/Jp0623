import { Head } from '@inertiajs/react';
import { NearbyLinks } from '@/components/cities/nearby-links';
import { BrandButton } from '@/components/frontend/brand-button';
import { MachineArt } from '@/components/frontend/machine-art';
import { PageHero } from '@/components/frontend/page-hero';
import { TextCardGrid } from '@/components/frontend/text-card-grid';
import { RequestSection } from '@/components/home/request-section';
import { MicroMarketsFaqSection } from '@/components/micro-markets/faq-section';
import type { CityShowPageProps } from '@/types/frontend';

export default function CityShow({
    hero,
    crumb,
    city,
    benefits,
    requestForm,
    faqs,
    nearby,
}: CityShowPageProps) {
    return (
        <>
            <Head title={`Vending in ${city.city}, ${city.state}`}>
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero
                crumb={crumb}
                eyebrow={hero.eyebrow}
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                actions={
                    <BrandButton href="/machines" variant="ghost">
                        See machines
                    </BrandButton>
                }
                art={
                    hero.art ? (
                        <MachineArt
                            kind={hero.art}
                            className="mx-auto block h-auto max-h-72 w-full max-w-[200px]"
                        />
                    ) : undefined
                }
            />
            <TextCardGrid title={benefits.title} items={benefits.items} />
            <RequestSection data={requestForm} />
            <MicroMarketsFaqSection data={faqs} />
            <NearbyLinks items={nearby} />
        </>
    );
}
