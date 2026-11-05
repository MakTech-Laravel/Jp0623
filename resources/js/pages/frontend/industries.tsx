import { Head } from '@inertiajs/react';
import { LinkImageCardGrid } from '@/components/frontend/link-image-card-grid';
import { PageHero } from '@/components/frontend/page-hero';
import type { IndustriesPageProps } from '@/types/frontend';

export default function Industries({ hero, items }: IndustriesPageProps) {
    return (
        <>
            <Head title="Industries">
                <meta
                    head-key="description"
                    name="description"
                    content="Vending, coffee and micro markets set up for how your location works—across offices, warehouses, schools, hospitals and more."
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead} />
            <LinkImageCardGrid items={items} />
        </>
    );
}
