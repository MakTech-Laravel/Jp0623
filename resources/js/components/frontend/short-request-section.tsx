import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Band } from '@/components/frontend/wrap';
import {
    LocationAutocomplete,
    type LocationSuggestion,
} from '@/components/location/location-autocomplete';
import type { RouteDefinition } from '@/wayfinder';

export type ShortRequestFormData = {
    title: string;
    text: string;
    successMessage: string;
    submitLabel: string;
    subjectLabel: string;
};

type ShortRequestFields = {
    business_name: string;
    zip_code: string;
    address: string | null;
    city: string;
    state: string;
    postal_code: string | null;
    latitude: number | null;
    longitude: number | null;
    contact_name: string;
    phone: string;
    email: string;
    notes: string;
    terms_accepted: boolean;
};

function sanitizePhone(value: string): string {
    return value.replace(/[^0-9+\-() ]/g, '');
}

function phoneDigitCount(value: string): number {
    return value.replace(/\D/g, '').length;
}

function FieldError({ message }: { message?: string }) {
    if (!message) {
        return null;
    }

    return <p className="text-sm font-medium text-bad">{message}</p>;
}

const emptyFields: ShortRequestFields = {
    business_name: '',
    zip_code: '',
    address: null,
    city: '',
    state: '',
    postal_code: null,
    latitude: null,
    longitude: null,
    contact_name: '',
    phone: '',
    email: '',
    notes: '',
    terms_accepted: false,
};

export function ShortRequestSection({
    data,
    id,
    alt = true,
    action,
}: {
    data: ShortRequestFormData;
    id?: string;
    alt?: boolean;
    action?: RouteDefinition<'post'>;
}) {
    const [localDone, setLocalDone] = useState(false);
    const [localErrors, setLocalErrors] = useState<Record<string, string>>({});
    const [localData, setLocalData] = useState<ShortRequestFields>(emptyFields);
    const [locationQuery, setLocationQuery] = useState('');
    const [selectedLocation, setSelectedLocation] =
        useState<LocationSuggestion | null>(null);

    const form = useForm(action ?? { url: '', method: 'post' }, emptyFields);

    const values = action ? form.data : localData;
    const errors = action ? form.errors : localErrors;
    const processing = action ? form.processing : false;
    const done = action ? form.wasSuccessful || localDone : localDone;

    function setField<K extends keyof ShortRequestFields>(
        key: K,
        value: ShortRequestFields[K],
    ) {
        if (action) {
            form.setData(key, value);
        } else {
            setLocalData((current) => ({ ...current, [key]: value }));
        }
    }

    function applyLocation(suggestion: LocationSuggestion | null) {
        setSelectedLocation(suggestion);

        setField('zip_code', suggestion?.postal_code ?? suggestion?.label ?? '');
        setField('address', suggestion?.address ?? null);
        setField('city', suggestion?.city ?? '');
        setField('state', suggestion?.state ?? '');
        setField('postal_code', suggestion?.postal_code ?? null);
        setField('latitude', suggestion?.latitude ?? null);
        setField('longitude', suggestion?.longitude ?? null);
    }

    function validate(): Record<string, string> {
        const nextErrors: Record<string, string> = {};

        if (!values.business_name.trim()) {
            nextErrors.business_name = 'Business name is required.';
        }

        if (!selectedLocation) {
            nextErrors.city = 'Select a valid address from the suggestions.';
        }

        if (!values.contact_name.trim()) {
            nextErrors.contact_name = 'Name is required.';
        }

        if (!values.phone.trim()) {
            nextErrors.phone = 'Phone is required.';
        } else {
            const digits = phoneDigitCount(values.phone);

            if (digits < 7 || digits > 15) {
                nextErrors.phone =
                    'Phone must contain between 7 and 15 digits.';
            }
        }

        if (!values.email.trim()) {
            nextErrors.email = 'Email is required.';
        }

        if (!values.terms_accepted) {
            nextErrors.terms_accepted = 'You must agree to the terms.';
        }

        return nextErrors;
    }

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const nextErrors = validate();

        if (Object.keys(nextErrors).length > 0) {
            if (action) {
                form.clearErrors();
                Object.entries(nextErrors).forEach(([key, message]) => {
                    form.setError(key as keyof ShortRequestFields, message);
                });
            } else {
                setLocalErrors(nextErrors);
            }

            return;
        }

        if (!action) {
            setLocalErrors({});
            setLocalDone(true);

            return;
        }

        form.submit({
            preserveScroll: true,
            onSuccess: () => {
                setLocalDone(true);
                form.reset();
                setLocationQuery('');
                setSelectedLocation(null);
            },
        });
    }

    const locationError =
        errors.city ??
        errors.state ??
        errors.latitude ??
        errors.longitude ??
        errors.zip_code;

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
                                    name="business_name"
                                    value={values.business_name}
                                    onChange={(event) =>
                                        setField(
                                            'business_name',
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                                <FieldError message={errors.business_name} />
                            </label>
                            <div className="flex flex-col gap-1 text-sm font-semibold">
                                <LocationAutocomplete
                                    label="Address"
                                    value={locationQuery}
                                    selected={selectedLocation}
                                    onQueryChange={setLocationQuery}
                                    onSelect={(suggestion) => {
                                        setLocationQuery(suggestion.label);
                                        applyLocation(suggestion);
                                    }}
                                    onClearSelection={() => applyLocation(null)}
                                    inputClassName="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                    error={locationError}
                                />
                            </div>
                            <label className="flex flex-col gap-1 text-sm font-semibold">
                                Your name
                                <input
                                    required
                                    name="contact_name"
                                    value={values.contact_name}
                                    onChange={(event) =>
                                        setField(
                                            'contact_name',
                                            event.target.value,
                                        )
                                    }
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                                <FieldError message={errors.contact_name} />
                            </label>
                            <label className="flex flex-col gap-1 text-sm font-semibold">
                                Phone
                                <input
                                    required
                                    name="phone"
                                    type="tel"
                                    inputMode="tel"
                                    value={values.phone}
                                    onChange={(event) =>
                                        setField(
                                            'phone',
                                            sanitizePhone(event.target.value),
                                        )
                                    }
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                                <FieldError message={errors.phone} />
                            </label>
                            <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                Email
                                <input
                                    type="email"
                                    required
                                    name="email"
                                    value={values.email}
                                    onChange={(event) =>
                                        setField('email', event.target.value)
                                    }
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                                <FieldError message={errors.email} />
                            </label>
                            <label className="col-span-full flex flex-col gap-1 text-sm font-semibold">
                                Anything else about your {data.subjectLabel}?
                                <textarea
                                    name="notes"
                                    rows={2}
                                    value={values.notes}
                                    onChange={(event) =>
                                        setField('notes', event.target.value)
                                    }
                                    className="rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink"
                                />
                                <FieldError message={errors.notes} />
                            </label>
                            <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                                <input
                                    type="checkbox"
                                    required
                                    name="terms_accepted"
                                    checked={values.terms_accepted}
                                    onChange={(event) =>
                                        setField(
                                            'terms_accepted',
                                            event.target.checked,
                                        )
                                    }
                                    className="mt-0.5 accent-brass"
                                />
                                I agree to the Location Terms and Privacy Policy
                                and to be contacted about my request.
                            </label>
                            <FieldError message={errors.terms_accepted} />
                            <div className="col-span-full">
                                <BrandButton
                                    as="button"
                                    type="submit"
                                    disabled={processing}
                                    className="w-full sm:w-auto"
                                >
                                    {processing
                                        ? 'Sending…'
                                        : data.submitLabel}
                                </BrandButton>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </Band>
    );
}
