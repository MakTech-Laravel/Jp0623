import { Head } from '@inertiajs/react';
import { CityListSection } from '@/components/cities/city-list-section';
import { PageHero } from '@/components/frontend/page-hero';
import type { CitiesIndexPageProps } from '@/types/frontend';

export default function Cities({ hero, items }: CitiesIndexPageProps) {
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
            <CityListSection items={items} />
        </>
    );
}
