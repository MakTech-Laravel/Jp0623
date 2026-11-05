import { LinkImageCardGrid } from '@/components/frontend/link-image-card-grid';
import type { OfficeCoffeeMachinesData } from '@/types/frontend';

export function OfficeCoffeeMachinesSection({
    data,
}: {
    data: OfficeCoffeeMachinesData;
}) {
    return (
        <LinkImageCardGrid
            eyebrow={data.eyebrow}
            title={data.title}
            items={data.items.map((item) => ({
                title: item.title,
                text: item.text,
                image: item.image,
                href: item.href,
                tag: item.tag,
            }))}
        />
    );
}
