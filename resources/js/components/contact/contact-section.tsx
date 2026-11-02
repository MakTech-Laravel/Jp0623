import { useState } from 'react';
import type { FormEvent } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Reveal } from '@/components/frontend/reveal';
import { Band } from '@/components/frontend/wrap';
import type { ContactFormData, ContactPanelData } from '@/types/frontend';

export function ContactSection({
    panel,
    form,
}: {
    panel: ContactPanelData;
    form: ContactFormData;
}) {
    const [done, setDone] = useState(false);

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setDone(true);
    }

    const [operatorPage, operatorLogin] = panel.operatorLinks;

    return (
        <Band>
            <div className="grid items-start gap-6 md:grid-cols-2 md:gap-8">
                <Reveal>
                    <div className="rounded-brand border border-brand-line bg-brand-surface p-6 md:p-7">
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {panel.title}
                        </h3>
                        <p className="mt-3 text-sm leading-relaxed text-brand-muted">
                            Phone:{' '}
                            <a
                                href={panel.phoneHref}
                                className="font-mono font-semibold text-brand-ink no-underline hover:underline"
                            >
                                {panel.phone}
                            </a>
                            <br />
                            Email:{' '}
                            <a
                                href={panel.emailHref}
                                className="text-brand-ink no-underline hover:underline"
                            >
                                {panel.email}
                            </a>
                            <br />
                            {panel.hours}
                        </p>
                        <h3 className="mt-5 font-display text-xl font-bold text-brand-ink">
                            {panel.operatorTitle}
                        </h3>
                        <p className="mt-2 text-sm text-brand-muted">
                            Visit the{' '}
                            {operatorPage ? (
                                <a
                                    href={operatorPage.href}
                                    className="font-semibold text-brand-ink underline decoration-brass underline-offset-2"
                                >
                                    {operatorPage.label}
                                </a>
                            ) : null}{' '}
                            or{' '}
                            {operatorLogin ? (
                                <a
                                    href={operatorLogin.href}
                                    className="font-semibold text-brand-ink underline decoration-brass underline-offset-2"
                                >
                                    {operatorLogin.label}
                                </a>
                            ) : null}
                            .
                        </p>
                    </div>
                </Reveal>

                <Reveal delay={0.05}>
                    <div className="min-w-0 rounded-2xl border border-brand-line bg-brand-surface p-6 md:p-7">
                        {done ? (
                            <div className="rounded-lg bg-good-bg p-3.5 font-semibold text-good">
                                {form.successMessage}
                            </div>
                        ) : (
                            <form
                                onSubmit={handleSubmit}
                                className="grid gap-3 sm:grid-cols-2"
                            >
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Name
                                    <input
                                        required
                                        className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Company
                                    <input className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink" />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Email
                                    <input
                                        type="email"
                                        required
                                        className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Phone
                                    <input
                                        inputMode="tel"
                                        className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                    />
                                </label>
                                <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                    I&apos;m contacting about
                                    <select
                                        required
                                        className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        defaultValue={form.topics[0]}
                                    >
                                        {form.topics.map((topic) => (
                                            <option key={topic} value={topic}>
                                                {topic}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                    Message
                                    <textarea
                                        required
                                        rows={4}
                                        className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                    />
                                </label>
                                <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                                    <input
                                        type="checkbox"
                                        required
                                        className="mt-0.5 accent-brass"
                                    />
                                    I agree to the Location Terms and Privacy
                                    Policy and to be contacted about my request.
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
                                        className="w-full"
                                    >
                                        {form.submitLabel}
                                    </BrandButton>
                                </div>
                            </form>
                        )}
                    </div>
                </Reveal>
            </div>
        </Band>
    );
}
