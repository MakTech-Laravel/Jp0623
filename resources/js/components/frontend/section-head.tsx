import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type SectionHeadProps = {
    eyebrow?: string;
    title: string;
    text?: ReactNode;
    action?: ReactNode;
    align?: 'start' | 'center';
};

export function SectionHead({
    eyebrow,
    title,
    text,
    action,
    align = 'start',
}: SectionHeadProps) {
    const sideOffset = Boolean(eyebrow);
    const centered = align === 'center';

    return (
        <div
            className={cn(
                'mb-9 flex flex-col gap-4 md:gap-8',
                centered
                    ? 'items-center text-center'
                    : 'md:flex-row md:items-start md:justify-between',
            )}
        >
            <div className={cn('min-w-0', !centered && 'flex-1')}>
                {eyebrow ? (
                    <p className="font-mono text-xs tracking-widest text-brand-muted uppercase">
                        {eyebrow}
                    </p>
                ) : null}
                <h2 className="font-display text-3xl leading-snug font-extrabold tracking-tight text-brand-ink md:text-4xl lg:text-5xl">
                    {title}
                </h2>
            </div>
            {action ? (
                <div
                    className={cn(
                        'shrink-0 whitespace-nowrap',
                        !centered && 'md:text-right',
                        sideOffset && !centered && 'md:pt-5',
                    )}
                >
                    {action}
                </div>
            ) : null}
            {!action && text ? (
                <div
                    className={cn(
                        'max-w-xl text-lg text-brand-muted',
                        sideOffset && !centered && 'md:pt-5',
                    )}
                >
                    {text}
                </div>
            ) : null}
        </div>
    );
}
