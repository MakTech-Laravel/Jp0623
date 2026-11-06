import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { CitiesSection } from '@/components/home/cities-section';
import type { CitiesData } from '@/types/frontend';

type CitiesPageProps = {
    hero: { title: string; lead: string };
    cities: CitiesData;
};

export default function CitiesPage({ hero, cities }: CitiesPageProps) {
    return (
        <>
            <Head title="Cities We Serve">
                <meta
                    head-key="description"
                    name="description"
                    content={hero.lead}
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead} />
            <CitiesSection data={cities} />
        </>
    );
}
