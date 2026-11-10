import { useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Wrap } from '@/components/frontend/wrap';
import {
    LocationAutocomplete,
    type LocationSuggestion,
} from '@/components/location/location-autocomplete';
import { store as storeVendingRequest } from '@/routes/vending-requests';
import type { RequestFormData } from '@/types/frontend';
import { cn } from '@/lib/utils';

const STEPS = ['Location', 'Details', 'Machines', 'Contact'] as const;

const PLACEMENTS = ['Inside', 'Outside (covered)'] as const;

const STEP_FIELDS: Record<number, string[]> = {
    0: [
        'location_type',
        'business_name',
        'address_or_zip',
        'address',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
    ],
    1: [
        'employees_on_site',
        'daily_visitors',
        'placement',
        'space_notes',
        'food_on_site',
        'comparing_quotes',
    ],
    2: [
        'machine_interests',
        'machine_count',
        'existing_vending',
        'switch_reason',
    ],
    3: [
        'contact_name',
        'job_title',
        'email',
        'phone',
        'terms_accepted',
    ],
};

function FieldError({ message }: { message?: string }) {
    if (!message) {
        return null;
    }

    return <p className="text-sm font-medium text-bad">{message}</p>;
}

function sanitizePhone(value: string): string {
    return value.replace(/[^0-9+\-() ]/g, '');
}

function phoneDigitCount(value: string): number {
    return value.replace(/\D/g, '').length;
}

export function RequestSection({ data }: { data: RequestFormData }) {
    const [step, setStep] = useState(0);
    const [done, setDone] = useState(false);
    const [stepErrors, setStepErrors] = useState<Record<string, string>>({});
    const [locationQuery, setLocationQuery] = useState('');
    const [selectedLocation, setSelectedLocation] =
        useState<LocationSuggestion | null>(null);

    const form = useForm(storeVendingRequest(), {
        location_type: data.locationTypes[0] ?? '',
        business_name: '',
        address_or_zip: '',
        address: null as string | null,
        city: '',
        state: '',
        postal_code: null as string | null,
        latitude: null as number | null,
        longitude: null as number | null,
        employees_on_site: '',
        daily_visitors: '',
        placement: 'Inside' as string,
        space_notes: '',
        food_on_site: data.yesNoOptions[1] ?? 'No',
        comparing_quotes: data.yesNoOptions[1] ?? 'No',
        machine_interests: data.machineInterests.slice(0, 2) as string[],
        machine_count: data.machineCounts[0] ?? '1',
        existing_vending: data.existingVending[0] ?? 'No',
        switch_reason: '',
        contact_name: '',
        job_title: '',
        email: '',
        phone: '',
        terms_accepted: false,
    });

    function applyLocation(suggestion: LocationSuggestion | null) {
        setSelectedLocation(suggestion);

        form.setData('address_or_zip', suggestion?.label ?? '');
        form.setData('address', suggestion?.address ?? null);
        form.setData('city', suggestion?.city ?? '');
        form.setData('state', suggestion?.state ?? '');
        form.setData('postal_code', suggestion?.postal_code ?? null);
        form.setData('latitude', suggestion?.latitude ?? null);
        form.setData('longitude', suggestion?.longitude ?? null);
    }

    const isSwitchingProvider = form.data.existing_vending !== 'No';

    useEffect(() => {
        const keys = Object.keys(form.errors);

        if (keys.length === 0) {
            return;
        }

        const firstStep = [0, 1, 2, 3].find((index) =>
            (STEP_FIELDS[index] ?? []).some((field) => keys.includes(field)),
        );

        if (firstStep !== undefined) {
            setStep(firstStep);
        }
    }, [form.errors]);

    function toggleInterest(value: string) {
        const current = form.data.machine_interests;
        form.setData(
            'machine_interests',
            current.includes(value)
                ? current.filter((item) => item !== value)
                : [...current, value],
        );
    }

    function validateStep(currentStep: number): boolean {
        const errors: Record<string, string> = {};

        if (currentStep === 0) {
            if (!form.data.location_type) {
                errors.location_type = 'Select a location type.';
            }
            if (!form.data.business_name.trim()) {
                errors.business_name = 'Business name is required.';
            }
            if (!selectedLocation) {
                errors.city = 'Select a valid address from the suggestions.';
            }
        }

        if (currentStep === 2) {
            if (form.data.machine_interests.length === 0) {
                errors.machine_interests = 'Select at least one interest.';
            }
            if (
                form.data.existing_vending !== 'No' &&
                !form.data.switch_reason.trim()
            ) {
                errors.switch_reason = 'Please tell us why you are switching.';
            }
        }

        if (currentStep === 3) {
            if (!form.data.contact_name.trim()) {
                errors.contact_name = 'Name is required.';
            }
            if (!form.data.email.trim()) {
                errors.email = 'Email is required.';
            }
            if (!form.data.phone.trim()) {
                errors.phone = 'Phone is required.';
            } else {
                const digits = phoneDigitCount(form.data.phone);

                if (digits < 7 || digits > 15) {
                    errors.phone = 'Phone must contain between 7 and 15 digits.';
                }
            }
            if (!form.data.terms_accepted) {
                errors.terms_accepted = 'You must agree to the terms.';
            }
        }

        setStepErrors(errors);

        return Object.keys(errors).length === 0;
    }

    function handleNext() {
        if (!validateStep(step)) {
            return;
        }

        setStepErrors({});
        setStep((value) => value + 1);
    }

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!validateStep(3)) {
            return;
        }

        form.submit({
            preserveScroll: true,
            onSuccess: () => {
                setDone(true);
                form.reset();
            },
        });
    }

    function errorFor(field: string): string | undefined {
        return stepErrors[field] ?? form.errors[field];
    }

    const fieldClass =
        'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink';

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
                                                        form.data
                                                            .location_type ===
                                                            type &&
                                                            'border-brand-ink bg-brand-ink text-brand-bg',
                                                    )}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="location_type"
                                                        className="sr-only"
                                                        checked={
                                                            form.data
                                                                .location_type ===
                                                            type
                                                        }
                                                        onChange={() =>
                                                            form.setData(
                                                                'location_type',
                                                                type,
                                                            )
                                                        }
                                                    />
                                                    {type}
                                                </label>
                                            ))}
                                        </div>
                                        <FieldError
                                            message={errorFor('location_type')}
                                        />
                                    </fieldset>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Business name
                                        <input
                                            value={form.data.business_name}
                                            onChange={(e) =>
                                                form.setData(
                                                    'business_name',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="e.g. Riverside Logistics"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('business_name')}
                                        />
                                    </label>
                                    <LocationAutocomplete
                                        label="Address"
                                        value={locationQuery}
                                        selected={selectedLocation}
                                        onQueryChange={setLocationQuery}
                                        onSelect={(suggestion) => {
                                            setLocationQuery(suggestion.label);
                                            applyLocation(suggestion);
                                            setStepErrors((current) => {
                                                const next = { ...current };
                                                delete next.city;
                                                delete next.state;
                                                delete next.latitude;
                                                delete next.longitude;
                                                delete next.address_or_zip;
                                                return next;
                                            });
                                        }}
                                        onClearSelection={() =>
                                            applyLocation(null)
                                        }
                                        inputClassName={fieldClass}
                                        error={
                                            errorFor('city') ??
                                            errorFor('state') ??
                                            errorFor('latitude') ??
                                            errorFor('longitude') ??
                                            errorFor('address_or_zip')
                                        }
                                    />
                                </div>
                            ) : null}

                            {step === 1 ? (
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Employees on site
                                        <input
                                            type="number"
                                            min={0}
                                            step={1}
                                            inputMode="numeric"
                                            value={form.data.employees_on_site}
                                            onChange={(e) =>
                                                form.setData(
                                                    'employees_on_site',
                                                    e.target.value === ''
                                                        ? ''
                                                        : Number(e.target.value),
                                                )
                                            }
                                            placeholder="e.g. 60"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor(
                                                'employees_on_site',
                                            )}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Daily visitors / traffic
                                        <input
                                            type="number"
                                            min={0}
                                            step={1}
                                            inputMode="numeric"
                                            value={form.data.daily_visitors}
                                            onChange={(e) =>
                                                form.setData(
                                                    'daily_visitors',
                                                    e.target.value === ''
                                                        ? ''
                                                        : Number(e.target.value),
                                                )
                                            }
                                            placeholder="e.g. 150"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('daily_visitors')}
                                        />
                                    </label>
                                    <fieldset className="col-span-full m-0 border-0 p-0">
                                        <legend className="mb-1.5 text-sm font-semibold">
                                            Where will the machine go?
                                        </legend>
                                        <div className="flex flex-wrap gap-2">
                                            {PLACEMENTS.map((option) => (
                                                <label
                                                    key={option}
                                                    className={cn(
                                                        'inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-brand-line bg-brand-surface px-3.5 py-2 text-sm font-medium',
                                                        form.data.placement ===
                                                            option &&
                                                            'border-brand-ink bg-brand-ink text-brand-bg',
                                                    )}
                                                >
                                                    <input
                                                        type="radio"
                                                        name="placement"
                                                        className="sr-only"
                                                        checked={
                                                            form.data
                                                                .placement ===
                                                            option
                                                        }
                                                        onChange={() =>
                                                            form.setData(
                                                                'placement',
                                                                option,
                                                            )
                                                        }
                                                    />
                                                    {option}
                                                </label>
                                            ))}
                                        </div>
                                        <FieldError
                                            message={errorFor('placement')}
                                        />
                                    </fieldset>
                                    <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                        Tell us about the space
                                        <textarea
                                            rows={2}
                                            value={form.data.space_notes}
                                            onChange={(e) =>
                                                form.setData(
                                                    'space_notes',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="e.g. Break room, two shifts, outlet available"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('space_notes')}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Are there food options on site?
                                        <select
                                            value={form.data.food_on_site}
                                            onChange={(event) =>
                                                form.setData(
                                                    'food_on_site',
                                                    event.target.value,
                                                )
                                            }
                                            className={fieldClass}
                                        >
                                            {data.yesNoOptions.map((option) => (
                                                <option key={option}>
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errorFor('food_on_site')}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Are you comparing quotes?
                                        <select
                                            value={form.data.comparing_quotes}
                                            onChange={(event) =>
                                                form.setData(
                                                    'comparing_quotes',
                                                    event.target.value,
                                                )
                                            }
                                            className={fieldClass}
                                        >
                                            {data.yesNoOptions.map((option) => (
                                                <option key={option}>
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errorFor(
                                                'comparing_quotes',
                                            )}
                                        />
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
                                                            form.data.machine_interests.includes(
                                                                interest,
                                                            ) &&
                                                                'border-brand-ink bg-brand-ink text-brand-bg',
                                                        )}
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            className="sr-only"
                                                            checked={form.data.machine_interests.includes(
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
                                        <FieldError
                                            message={errorFor(
                                                'machine_interests',
                                            )}
                                        />
                                    </fieldset>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        How many machines?
                                        <select
                                            value={form.data.machine_count}
                                            onChange={(e) =>
                                                form.setData(
                                                    'machine_count',
                                                    e.target.value,
                                                )
                                            }
                                            className={fieldClass}
                                        >
                                            {data.machineCounts.map((count) => (
                                                <option key={count}>
                                                    {count}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errorFor('machine_count')}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Do you have vending now?
                                        <select
                                            value={form.data.existing_vending}
                                            onChange={(event) =>
                                                form.setData(
                                                    'existing_vending',
                                                    event.target.value,
                                                )
                                            }
                                            className={fieldClass}
                                        >
                                            {data.existingVending.map(
                                                (option) => (
                                                    <option key={option}>
                                                        {option}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <FieldError
                                            message={errorFor(
                                                'existing_vending',
                                            )}
                                        />
                                    </label>
                                    {isSwitchingProvider ? (
                                        <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                            Why are you changing vending
                                            providers?
                                            <textarea
                                                rows={2}
                                                value={form.data.switch_reason}
                                                onChange={(e) =>
                                                    form.setData(
                                                        'switch_reason',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Tell us briefly why you are switching"
                                                className={fieldClass}
                                            />
                                            <FieldError
                                                message={errorFor(
                                                    'switch_reason',
                                                )}
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
                                            value={form.data.contact_name}
                                            onChange={(e) =>
                                                form.setData(
                                                    'contact_name',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="First and last name"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('contact_name')}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Job title
                                        <input
                                            value={form.data.job_title}
                                            onChange={(e) =>
                                                form.setData(
                                                    'job_title',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="e.g. Office manager"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('job_title')}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Email
                                        <input
                                            type="email"
                                            value={form.data.email}
                                            onChange={(e) =>
                                                form.setData(
                                                    'email',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="you@company.com"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('email')}
                                        />
                                    </label>
                                    <label className="flex flex-col gap-1 text-sm font-semibold">
                                        Phone
                                        <input
                                            type="tel"
                                            inputMode="tel"
                                            autoComplete="tel"
                                            value={form.data.phone}
                                            onChange={(e) =>
                                                form.setData(
                                                    'phone',
                                                    sanitizePhone(
                                                        e.target.value,
                                                    ),
                                                )
                                            }
                                            placeholder="(555) 555-5555"
                                            className={fieldClass}
                                        />
                                        <FieldError
                                            message={errorFor('phone')}
                                        />
                                    </label>
                                    <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                                        <input
                                            type="checkbox"
                                            checked={form.data.terms_accepted}
                                            onChange={(e) =>
                                                form.setData(
                                                    'terms_accepted',
                                                    e.target.checked,
                                                )
                                            }
                                            className="mt-0.5 accent-brass"
                                        />
                                        <span>
                                            I agree to the Location Terms and
                                            Privacy Policy and to be contacted
                                            about my request.
                                        </span>
                                    </label>
                                    <FieldError
                                        message={errorFor('terms_accepted')}
                                    />
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
                                        onClick={handleNext}
                                    >
                                        Next
                                    </BrandButton>
                                ) : (
                                    <BrandButton
                                        as="button"
                                        type="submit"
                                        disabled={form.processing}
                                    >
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
