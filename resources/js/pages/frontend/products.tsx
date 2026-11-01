import { Head } from '@inertiajs/react';
import { PageHero } from '@/components/frontend/page-hero';
import { CatalogSection } from '@/components/products/catalog-section';
import type { ProductsPageProps } from '@/types/frontend';

export default function Products({ hero, catalog }: ProductsPageProps) {
    return (
        <>
            <Head title="Snacks & Beverages">
                <meta
                    head-key="description"
                    name="description"
                    content="Popular brands, healthier picks and local favorites for vending machines, micro markets and office coffee."
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead} cta={hero.cta} />
            <CatalogSection data={catalog} />
        </>
    );
}
