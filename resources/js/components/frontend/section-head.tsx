import type { ReactNode } from 'react';

type SectionHeadProps = {
    eyebrow?: string;
    title: string;
    text?: ReactNode;
};

export function SectionHead({ eyebrow, title, text }: SectionHeadProps) {
    return (
        <div className="mb-9 grid items-end gap-8 md:grid-cols-2">
            <div>
                {eyebrow ? (
                    <p className="font-mono text-xs tracking-widest text-brand-muted uppercase">
                        {eyebrow}
                    </p>
                ) : null}
                <h2 className="font-display text-3xl leading-tight font-extrabold tracking-tight text-brand-ink md:text-4xl lg:text-5xl">
                    {title}
                </h2>
            </div>
            {text ? (
                <div className="max-w-xl text-lg text-brand-muted">{text}</div>
            ) : (
                <span />
            )}
        </div>
    );
}
