import { useState } from 'react';
import type { FormEvent } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Wrap } from '@/components/frontend/wrap';
import type { RequestFormData } from '@/types/frontend';
import { cn } from '@/lib/utils';

const STEPS = ['Location', 'Details', 'Machines', 'Contact'] as const;

export function RequestSection({ data }: { data: RequestFormData }) {
    const [step, setStep] = useState(0);
    const [done, setDone] = useState(false);
    const [locationType, setLocationType] = useState(
        data.locationTypes[0] ?? '',
    );
    const [interests, setInterests] = useState<string[]>(
        data.machineInterests.slice(0, 2),
    );
    const [placement, setPlacement] = useState('Inside');
    const [foodOnSite, setFoodOnSite] = useState(data.yesNoOptions[1] ?? 'No');
    const [comparingQuotes, setComparingQuotes] = useState(
        data.yesNoOptions[1] ?? 'No',
    );
    const [existingVending, setExistingVending] = useState(
        data.existingVending[0] ?? 'No',
    );

    const isSwitchingProvider = existingVending !== 'No';

    function toggleInterest(value: string) {
        setInterests((current) =>
            current.includes(value)
                ? current.filter((item) => item !== value)
                : [...current, value],
        );
    }

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setDone(true);
    }

    return (
        <section id="request-sec" className="py-16 md:py-18">
            <Wrap className="grid items-start gap-10 md:grid-cols-2">
                <div>
                    <p className="font-mono text-xs tracking-widest text-brand-muted uppercase">
                        {data.eyebrow}
                    </p>
                    <h2 className="mt-1 font-display text-3xl leading-tight font-extrabold tracking-tight text-brand-ink md:text-4xl lg:text-5xl">
                        {data.title}
                    </h2>
                    <p className="mt-3 text-brand-muted">{data.text}</p>
                </div>

                <div className="min-w-0 rounded-2xl border border-brand-line bg-brand-surface p-6 md:p-7">
                    {done ? (
                        <div className="rounded-lg bg-good-bg p-3.5 font-semibold text-good">
                            {data.successMessage}
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit}>
                            <div className="mb-5 flex flex-wrap gap-1.5">
                                {STEPS.map((label, index) => (
                                    <span
                                        key={label}
                                        className={cn(
                                            'rounded px-2.5 py-1 font-mono text-xs font-semibold',
                                            index === step &&
                                                'bg-brass text-brass-ink',
                                            index < step &&
                                                'bg-brand-ink text-brand-bg',
                                            index > step &&
                                                'bg-brand-sunk text-brand-muted',
                                        )}
                                    >
                                        {index + 1}. {label}
                                    </span>
                                ))}
                            </div>

                            {step === 0 ? (
                                <div className="grid gap-3">
                                    <fieldset className="m-0 border-0 p-0">
                                        <legend className="mb-1.5 text-sm font-semibold">
                                            Type of location
                                        </legend>
                                        <div className="flex flex-wrap gap-2">
                                            {data.locationTypes.map((type) => (
                                                <label
                                                    key={type}
                                                    className={cn(
                                                        'inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-brand-line bg-brand-surface px-3.5 py-2 text-sm font-medium',
                                                        locationType === type &&
                                                            'border-brand-ink bg-brand-ink text-brand-bg',
                                                    )}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="loc"
                                                        className="sr-only"
                                                        checked={
                                                            locationType ===
                                                            type
                                                        }
                                                        onChange={() =>
                                                            setLocationType(
                                                                type,
                                                            )
                                                        }
                                                    />
                                                    {type}
                                                </label>
                                            ))}
                                        </div>
                                    </fieldset>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Business name
                                        <input
                                            required
                                            placeholder="e.g. Riverside Logistics"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Address or ZIP
                                        <input
                                            required
                                            placeholder="e.g. 77002"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                </div>
                            ) : null}

                            {step === 1 ? (
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Employees on site
                                        <input
                                            inputMode="numeric"
                                            placeholder="e.g. 60"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Daily visitors / traffic
                                        <input
                                            inputMode="numeric"
                                            placeholder="e.g. 150"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <fieldset className="col-span-full m-0 border-0 p-0">
                                        <legend className="mb-1.5 text-sm font-semibold">
                                            Where will the machine go?
                                        </legend>
                                        <div className="flex flex-wrap gap-2">
                                            {[
                                                'Inside',
                                                'Outside (covered)',
                                            ].map((option) => (
                                                <label
                                                    key={option}
                                                    className={cn(
                                                        'inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-brand-line bg-brand-surface px-3.5 py-2 text-sm font-medium',
                                                        placement === option &&
                                                            'border-brand-ink bg-brand-ink text-brand-bg',
                                                    )}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="io"
                                                        className="sr-only"
                                                        checked={
                                                            placement === option
                                                        }
                                                        onChange={() =>
                                                            setPlacement(option)
                                                        }
                                                    />
                                                    {option}
                                                </label>
                                            ))}
                                        </div>
                                    </fieldset>
                                    <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                        Tell us about the space
                                        <textarea
                                            rows={2}
                                            placeholder="e.g. Break room, two shifts, outlet available"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Are there food options on site?
                                        <select
                                            value={foodOnSite}
                                            onChange={(event) =>
                                                setFoodOnSite(event.target.value)
                                            }
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        >
                                            {data.yesNoOptions.map((option) => (
                                                <option key={option}>
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Are you comparing quotes?
                                        <select
                                            value={comparingQuotes}
                                            onChange={(event) =>
                                                setComparingQuotes(
                                                    event.target.value,
                                                )
                                            }
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        >
                                            {data.yesNoOptions.map((option) => (
                                                <option key={option}>
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                </div>
                            ) : null}

                            {step === 2 ? (
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <fieldset className="col-span-full m-0 border-0 p-0">
                                        <legend className="mb-1.5 text-sm font-semibold">
                                            What would you like? (choose any)
                                        </legend>
                                        <div className="flex flex-wrap gap-2">
                                            {data.machineInterests.map(
                                                (interest) => (
                                                    <label
                                                        key={interest}
                                                        className={cn(
                                                            'inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-brand-line bg-brand-surface px-3.5 py-2 text-sm font-medium',
                                                            interests.includes(
                                                                interest,
                                                            ) &&
                                                                'border-brand-ink bg-brand-ink text-brand-bg',
                                                        )}
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            className="sr-only"
                                                            checked={interests.includes(
                                                                interest,
                                                            )}
                                                            onChange={() =>
                                                                toggleInterest(
                                                                    interest,
                                                                )
                                                            }
                                                        />
                                                        {interest}
                                                    </label>
                                                ),
                                            )}
                                        </div>
                                    </fieldset>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        How many machines?
                                        <select className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink">
                                            {data.machineCounts.map((count) => (
                                                <option key={count}>
                                                    {count}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Do you have vending now?
                                        <select
                                            value={existingVending}
                                            onChange={(event) =>
                                                setExistingVending(
                                                    event.target.value,
                                                )
                                            }
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        >
                                            {data.existingVending.map(
                                                (option) => (
                                                    <option key={option}>
                                                        {option}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                    </label>
                                    {isSwitchingProvider ? (
                                        <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                            Why are you changing vending
                                            providers?
                                            <textarea
                                                rows={2}
                                                required
                                                placeholder="Tell us briefly why you are switching"
                                                className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                            />
                                        </label>
                                    ) : null}
                                </div>
                            ) : null}

                            {step === 3 ? (
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Your name
                                        <input
                                            required
                                            placeholder="First and last name"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Job title
                                        <input
                                            placeholder="e.g. Office manager"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Email
                                        <input
                                            type="email"
                                            required
                                            placeholder="you@company.com"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Phone
                                        <input
                                            inputMode="tel"
                                            required
                                            placeholder="(555) 555-5555"
                                            className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                        />
                                    </label>
                                    <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                                        <input
                                            type="checkbox"
                                            required
                                            className="mt-0.5 accent-brass"
                                        />
                                        I agree to the Location Terms and
                                        Privacy Policy and to be contacted about
                                        my request.
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
                                </div>
                            ) : null}

                            <div className="mt-5 flex gap-2.5">
                                {step > 0 ? (
                                    <BrandButton
                                        as="button"
                                        variant="ghost"
                                        type="button"
                                        onClick={() =>
                                            setStep((value) => value - 1)
                                        }
                                    >
                                        Back
                                    </BrandButton>
                                ) : null}
                                {step < STEPS.length - 1 ? (
                                    <BrandButton
                                        as="button"
                                        type="button"
                                        onClick={() =>
                                            setStep((value) => value + 1)
                                        }
                                    >
                                        Next
                                    </BrandButton>
                                ) : (
                                    <BrandButton as="button" type="submit">
                                        {data.submitLabel}
                                    </BrandButton>
                                )}
                            </div>
                        </form>
                    )}
                </div>
            </Wrap>
        </section>
    );
}
