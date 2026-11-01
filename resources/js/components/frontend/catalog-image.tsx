import { useState } from 'react';
import { cn } from '@/lib/utils';

export const MACHINE_IMAGE_FALLBACK = '/images/machines/Alpine-ST5000.jpg';
export const PRODUCT_IMAGE_FALLBACK = '/images/products/canned-regular-cola.jpg';

export function machineImageSrc(
    kind: string,
    image?: string | null,
): string {
    return image || MACHINE_IMAGE_FALLBACK;
}

export function productImageSrc(
    kind: string,
    image?: string | null,
): string {
    return image || PRODUCT_IMAGE_FALLBACK;
}

type CatalogImageProps = {
    src: string;
    alt: string;
    fallback: string;
    className?: string;
};

export function CatalogImage({
    src,
    alt,
    fallback,
    className,
}: CatalogImageProps) {
    const [current, setCurrent] = useState(src || fallback);

    return (
        <img
            src={current}
            alt={alt}
            loading="lazy"
            decoding="async"
            onError={() => {
                if (current !== fallback) {
                    setCurrent(fallback);
                }
            }}
            className={cn('h-auto max-h-44 w-auto max-w-full object-contain', className)}
        />
    );
}
