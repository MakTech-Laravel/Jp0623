import { router } from '@inertiajs/react';
import { useRef, useState } from 'react';
import type { ClipboardEvent, KeyboardEvent } from 'react';
import InputError from '@/components/input-error';
import {
    LocationAutocomplete,
    type LocationSuggestion,
} from '@/components/location/location-autocomplete';
import PasswordInput from '@/components/password-input';
import { BrandButton } from '@/components/frontend/brand-button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function postJson(
    url: string,
    body: Record<string, unknown> = {},
): Promise<Record<string, unknown>> {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: JSON.stringify(body),
    });

    const payload = (await response.json().catch(() => null)) as {
        message?: string;
        errors?: Record<string, string[]>;
    } | null;

    if (!response.ok) {
        const errors = payload?.errors ?? {};
        const first =
            Object.values(errors).flat()[0] ??
            payload?.message ??
            'Request failed.';

        const error = new Error(first) as Error & {
            errors: Record<string, string[]>;
        };
        error.errors = errors;
        throw error;
    }

    return payload ?? {};
}

const fieldClass =
    'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink shadow-none focus-visible:border-brass focus-visible:ring-brass/30';

const STEPS = ['Machines', 'Area', 'Account', 'Verify'] as const;
const ACCOUNT_STEP = 2;
const VERIFY_STEP = 3;
const MACHINE_TYPES = [
    'Soda',
    'Snack',
    'Combo',
    'Healthy',
    'Micro market',
    'Coffee',
] as const;
const QUANTITIES = ['1', '2', '3', '4', '5', '6+'] as const;

type RegisterStepperProps = {
    passwordRules: string;
};

function OptLabel({
    children,
    checked,
    className,
}: {
    children: React.ReactNode;
    checked: boolean;
    className?: string;
}) {
    return (
        <label
            className={cn(
                'inline-flex cursor-pointer items-center gap-2 rounded-full border-[1.5px] border-brand-line bg-brand-surface px-3.5 py-2 text-sm font-medium',
                checked && 'border-brand-ink bg-brand-ink text-brand-bg',
                className,
            )}
        >
            {children}
        </label>
    );
}

function validateStep(root: HTMLElement | null): boolean {
    if (!root) {
        return true;
    }

    const fields = root.querySelectorAll<
        HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
    >('input, select, textarea');

    for (const field of fields) {
        if (!field.checkValidity()) {
            field.reportValidity();
            return false;
        }
    }

    return true;
}

export function RegisterStepper({ passwordRules }: RegisterStepperProps) {
    const [step, setStep] = useState(0);
    const [ownsMachines, setOwnsMachines] = useState<'Yes' | 'No'>('Yes');
    const [machineTypes, setMachineTypes] = useState<string[]>(['Combo']);
    const [quantity, setQuantity] = useState('1');
    const [locationQuery, setLocationQuery] = useState('');
    const [selectedLocation, setSelectedLocation] =
        useState<LocationSuggestion | null>(null);
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [company, setCompany] = useState('');
    const [phone, setPhone] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [agreed, setAgreed] = useState(false);
    const [emailOtp, setEmailOtp] = useState(['', '', '', '', '', '']);
    const [busy, setBusy] = useState(false);
    const [otpMessage, setOtpMessage] = useState<string | null>(null);
    const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
    const [formError, setFormError] = useState<string | null>(null);
    const stepRef = useRef<HTMLDivElement>(null);
    const otpRefs = useRef<Array<HTMLInputElement | null>>([]);

    function toggleMachineType(type: string) {
        setMachineTypes((current) =>
            current.includes(type)
                ? current.filter((item) => item !== type)
                : [...current, type],
        );
    }

    function mapErrors(errors: Record<string, string[]>) {
        const next: Record<string, string> = {};

        for (const [key, messages] of Object.entries(errors)) {
            if (messages[0]) {
                next[key] = messages[0];
            }
        }

        setFieldErrors(next);
    }

    async function createAccount(): Promise<boolean> {
        if (password !== passwordConfirmation) {
            setFieldErrors({
                password_confirmation: 'Passwords do not match.',
            });
            return false;
        }

        setBusy(true);
        setFormError(null);
        setFieldErrors({});

        try {
            if (!selectedLocation) {
                setFieldErrors({
                    city: 'Select a valid location from the suggestions.',
                });
                return false;
            }

            await postJson('/register/account', {
                first_name: firstName,
                last_name: lastName,
                email,
                password,
                password_confirmation: passwordConfirmation,
                company_name: company,
                phone,
                owns_machines: ownsMachines === 'Yes',
                machine_types: machineTypes,
                machines_needed: quantity,
                primary_city: `${selectedLocation.city}, ${selectedLocation.state}`,
                address: selectedLocation.address,
                city: selectedLocation.city,
                state: selectedLocation.state,
                postal_code: selectedLocation.postal_code,
                latitude: selectedLocation.latitude,
                longitude: selectedLocation.longitude,
                terms_accepted: agreed,
            });
            setOtpMessage('Verification code sent. Check your email.');
            return true;
        } catch (error) {
            const err = error as Error & {
                errors?: Record<string, string[]>;
            };

            if (err.errors) {
                mapErrors(err.errors);
            } else {
                setFormError(err.message);
            }

            return false;
        } finally {
            setBusy(false);
        }
    }

    async function verifyOtp(): Promise<boolean> {
        const code = emailOtp.join('');

        if (code.length !== 6) {
            setFieldErrors({ email_otp: 'Enter the 6-digit code.' });
            return false;
        }

        setBusy(true);
        setFormError(null);
        setFieldErrors({});

        try {
            await postJson('/register/otp/verify', { email_otp: code });
            return true;
        } catch (error) {
            const err = error as Error & {
                errors?: Record<string, string[]>;
            };

            if (err.errors) {
                mapErrors(err.errors);
            } else {
                setFieldErrors({ email_otp: err.message });
            }

            return false;
        } finally {
            setBusy(false);
        }
    }

    async function resendOtp() {
        setBusy(true);
        setFormError(null);

        try {
            await postJson('/register/otp', {});
            setOtpMessage('Verification code sent. Check your email.');
        } catch (error) {
            setFormError(
                error instanceof Error
                    ? error.message
                    : 'Could not send verification code.',
            );
        } finally {
            setBusy(false);
        }
    }

    async function goNext() {
        if (!validateStep(stepRef.current)) {
            return;
        }

        if (step === 0 && machineTypes.length === 0) {
            return;
        }

        if (step === 1 && !selectedLocation) {
            setFieldErrors({
                city: 'Select a valid location from the suggestions.',
            });
            return;
        }

        if (step === ACCOUNT_STEP) {
            if (!agreed) {
                return;
            }

            if (!phone.trim()) {
                setFieldErrors({ phone: 'Phone number is required.' });
                return;
            }

            const created = await createAccount();

            if (!created) {
                return;
            }

            setStep(VERIFY_STEP);
            return;
        }

        if (step === VERIFY_STEP) {
            const verified = await verifyOtp();

            if (!verified) {
                return;
            }

            router.visit('/dashboard');
            return;
        }

        setStep((current) => Math.min(current + 1, STEPS.length - 1));
    }

    function goBack() {
        if (step <= ACCOUNT_STEP) {
            setStep((current) => Math.max(current - 1, 0));
        }
    }

    function handleOtpChange(index: number, value: string) {
        const digit = value.replace(/\D/g, '').slice(-1);
        const next = [...emailOtp];
        next[index] = digit;
        setEmailOtp(next);

        if (digit && index < 5) {
            otpRefs.current[index + 1]?.focus();
        }
    }

    function handleOtpPaste(
        index: number,
        event: ClipboardEvent<HTMLInputElement>,
    ) {
        event.preventDefault();

        const digits = event.clipboardData
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, 6);

        if (digits.length === 0) {
            return;
        }

        const next = [...emailOtp];
        const start = digits.length === 6 ? 0 : index;

        for (
            let offset = 0;
            offset < digits.length && start + offset < 6;
            offset++
        ) {
            next[start + offset] = digits[offset] ?? '';
        }

        setEmailOtp(next);
        otpRefs.current[Math.min(start + digits.length, 5)]?.focus();
    }

    function handleOtpKeyDown(
        index: number,
        event: KeyboardEvent<HTMLInputElement>,
    ) {
        if (event.key === 'Backspace' && !emailOtp[index] && index > 0) {
            otpRefs.current[index - 1]?.focus();
        }
    }

    return (
        <div className="flex flex-col gap-5">
            <div className="flex flex-wrap gap-1.5">
                {STEPS.map((label, index) => (
                    <span
                        key={label}
                        className={cn(
                            'rounded px-2.5 py-1 font-mono text-xs font-semibold',
                            index === step && 'bg-brass text-brass-ink',
                            index < step && 'bg-brand-ink text-brand-bg',
                            index > step && 'bg-brand-sunk text-brand-muted',
                        )}
                    >
                        {index + 1}. {label}
                    </span>
                ))}
            </div>

            <div ref={stepRef}>
                {step === 0 ? (
                    <div className="grid gap-5">
                        <fieldset className="grid gap-2.5">
                            <legend className="mb-1 text-sm font-semibold text-brand-ink">
                                Do you currently own vending machines?
                            </legend>
                            <div className="flex flex-wrap gap-2">
                                {(['Yes', 'No'] as const).map((option) => (
                                    <OptLabel
                                        key={option}
                                        checked={ownsMachines === option}
                                    >
                                        <input
                                            type="radio"
                                            name="ownsMachines"
                                            value={option}
                                            checked={ownsMachines === option}
                                            onChange={() =>
                                                setOwnsMachines(option)
                                            }
                                            className="accent-brass"
                                        />
                                        {option}
                                    </OptLabel>
                                ))}
                            </div>
                        </fieldset>
                        <fieldset className="grid gap-2.5">
                            <legend className="mb-1 text-sm font-semibold text-brand-ink">
                                What type of machines? (select all)
                            </legend>
                            <div className="flex flex-wrap gap-2">
                                {MACHINE_TYPES.map((type) => (
                                    <OptLabel
                                        key={type}
                                        checked={machineTypes.includes(type)}
                                    >
                                        <input
                                            type="checkbox"
                                            checked={machineTypes.includes(
                                                type,
                                            )}
                                            onChange={() =>
                                                toggleMachineType(type)
                                            }
                                            className="accent-brass"
                                        />
                                        {type}
                                    </OptLabel>
                                ))}
                            </div>
                        </fieldset>
                    </div>
                ) : null}

                {step === 1 ? (
                    <div className="grid gap-5">
                        <fieldset className="grid gap-2.5">
                            <legend className="mb-1 text-sm font-semibold text-brand-ink">
                                How many machines need locations?
                            </legend>
                            <div className="flex flex-wrap gap-2">
                                {QUANTITIES.map((qty) => (
                                    <OptLabel
                                        key={qty}
                                        checked={quantity === qty}
                                    >
                                        <input
                                            type="radio"
                                            name="quantity"
                                            value={qty}
                                            checked={quantity === qty}
                                            onChange={() => setQuantity(qty)}
                                            className="accent-brass"
                                        />
                                        {qty}
                                    </OptLabel>
                                ))}
                            </div>
                        </fieldset>
                        <LocationAutocomplete
                            label="Primary city you want locations in"
                            value={locationQuery}
                            selected={selectedLocation}
                            onQueryChange={setLocationQuery}
                            onSelect={(suggestion) => {
                                setSelectedLocation(suggestion);
                                setLocationQuery(suggestion.label);
                                setFieldErrors((current) => {
                                    const next = { ...current };
                                    delete next.city;
                                    delete next.state;
                                    delete next.primary_city;
                                    delete next.latitude;
                                    delete next.longitude;
                                    return next;
                                });
                            }}
                            onClearSelection={() => setSelectedLocation(null)}
                            error={
                                fieldErrors.city ??
                                fieldErrors.state ??
                                fieldErrors.primary_city ??
                                fieldErrors.latitude ??
                                fieldErrors.longitude ??
                                null
                            }
                        />
                    </div>
                ) : null}

                {step === ACCOUNT_STEP ? (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            First name
                            <input
                                required
                                value={firstName}
                                onChange={(event) =>
                                    setFirstName(event.target.value)
                                }
                                autoComplete="given-name"
                                className={fieldClass}
                            />
                            <InputError message={fieldErrors.first_name} />
                        </label>
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Last name
                            <input
                                required
                                value={lastName}
                                onChange={(event) =>
                                    setLastName(event.target.value)
                                }
                                autoComplete="family-name"
                                className={fieldClass}
                            />
                            <InputError message={fieldErrors.last_name} />
                        </label>
                        <label className="col-span-full flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Company name
                            <input
                                required
                                value={company}
                                onChange={(event) =>
                                    setCompany(event.target.value)
                                }
                                autoComplete="organization"
                                className={fieldClass}
                            />
                            <InputError message={fieldErrors.company_name} />
                        </label>
                        <label className="col-span-full flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Phone number
                            <input
                                required
                                type="tel"
                                inputMode="tel"
                                value={phone}
                                onChange={(event) =>
                                    setPhone(
                                        event.target.value.replace(
                                            /[^\d+\-() ]/g,
                                            '',
                                        ),
                                    )
                                }
                                autoComplete="tel"
                                placeholder="(555) 123-4567"
                                className={fieldClass}
                            />
                            <InputError message={fieldErrors.phone} />
                        </label>
                        <label className="col-span-full flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Email
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(event) =>
                                    setEmail(event.target.value)
                                }
                                autoComplete="email"
                                className={fieldClass}
                            />
                            <InputError message={fieldErrors.email} />
                        </label>
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Create password
                            <PasswordInput
                                required
                                value={password}
                                onChange={(event) =>
                                    setPassword(event.target.value)
                                }
                                autoComplete="new-password"
                                passwordrules={passwordRules}
                                className={cn(fieldClass, 'pr-10')}
                            />
                            <InputError message={fieldErrors.password} />
                        </label>
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Re-enter password
                            <PasswordInput
                                required
                                value={passwordConfirmation}
                                onChange={(event) =>
                                    setPasswordConfirmation(event.target.value)
                                }
                                autoComplete="new-password"
                                passwordrules={passwordRules}
                                className={cn(fieldClass, 'pr-10')}
                            />
                            <InputError
                                message={fieldErrors.password_confirmation}
                            />
                        </label>
                        <label className="col-span-full inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium">
                            <input
                                type="checkbox"
                                required
                                checked={agreed}
                                onChange={(event) =>
                                    setAgreed(event.target.checked)
                                }
                                className="mt-0.5 accent-brass"
                            />
                            I&apos;m 18+, acting for a business, and agree to
                            the Operator Terms
                        </label>
                        {formError ? (
                            <p className="col-span-full text-sm font-medium text-bad">
                                {formError}
                            </p>
                        ) : null}
                    </div>
                ) : null}

                {step === VERIFY_STEP ? (
                    <div className="grid gap-4">
                        <p className="text-sm text-brand-muted">
                            We sent a 6-digit code to{' '}
                            <span className="font-semibold text-brand-ink">
                                {email}
                            </span>
                            . Enter it below.
                        </p>
                        {otpMessage ? (
                            <p className="rounded-lg bg-good-bg p-3 text-sm font-semibold text-good">
                                {otpMessage}
                            </p>
                        ) : null}
                        {formError ? (
                            <p className="text-sm font-medium text-bad">
                                {formError}
                            </p>
                        ) : null}
                        <label className="flex flex-col gap-2 text-sm font-semibold text-brand-ink">
                            Email code
                            <span className="flex flex-wrap gap-2">
                                {emailOtp.map((digit, index) => (
                                    <input
                                        key={index}
                                        ref={(el) => {
                                            otpRefs.current[index] = el;
                                        }}
                                        inputMode="numeric"
                                        maxLength={1}
                                        required
                                        value={digit}
                                        onChange={(event) =>
                                            handleOtpChange(
                                                index,
                                                event.target.value,
                                            )
                                        }
                                        onKeyDown={(event) =>
                                            handleOtpKeyDown(index, event)
                                        }
                                        onPaste={(event) =>
                                            handleOtpPaste(index, event)
                                        }
                                        className="h-11 w-10 rounded-lg border border-brand-line bg-brand-surface text-center text-base font-semibold text-brand-ink"
                                    />
                                ))}
                            </span>
                            <InputError message={fieldErrors.email_otp} />
                        </label>
                        <p className="text-sm text-brand-muted">
                            Didn&apos;t get it?{' '}
                            <button
                                type="button"
                                disabled={busy}
                                className="font-semibold text-brand-ink underline decoration-brand-line hover:decoration-brass disabled:opacity-60"
                                onClick={() => {
                                    void resendOtp();
                                }}
                            >
                                {busy ? 'Sending…' : 'Resend code'}
                            </button>
                        </p>
                    </div>
                ) : null}

            </div>

            <div className="flex flex-wrap gap-2.5">
                {step > 0 && step < VERIFY_STEP ? (
                    <BrandButton
                        as="button"
                        type="button"
                        variant="ghost"
                        onClick={goBack}
                        className="border-brand-ink text-brand-ink"
                    >
                        Back
                    </BrandButton>
                ) : null}
                <BrandButton
                    as="button"
                    type="button"
                    disabled={busy}
                    onClick={() => {
                        void goNext();
                    }}
                >
                    {busy ? <Spinner /> : null}
                    Next
                </BrandButton>
            </div>
        </div>
    );
}
