import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function Wrap({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('container mx-auto px-4 sm:px-5', className)}>
            {children}
        </div>
    );
}

export function Band({
    children,
    alt = false,
    id,
    className,
}: {
    children: ReactNode;
    alt?: boolean;
    id?: string;
    className?: string;
}) {
    return (
        <section
            id={id}
            className={cn(
                'py-16 md:py-18',
                alt && 'border-y border-brand-line bg-brand-surface',
                className,
            )}
        >
            <Wrap>{children}</Wrap>
        </section>
    );
}
