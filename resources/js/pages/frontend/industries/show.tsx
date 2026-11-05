import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { ShortRequestSection } from '@/components/frontend/short-request-section';
import { TextCardGrid } from '@/components/frontend/text-card-grid';
import { DetailRelatedSection } from '@/components/machines/detail-related-section';
import type { IndustryShowProps } from '@/types/frontend';

export default function IndustryShow({
    hero,
    crumb,
    industry,
    benefits,
    relatedMachines,
    requestForm,
}: IndustryShowProps) {
    return (
        <>
            <Head title={`${industry.label} Vending`}>
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero
                crumb={crumb}
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                image={hero.image}
            />
            <TextCardGrid title={benefits.title} items={benefits.items} />
            <DetailRelatedSection data={relatedMachines} />
            <ShortRequestSection
                data={requestForm}
                id="industry-request"
                alt={false}
            />
        </>
    );
}
