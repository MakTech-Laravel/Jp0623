import { BrandButton } from '@/components/frontend/brand-button';
import {
    CatalogImage,
    MACHINE_IMAGE_FALLBACK,
} from '@/components/frontend/catalog-image';
import { Wrap } from '@/components/frontend/wrap';
import type { HeroData } from '@/types/frontend';

export function HeroSection({
    hero,
    phone,
}: {
    hero: HeroData;
    phone: string;
}) {
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
                        <span className="font-mono text-sm font-semibold">
                            {hero.phoneLabel} {phone}
                        </span>
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
                <div className="grid max-w-md grid-cols-2 items-end gap-4 justify-self-center md:justify-self-end">
                    {hero.images.map((image) => (
                        <CatalogImage
                            key={image.src}
                            src={image.src}
                            alt={image.alt}
                            fallback={MACHINE_IMAGE_FALLBACK}
                            className="max-h-72 w-full"
                        />
                    ))}
                </div>
            </Wrap>
        </section>
    );
}
