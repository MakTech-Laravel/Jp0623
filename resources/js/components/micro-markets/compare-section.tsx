import { LinkImageCardGrid } from '@/components/frontend/link-image-card-grid';
import type { MicroMarketsCompareData } from '@/types/frontend';

export function MicroMarketsCompareSection({
    data,
}: {
    data: MicroMarketsCompareData;
}) {
    if (data.items.length === 0) {
        return null;
    }

    return (
        <LinkImageCardGrid
            title={data.title}
            alt
            columns="2"
            items={data.items.map((item) => ({
                title: item.title,
                text: item.text,
                image: item.image,
                href: item.href,
                tag: item.tag ?? undefined,
            }))}
        />
    );
}
