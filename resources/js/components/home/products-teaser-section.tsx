import { PackArt } from '@/components/frontend/machine-art';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ProductsData } from '@/types/frontend';

export function ProductsTeaserSection({ data }: { data: ProductsData }) {
    return (
        <Band alt>
            <SectionHead
                eyebrow={data.eyebrow}
                title={data.title}
                text={
                    <a
                        href={data.link.href}
                        className="font-medium text-brand-ink no-underline hover:underline hover:decoration-brass"
                    >
                        {data.link.label}
                    </a>
                }
            />
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                {data.items.map((product) => (
                    <a
                        key={product.kind}
                        href={product.href}
                        className="flex min-w-0 flex-col items-center gap-2.5 rounded-brand border border-brand-line bg-brand-surface p-6 text-center no-underline transition hover:border-brand-ink"
                    >
                        <PackArt kind={product.kind} />
                        <h3 className="font-display text-base font-bold text-brand-ink">
                            {product.name}
                        </h3>
                    </a>
                ))}
            </div>
        </Band>
    );
}
