import { PackArt } from '@/components/frontend/machine-art';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { MicroMarketsOfferingsData } from '@/types/frontend';

export function MicroMarketsOfferingsSection({
    data,
}: {
    data: MicroMarketsOfferingsData;
}) {
    return (
        <Band>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                {data.items.map((item) => (
                    <div
                        key={item.kind}
                        className="flex min-w-0 flex-col items-center gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 text-center"
                    >
                        <PackArt kind={item.kind} />
                        <h3 className="font-display text-base font-bold text-brand-ink">
                            {item.name}
                        </h3>
                    </div>
                ))}
            </div>
        </Band>
    );
}
