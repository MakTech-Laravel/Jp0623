import { useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Reveal } from '@/components/frontend/reveal';
import { Band } from '@/components/frontend/wrap';
import { store as storeContactMessage } from '@/routes/contact';
import type { ContactFormData, ContactPanelData } from '@/types/frontend';

export function ContactSection({
    panel,
    form: formCopy,
}: {
    panel: ContactPanelData;
    form: ContactFormData;
}) {
    const defaultTopic = formCopy.topics[0] ?? '';
    const [done, setDone] = useState(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return new URLSearchParams(window.location.search).get('sent') === '1';
    });

    const form = useForm(storeContactMessage(), {
        name: '',
        company: '',
        email: '',
        phone: '',
        topic: defaultTopic,
        message: '',
        terms_accepted: false,
    });

    const fieldClass =
        'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink';

    const [operatorPage, operatorLogin] = panel.operatorLinks;

    const errorMessage = useMemo(() => {
        const first = Object.values(form.errors)[0];

        return typeof first === 'string' ? first : null;
    }, [form.errors]);

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.submit({
            preserveScroll: true,
            onSuccess: () => {
                setDone(true);
                form.reset();
                form.setData('topic', defaultTopic);
            },
        });
    }

    return (
        <Band>
            <div className="grid items-start gap-6 md:grid-cols-2 md:gap-8">
                <Reveal>
                    <div className="rounded-brand border border-brand-line bg-brand-surface p-6 md:p-7">
                        <h3 className="font-display text-xl font-bold text-brand-ink">
                            {panel.title}
                        </h3>
                        <p className="mt-3 text-sm leading-relaxed text-brand-muted">
                            Email:{' '}
                            <a
                                href={panel.emailHref}
                                className="text-brand-ink no-underline hover:underline"
                            >
                                {panel.email}
                            </a>
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
                                {formCopy.successMessage}
                            </div>
                        ) : (
                            <form
                                onSubmit={handleSubmit}
                                className="grid gap-3 sm:grid-cols-2"
                                noValidate
                            >
                                {errorMessage ? (
                                    <p className="col-span-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                        {errorMessage}
                                    </p>
                                ) : null}

                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Name
                                    <input
                                        required
                                        value={form.data.name}
                                        onChange={(e) =>
                                            form.setData('name', e.target.value)
                                        }
                                        className={fieldClass}
                                        aria-invalid={Boolean(form.errors.name)}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Company
                                    <input
                                        value={form.data.company}
                                        onChange={(e) =>
                                            form.setData(
                                                'company',
                                                e.target.value,
                                            )
                                        }
                                        className={fieldClass}
                                        aria-invalid={Boolean(
                                            form.errors.company,
                                        )}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Email
                                    <input
                                        type="email"
                                        required
                                        value={form.data.email}
                                        onChange={(e) =>
                                            form.setData(
                                                'email',
                                                e.target.value,
                                            )
                                        }
                                        className={fieldClass}
                                        aria-invalid={Boolean(
                                            form.errors.email,
                                        )}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold">
                                    Phone
                                    <input
                                        inputMode="tel"
                                        value={form.data.phone}
                                        onChange={(e) =>
                                            form.setData(
                                                'phone',
                                                e.target.value,
                                            )
                                        }
                                        className={fieldClass}
                                        aria-invalid={Boolean(
                                            form.errors.phone,
                                        )}
                                    />
                                </label>
                                <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                    I&apos;m contacting about
                                    <select
                                        required
                                        value={form.data.topic}
                                        onChange={(e) =>
                                            form.setData(
                                                'topic',
                                                e.target.value,
                                            )
                                        }
                                        className={fieldClass}
                                        aria-invalid={Boolean(
                                            form.errors.topic,
                                        )}
                                    >
                                        {formCopy.topics.map((topic) => (
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
                                        value={form.data.message}
                                        onChange={(e) =>
                                            form.setData(
                                                'message',
                                                e.target.value,
                                            )
                                        }
                                        className={fieldClass}
                                        aria-invalid={Boolean(
                                            form.errors.message,
                                        )}
                                    />
                                </label>
                                <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                                    <input
                                        type="checkbox"
                                        required
                                        checked={form.data.terms_accepted}
                                        onChange={(e) =>
                                            form.setData(
                                                'terms_accepted',
                                                e.target.checked,
                                            )
                                        }
                                        className="mt-0.5 accent-brass"
                                        aria-invalid={Boolean(
                                            form.errors.terms_accepted,
                                        )}
                                    />
                                    I agree to the Location Terms and Privacy
                                    Policy and to be contacted about my request.
                                </label>
                                <div className="col-span-full">
                                    <BrandButton
                                        as="button"
                                        type="submit"
                                        className="w-full"
                                        disabled={form.processing}
                                    >
                                        {form.processing
                                            ? 'Sending…'
                                            : formCopy.submitLabel}
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
