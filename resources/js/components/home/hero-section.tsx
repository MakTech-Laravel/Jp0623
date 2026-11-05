import { BrandButton } from '@/components/frontend/brand-button';
import {
    CatalogImage,
    MACHINE_IMAGE_FALLBACK,
} from '@/components/frontend/catalog-image';
import { Wrap } from '@/components/frontend/wrap';
import type { HeroData } from '@/types/frontend';
import { cn } from '@/lib/utils';
import { useReducedMotion } from 'motion/react';
import { useEffect, useState } from 'react';

const CYCLE_MS = 4000;

type HeroImage = HeroData['images'][number];

function HeroImageFrame({
    images,
    activeIndex,
    offset,
    reduceMotion,
    className,
}: {
    images: HeroImage[];
    activeIndex: number;
    offset: number;
    reduceMotion: boolean | null;
    className?: string;
}) {
    const visibleIndex =
        images.length === 0 ? 0 : (activeIndex + offset) % images.length;

    return (
        <div
            className={cn(
                'group relative flex min-h-80 items-end justify-center transition duration-500 md:min-h-[28rem]',
                !reduceMotion &&
                    'hover:scale-[1.03] hover:shadow-brass-glow',
                className,
            )}
        >
            {images.map((image, index) => {
                const isVisible = index === visibleIndex;

                return (
                    <div
                        key={image.src}
                        className={cn(
                            'absolute inset-0 flex items-end justify-center',
                            !reduceMotion &&
                                'transition-opacity duration-1000 ease-in-out',
                            isVisible ? 'opacity-100' : 'opacity-0',
                        )}
                        aria-hidden={!isVisible}
                    >
                        <CatalogImage
                            src={image.src}
                            alt={image.alt}
                            fallback={MACHINE_IMAGE_FALLBACK}
                            className="max-h-80 w-full md:max-h-[28rem]"
                        />
                    </div>
                );
            })}
        </div>
    );
}

export function HeroSection({ hero }: { hero: HeroData }) {
    const reduceMotion = useReducedMotion();
    const images = hero.images;
    const [activeIndex, setActiveIndex] = useState(0);

    useEffect(() => {
        if (reduceMotion || images.length < 3) {
            return;
        }

        const timer = window.setInterval(() => {
            setActiveIndex((current) => (current + 1) % images.length);
        }, CYCLE_MS);

        return () => window.clearInterval(timer);
    }, [images.length, reduceMotion]);

    return (
        <section className="bg-petrol py-14 text-on-petrol md:py-16">
            <Wrap className="grid items-center gap-10 md:grid-cols-2">
                <div>
                    <p className="font-mono text-xs tracking-widest text-brass uppercase">
                        {hero.eyebrow}
                    </p>
                    <h1 className="mt-2 font-display text-4xl leading-tight font-extrabold tracking-tight md:text-5xl lg:text-6xl xl:text-7xl">
                        {hero.titleBefore}
                        <em className="not-italic text-brass">
                            {hero.titleAccent}
                        </em>
                    </h1>
                    <p className="mt-4 max-w-xl text-lg text-on-petrol/90 md:text-xl">
                        {hero.lede}
                    </p>
                    <ul className="mt-5 flex list-none flex-col gap-2 p-0">
                        {hero.bullets.map((bullet) => (
                            <li key={bullet} className="flex gap-2">
                                <span className="font-bold text-brass">✓</span>
                                <span>{bullet}</span>
                            </li>
                        ))}
                    </ul>
                    <div className="mt-6 flex flex-wrap items-center gap-3">
                        <BrandButton href={hero.cta.href}>
                            {hero.cta.label}
                        </BrandButton>
                    </div>
                    <p className="mt-4 text-sm text-on-petrol/90">
                        <b className="text-brass">{hero.trust.stars}</b>{' '}
                        {hero.trust.text}{' '}
                        <a
                            href={hero.trust.reviewsHref}
                            className="underline decoration-brass underline-offset-2"
                        >
                            {hero.trust.reviewsLabel}
                        </a>
                    </p>
                </div>
                <div className="grid w-full max-w-2xl grid-cols-2 items-end gap-5 justify-self-center md:justify-self-end">
                    <HeroImageFrame
                        images={images}
                        activeIndex={activeIndex}
                        offset={0}
                        reduceMotion={reduceMotion}
                    />
                    <HeroImageFrame
                        images={images}
                        activeIndex={activeIndex}
                        offset={1}
                        reduceMotion={reduceMotion}
                        className="mb-6 md:mb-10"
                    />
                </div>
            </Wrap>
        </section>
    );
}
