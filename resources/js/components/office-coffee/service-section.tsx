import { TextCardGrid } from '@/components/frontend/text-card-grid';
import type { OfficeCoffeeServiceData } from '@/types/frontend';

export function OfficeCoffeeServiceSection({
    data,
}: {
    data: OfficeCoffeeServiceData;
}) {
    return (
        <TextCardGrid
            eyebrow={data.eyebrow}
            title={data.title}
            items={data.items.map((item) => ({
                title: item.title,
                text: item.text,
                href: item.href,
            }))}
        />
    );
}
