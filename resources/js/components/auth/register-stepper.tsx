import { Form } from '@inertiajs/react';
import { useRef, useState } from 'react';
import type { ClipboardEvent, KeyboardEvent } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { BrandButton } from '@/components/frontend/brand-button';
import { Spinner } from '@/components/ui/spinner';
import { otp, store } from '@/routes/register';
import { cn } from '@/lib/utils';

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function requestRegisterOtp(email: string): Promise<void> {
    const response = await fetch(otp.url(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: JSON.stringify({ email }),
    });

    if (response.ok) {
        return;
    }

    const payload = (await response.json().catch(() => null)) as {
        message?: string;
        errors?: Record<string, string[]>;
    } | null;

    const message =
        payload?.errors?.email?.[0] ??
        payload?.message ??
        'Could not send verification code.';

    throw new Error(message);
}

const fieldClass =
    'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink shadow-none focus-visible:border-brass focus-visible:ring-brass/30';

const STEPS = ['Machines', 'Area', 'Account', 'Verify', 'Payment'] as const;
const ACCOUNT_STEP = 2;
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
    const [city, setCity] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [company, setCompany] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [agreed, setAgreed] = useState(false);
    const [emailOtp, setEmailOtp] = useState(['', '', '', '', '', '']);
    const [otpSending, setOtpSending] = useState(false);
    const [otpMessage, setOtpMessage] = useState<string | null>(null);
    const [otpError, setOtpError] = useState<string | null>(null);
    const stepRef = useRef<HTMLDivElement>(null);
    const otpRefs = useRef<Array<HTMLInputElement | null>>([]);

    function toggleMachineType(type: string) {
        setMachineTypes((current) =>
            current.includes(type)
                ? current.filter((item) => item !== type)
                : [...current, type],
        );
    }

    async function sendOtpCode(): Promise<boolean> {
        setOtpSending(true);
        setOtpError(null);
        setOtpMessage(null);

        try {
            await requestRegisterOtp(email);
            setOtpMessage('Verification code sent. Check your email.');
            return true;
        } catch (error) {
            setOtpError(
                error instanceof Error
                    ? error.message
                    : 'Could not send verification code.',
            );
            return false;
        } finally {
            setOtpSending(false);
        }
    }

    async function goNext() {
        if (!validateStep(stepRef.current)) {
            return;
        }

        if (step === 0 && machineTypes.length === 0) {
            return;
        }

        if (step === ACCOUNT_STEP && !agreed) {
            return;
        }

        if (step === ACCOUNT_STEP) {
            const sent = await sendOtpCode();

            if (!sent) {
                return;
            }
        }

        setStep((current) => Math.min(current + 1, STEPS.length - 1));
    }

    function goBack() {
        setStep((current) => Math.max(current - 1, 0));
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

        for (let offset = 0; offset < digits.length && start + offset < 6; offset++) {
            next[start + offset] = digits[offset] ?? '';
        }

        setEmailOtp(next);

        const focusIndex = Math.min(start + digits.length, 5);
        otpRefs.current[focusIndex]?.focus();
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
        <Form
            {...store.form()}
            transform={() => ({
                first_name: firstName,
                last_name: lastName,
                email,
                password,
                password_confirmation: passwordConfirmation,
                company_name: company,
                owns_machines: ownsMachines === 'Yes',
                machine_types: machineTypes,
                machines_needed: quantity,
                primary_city: city,
                terms_accepted: agreed,
                email_otp: emailOtp.join(''),
                start_trial: true,
            })}
            resetOnSuccess={['password', 'password_confirmation']}
            disableWhileProcessing
            className="flex flex-col gap-5"
            onError={(errors) => {
                if (errors.owns_machines || errors.machine_types) {
                    setStep(0);
                } else if (errors.machines_needed || errors.primary_city) {
                    setStep(1);
                } else if (errors.email_otp) {
                    setStep(3);
                } else if (errors.start_trial) {
                    setStep(4);
                } else {
                    setStep(ACCOUNT_STEP);
                }
            }}
        >
            {({ processing, errors }) => (
                <>
                    <div className="flex flex-wrap gap-1.5">
                        {STEPS.map((label, index) => (
                            <span
                                key={label}
                                className={cn(
                                    'rounded px-2.5 py-1 font-mono text-xs font-semibold',
                                    index === step && 'bg-brass text-brass-ink',
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

                    <div ref={stepRef}>
                        {step === 0 ? (
                            <div className="grid gap-5">
                                <fieldset className="grid gap-2.5">
                                    <legend className="mb-1 text-sm font-semibold text-brand-ink">
                                        Do you currently own vending machines?
                                    </legend>
                                    <div className="flex flex-wrap gap-2">
                                        {(['Yes', 'No'] as const).map(
                                            (option) => (
                                                <OptLabel
                                                    key={option}
                                                    checked={
                                                        ownsMachines === option
                                                    }
                                                >
                                                    <input
                                                        type="radio"
                                                        name="ownsMachines"
                                                        value={option}
                                                        checked={
                                                            ownsMachines ===
                                                            option
                                                        }
                                                        onChange={() =>
                                                            setOwnsMachines(
                                                                option,
                                                            )
                                                        }
                                                        className="accent-brass"
                                                    />
                                                    {option}
                                                </OptLabel>
                                            ),
                                        )}
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
                                                checked={machineTypes.includes(
                                                    type,
                                                )}
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
                                                    onChange={() =>
                                                        setQuantity(qty)
                                                    }
                                                    className="accent-brass"
                                                />
                                                {qty}
                                            </OptLabel>
                                        ))}
                                    </div>
                                </fieldset>

                                <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                                    Primary city you want locations in
                                    <input
                                        required
                                        value={city}
                                        onChange={(event) =>
                                            setCity(event.target.value)
                                        }
                                        placeholder="Type city name, e.g. Houston, TX"
                                        className={fieldClass}
                                    />
                                </label>
                            </div>
                        ) : null}

                        {step === 2 ? (
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
                                        <InputError
                                            message={errors.first_name}
                                        />
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
                                        <InputError
                                            message={errors.last_name}
                                        />
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
                                        <InputError
                                            message={errors.company_name}
                                        />
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
                                    <InputError message={errors.email} />
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
                                    <InputError message={errors.password} />
                                </label>
                                <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                                    Re-enter password
                                    <PasswordInput
                                        required
                                        value={passwordConfirmation}
                                        onChange={(event) =>
                                            setPasswordConfirmation(
                                                event.target.value,
                                            )
                                        }
                                        autoComplete="new-password"
                                        passwordrules={passwordRules}
                                        className={cn(fieldClass, 'pr-10')}
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
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
                                        I&apos;m 18+, acting for a business, and
                                        agree to the Operator Terms
                                    </label>
                                    {otpError ? (
                                        <p className="col-span-full text-sm font-medium text-bad">
                                            {otpError}
                                        </p>
                                    ) : null}
                                </div>
                            ) : null}

                        {step === 3 ? (
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
                                {otpError ? (
                                    <p className="text-sm font-medium text-bad">
                                        {otpError}
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
                                                    handleOtpKeyDown(
                                                        index,
                                                        event,
                                                    )
                                                }
                                                onPaste={(event) =>
                                                    handleOtpPaste(index, event)
                                                }
                                                className="h-11 w-10 rounded-lg border border-brand-line bg-brand-surface text-center text-base font-semibold text-brand-ink"
                                            />
                                        ))}
                                    </span>
                                    <InputError message={errors.email_otp} />
                                </label>
                                <p className="text-sm text-brand-muted">
                                    Didn&apos;t get it?{' '}
                                    <button
                                        type="button"
                                        disabled={otpSending}
                                        className="font-semibold text-brand-ink underline decoration-brand-line hover:decoration-brass disabled:opacity-60"
                                        onClick={() => {
                                            void sendOtpCode();
                                        }}
                                    >
                                        {otpSending
                                            ? 'Sending…'
                                            : 'Resend code'}
                                    </button>
                                </p>
                            </div>
                        ) : null}

                        {step === 4 ? (
                            <div className="grid gap-4">
                                <p className="text-sm text-brand-muted">
                                    Your card is not charged today. Trial ends
                                    Dec 1, 2026, then $39/month.
                                </p>
                                <div className="rounded-xl border border-dashed border-brand-line bg-brand-sunk px-4 py-10 text-center text-sm text-brand-muted">
                                    Secure card form from the payment provider
                                    goes here.
                                </div>
                            </div>
                        ) : null}
                    </div>

                    <div className="flex flex-wrap gap-2.5">
                        {step > 0 ? (
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
                        {step < STEPS.length - 1 ? (
                            <BrandButton
                                as="button"
                                type="button"
                                disabled={otpSending}
                                onClick={() => {
                                    void goNext();
                                }}
                            >
                                {otpSending && step === ACCOUNT_STEP ? (
                                    <Spinner />
                                ) : null}
                                Next
                            </BrandButton>
                        ) : (
                            <BrandButton
                                as="button"
                                type="submit"
                                disabled={processing}
                                data-test="register-user-button"
                                onClick={(event) => {
                                    if (!validateStep(stepRef.current)) {
                                        event.preventDefault();
                                    }
                                }}
                            >
                                {processing ? <Spinner /> : null}
                                Start my free trial
                            </BrandButton>
                        )}
                    </div>
                </>
            )}
        </Form>
    );
}
