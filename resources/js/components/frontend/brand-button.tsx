import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

type Variant = 'brass' | 'ghost' | 'dark';
type Size = 'md' | 'sm';

type BrandButtonProps = ComponentProps<'a'> &
    ComponentProps<'button'> & {
        variant?: Variant;
        size?: Size;
        as?: 'a' | 'button';
    };

const variantClasses: Record<Variant, string> = {
    brass: 'border-0 bg-brass text-brass-ink hover:brightness-105',
    ghost: 'border border-current bg-transparent text-inherit',
    dark: 'border-0 bg-brand-ink text-brand-bg',
};

const sizeClasses: Record<Size, string> = {
    md: 'px-5 py-3 text-sm',
    sm: 'px-3 py-1.5 text-sm',
};

export function BrandButton({
    as = 'a',
    variant = 'brass',
    size = 'md',
    className,
    children,
    ...props
}: BrandButtonProps) {
    const classes = cn(
        'inline-flex items-center justify-center gap-2 rounded-full font-semibold whitespace-nowrap no-underline transition',
        variantClasses[variant],
        sizeClasses[size],
        className,
    );

    if (as === 'button') {
        const buttonProps = props as ComponentProps<'button'>;

        return (
            <button
                className={classes}
                {...buttonProps}
                type={buttonProps.type ?? 'button'}
            >
                {children}
            </button>
        );
    }

    return (
        <a className={classes} {...(props as ComponentProps<'a'>)}>
            {children}
        </a>
    );
}
