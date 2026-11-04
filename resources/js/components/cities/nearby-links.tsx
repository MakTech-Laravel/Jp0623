import { Wrap } from '@/components/frontend/wrap';
import type { NavLink } from '@/types/frontend';

export function NearbyLinks({ items }: { items: NavLink[] }) {
    return (
        <Wrap className="pb-16 md:pb-18">
            <p className="mx-auto max-w-4xl text-brand-muted">
                Nearby:{' '}
                {items.map((item, index) => (
                    <span key={item.href}>
                        {index > 0 ? ' · ' : null}
                        <a
                            href={item.href}
                            className="text-brand-ink no-underline hover:underline hover:decoration-brass"
                        >
                            {item.label}
                        </a>
                    </span>
                ))}
            </p>
        </Wrap>
    );
}
