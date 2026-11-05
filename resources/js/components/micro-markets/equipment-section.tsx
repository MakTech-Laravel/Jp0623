import { TextCardGrid } from '@/components/frontend/text-card-grid';
import type { MicroMarketsEquipmentData } from '@/types/frontend';

export function MicroMarketsEquipmentSection({
    data,
}: {
    data: MicroMarketsEquipmentData;
}) {
    return (
        <TextCardGrid
            eyebrow={data.eyebrow}
            title={data.title}
            alt
            columns="4"
            items={data.items}
        />
    );
}
