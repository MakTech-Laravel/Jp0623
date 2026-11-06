import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { CitiesData } from '@/types/frontend';

export function CitiesSection({ data }: { data: CitiesData }) {
    return (
        <Band alt id="cities">
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <ul className="m-0 grid list-none grid-cols-2 gap-x-4 gap-y-2 p-0 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                {data.items.map((city) => (
                    <li key={`${city.city}-${city.state}`}>
                        <a
                            href={city.href}
                            className="text-brand-ink no-underline hover:underline hover:decoration-brass"
                        >
                            {city.label}
                        </a>
                    </li>
                ))}
            </ul>
        </Band>
    );
}
