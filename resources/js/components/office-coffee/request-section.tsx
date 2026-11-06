import { ShortRequestSection } from '@/components/frontend/short-request-section';
import { store as storeCoffeeServiceRequest } from '@/routes/coffee-service-requests';
import type { OfficeCoffeeRequestFormData } from '@/types/frontend';

export function OfficeCoffeeRequestSection({
    data,
}: {
    data: OfficeCoffeeRequestFormData;
}) {
    return (
        <ShortRequestSection
            data={data}
            id="coffee-form"
            action={storeCoffeeServiceRequest()}
        />
    );
}
