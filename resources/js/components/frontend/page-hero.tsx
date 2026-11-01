import type { ReactNode } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { MachineArt } from '@/components/frontend/machine-art';
import { Wrap } from '@/components/frontend/wrap';
import type { NavLink } from '@/types/frontend';

type PageHeroProps = {
    eyebrow?: string;
    title: string;
    lead?: string;
    cta?: NavLink;
    art?: string;
    actions?: ReactNode;
};

export function PageHero({
    eyebrow,
    title,
    lead,
    cta,
    art,
    actions,
}: PageHeroProps) {
    return (
        <section className="bg-petrol py-12 text-on-petrol md:py-14">
            <Wrap className="grid items-center gap-10 md:grid-cols-[1.3fr_0.7fr]">
                <div>
                    {eyebrow ? (
                        <p className="font-mono text-xs tracking-widest text-brass uppercase">
                            {eyebrow}
                        </p>
                    ) : null}
                    <h1 className="mt-2 font-display text-4xl leading-tight font-extrabold tracking-tight md:text-5xl lg:text-6xl">
                        {title}
                    </h1>
                    {lead ? (
                        <p className="mt-3.5 max-w-xl text-lg text-on-petrol/90">
                            {lead}
                        </p>
                    ) : null}
                    {(cta || actions) && (
                        <div className="mt-5 flex flex-wrap gap-3">
                            {cta ? (
                                <BrandButton href={cta.href}>
                                    {cta.label}
                                </BrandButton>
                            ) : null}
                            {actions}
                        </div>
                    )}
                </div>
                {art ? (
                    <div className="w-full max-w-xs justify-self-center md:justify-self-end">
                        <MachineArt kind={art} />
                    </div>
                ) : null}
            </Wrap>
        </section>
    );
}
