import { useState } from 'react';
import type { FormEvent } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Band } from '@/components/frontend/wrap';

export type ShortRequestFormData = {
    title: string;
    text: string;
    successMessage: string;
    submitLabel: string;
    subjectLabel: string;
};

export function ShortRequestSection({
    data,
    id,
    alt = true,
}: {
    data: ShortRequestFormData;
    id?: string;
    alt?: boolean;
}) {
    const [done, setDone] = useState(false);

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setDone(true);
    }

    return (
        <Band alt={alt} id={id}>
            <div className="grid items-start gap-10 md:grid-cols-2">
                <div>
                    <h2 className="font-display text-3xl leading-tight font-extrabold tracking-tight text-brand-ink md:text-4xl">
                        {data.title}
                    </h2>
                    <p className="mt-2.5 text-brand-muted">{data.text}</p>
                </div>

                <div className="min-w-0 rounded-2xl border border-brand-line bg-brand-surface p-6 md:p-7">
                    {done ? (
                        <div className="rounded-lg bg-good-bg p-3.5 font-semibold text-good">
                            {data.successMessage}
                        </div>
                    ) : (
                        <form
                            onSubmit={handleSubmit}
                            className="grid gap-3 sm:grid-cols-2"
                        >
                            <label className="flex flex-col gap-1 text-sm font-semibold">
                                Business name
                                <input
                                    required
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                            </label>
                            <label className="flex flex-col gap-1 text-sm font-semibold">
                                ZIP code
                                <input
                                    required
                                    inputMode="numeric"
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                            </label>
                            <label className="flex flex-col gap-1 text-sm font-semibold">
                                Your name
                                <input
                                    required
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                            </label>
                            <label className="flex flex-col gap-1 text-sm font-semibold">
                                Phone
                                <input
                                    required
                                    inputMode="tel"
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                            </label>
                            <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                Email
                                <input
                                    type="email"
                                    required
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                            </label>
                            <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                Anything else about your {data.subjectLabel}?
                                <textarea
                                    rows={2}
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                            </label>
                            <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                                <input
                                    type="checkbox"
                                    required
                                    className="mt-0.5 accent-brass"
                                />
                                I agree to the Location Terms and Privacy Policy
                                and to be contacted about my request.
                            </label>
                            <div className="col-span-full flex max-w-xs items-center gap-2.5 rounded-lg border border-brand-line bg-brand-sunk p-3 text-sm">
                                <input
                                    type="checkbox"
                                    required
                                    aria-label="reCAPTCHA check"
                                    className="accent-brass"
                                />
                                reCAPTCHA check goes here
                            </div>
                            <div className="col-span-full">
                                <BrandButton
                                    as="button"
                                    type="submit"
                                    className="w-full sm:w-auto"
                                >
                                    {data.submitLabel}
                                </BrandButton>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </Band>
    );
}
