import { BrandButton } from '@/components/frontend/brand-button';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { AboutCtaData } from '@/types/frontend';

export function AboutCtaSection({ data }: { data: AboutCtaData }) {
    return (
        <Band alt>
            <SectionHead title={data.title} />
            <div className="flex flex-wrap gap-3">
                <BrandButton href={data.primary.href}>
                    {data.primary.label}
                </BrandButton>
                <BrandButton href={data.secondary.href} variant="ghost">
                    {data.secondary.label}
                </BrandButton>
            </div>
        </Band>
    );
}
