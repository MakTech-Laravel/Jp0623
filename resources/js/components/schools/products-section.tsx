import {
    CatalogImage,
    PRODUCT_IMAGE_FALLBACK,
    productImageSrc,
} from '@/components/frontend/catalog-image';
import { Reveal, brandCardHoverClass } from '@/components/frontend/reveal';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import { cn } from '@/lib/utils';
import type { SchoolsProductsData } from '@/types/frontend';

export function SchoolsProductsSection({
    data,
}: {
    data: SchoolsProductsData;
}) {
    return (
        <Band alt>
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                {data.items.map((item, index) => (
                    <Reveal key={item.kind} delay={index * 0.05}>
                        <div
                            className={cn(
                                'flex h-full min-w-0 flex-col items-center gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 text-center',
                                brandCardHoverClass,
                            )}
                        >
                            <CatalogImage
                                src={productImageSrc(item.kind, item.image)}
                                alt={item.name}
                                fallback={PRODUCT_IMAGE_FALLBACK}
                                className="max-h-28"
                            />
                            <h3 className="font-display text-base font-bold text-brand-ink">
                                {item.name}
                            </h3>
                        </div>
                    </Reveal>
                ))}
            </div>
        </Band>
    );
}
