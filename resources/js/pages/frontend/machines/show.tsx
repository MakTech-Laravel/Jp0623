import { Head } from '@inertiajs/react';
import { DetailOverviewSection } from '@/components/machines/detail-overview-section';
import { DetailRelatedSection } from '@/components/machines/detail-related-section';
import type { MachineDetailPageProps } from '@/types/frontend';

export default function MachineShow({
    machine,
    status,
    specs,
    checks,
    related,
    cta,
}: MachineDetailPageProps) {
    return (
        <>
            <Head title={machine.name}>
                <meta
                    head-key="description"
                    name="description"
                    content={machine.desc}
                />
            </Head>

            <DetailOverviewSection
                machine={machine}
                status={status}
                specs={specs}
                checks={checks}
                cta={cta}
            />
            <DetailRelatedSection data={related} />
        </>
    );
}
