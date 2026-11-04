import { Band } from '@/components/frontend/wrap';
import type { CityItem } from '@/types/frontend';

export function CityListSection({ items }: { items: CityItem[] }) {
    return (
        <Band>
            <ul className="m-0 grid list-none grid-cols-2 gap-x-4 p-0 md:grid-cols-3 lg:grid-cols-5">
                {items.map((city) => (
                    <li key={city.slug}>
                        <a
                            href={city.href}
                            className="block border-b border-brand-line py-2 text-[15px] text-brand-ink no-underline hover:border-brass"
                        >
                            {city.city}, {city.state}
                        </a>
                    </li>
                ))}
            </ul>
        </Band>
    );
}
