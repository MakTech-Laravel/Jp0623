import { Head } from '@inertiajs/react';
import { MachineArt } from '@/components/frontend/machine-art';
import { PageHero } from '@/components/frontend/page-hero';
import { SchoolsAudiencesSection } from '@/components/schools/audiences-section';
import { SchoolsGuideSection } from '@/components/schools/guide-section';
import { SchoolsProductsSection } from '@/components/schools/products-section';
import type { SchoolsPageProps } from '@/types/frontend';

export default function Schools({
    hero,
    audiences,
    products,
    guide,
}: SchoolsPageProps) {
    return (
        <>
            <Head title="School Vending">
                <meta
                    head-key="description"
                    name="description"
                    content="Vending for K-12 schools, colleges and universities, with products that follow nutrition rules for students."
                />
            </Head>
            <PageHero
                title={hero.title}
                lead={hero.lead}
                cta={hero.cta}
                art={
                    hero.art ? (
                        <MachineArt
                            kind={hero.art}
                            className="mx-auto block h-auto max-h-72 w-full max-w-[200px]"
                        />
                    ) : undefined
                }
            />
            <SchoolsAudiencesSection data={audiences} />
            <SchoolsProductsSection data={products} />
            <SchoolsGuideSection data={guide} />
        </>
    );
}
