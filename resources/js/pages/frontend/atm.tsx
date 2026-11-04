import { Head } from '@inertiajs/react';
import { AtmBenefitsSection } from '@/components/atm/benefits-section';
import { AtmGoodFitSection } from '@/components/atm/good-fit-section';
import { MachineArt } from '@/components/frontend/machine-art';
import { PageHero } from '@/components/frontend/page-hero';
import { ShortRequestSection } from '@/components/frontend/short-request-section';
import type { AtmPageProps } from '@/types/frontend';

export default function Atm({
    hero,
    benefits,
    goodFit,
    request,
}: AtmPageProps) {
    return (
        <>
            <Head title="ATM Machines">
                <meta
                    head-key="description"
                    name="description"
                    content="Free ATM placement for businesses with cash-paying customers. We install, fill and service it."
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
            <AtmBenefitsSection data={benefits} />
            <AtmGoodFitSection data={goodFit} />
            <ShortRequestSection id="atm-form" alt={false} data={request} />
        </>
    );
}
