import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { ClipboardEvent, KeyboardEvent } from 'react';
import TextLink from '@/components/text-link';
import { BrandButton } from '@/components/frontend/brand-button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { cn } from '@/lib/utils';

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function postJson(url: string, body: Record<string, unknown> = {}) {
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
        throw new Error(
            payload?.errors?.email_otp?.[0] ??
                payload?.message ??
                'Request failed.',
        );
    }

    return payload;
}

type Props = {
    email?: string;
    status?: string;
};

export default function VerifyOtp({ email }: Props) {
    const [digits, setDigits] = useState(['', '', '', '', '', '']);
    const [error, setError] = useState<string | null>(null);
    const [message, setMessage] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const refs = useRef<Array<HTMLInputElement | null>>([]);
    const didAutoSend = useRef(false);

    useEffect(() => {
        if (didAutoSend.current) {
            return;
        }

        didAutoSend.current = true;
        void resend();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    function handleChange(index: number, value: string) {
        const digit = value.replace(/\D/g, '').slice(-1);
        const next = [...digits];
        next[index] = digit;
        setDigits(next);

        if (digit && index < 5) {
            refs.current[index + 1]?.focus();
        }
    }

    function handlePaste(
        index: number,
        event: ClipboardEvent<HTMLInputElement>,
    ) {
        event.preventDefault();
        const pasted = event.clipboardData
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, 6);

        if (!pasted) {
            return;
        }

        const next = [...digits];
        const start = pasted.length === 6 ? 0 : index;

        for (let i = 0; i < pasted.length && start + i < 6; i++) {
            next[start + i] = pasted[i] ?? '';
        }

        setDigits(next);
        refs.current[Math.min(start + pasted.length, 5)]?.focus();
    }

    function handleKeyDown(
        index: number,
        event: KeyboardEvent<HTMLInputElement>,
    ) {
        if (event.key === 'Backspace' && !digits[index] && index > 0) {
            refs.current[index - 1]?.focus();
        }
    }

    async function resend() {
        setBusy(true);
        setError(null);

        try {
            await postJson('/register/otp', {});
            setMessage('Verification code sent. Check your email.');
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Could not resend.');
        } finally {
            setBusy(false);
        }
    }

    async function verify() {
        const code = digits.join('');

        if (code.length !== 6) {
            setError('Enter the 6-digit code.');
            return;
        }

        setBusy(true);
        setError(null);

        try {
            await postJson('/register/otp/verify', { email_otp: code });
            router.visit('/dashboard');
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Invalid code.');
        } finally {
            setBusy(false);
        }
    }

    return (
        <>
            <Head title="Verify email" />
            <div className="flex flex-col gap-4">
                <p className="text-sm text-brand-muted">
                    Enter the 6-digit code we sent to{' '}
                    <span className="font-semibold text-brand-ink">
                        {email}
                    </span>
                    .
                </p>
                {message ? (
                    <p className="rounded-lg bg-good-bg p-3 text-sm font-semibold text-good">
                        {message}
                    </p>
                ) : null}
                {error ? (
                    <p className="text-sm font-medium text-bad">{error}</p>
                ) : null}
                <div className="flex flex-wrap gap-2">
                    {digits.map((digit, index) => (
                        <input
                            key={index}
                            ref={(el) => {
                                refs.current[index] = el;
                            }}
                            inputMode="numeric"
                            maxLength={1}
                            value={digit}
                            onChange={(event) =>
                                handleChange(index, event.target.value)
                            }
                            onKeyDown={(event) => handleKeyDown(index, event)}
                            onPaste={(event) => handlePaste(index, event)}
                            className={cn(
                                'h-11 w-10 rounded-lg border border-brand-line bg-brand-surface text-center text-base font-semibold text-brand-ink',
                            )}
                        />
                    ))}
                </div>
                <BrandButton
                    as="button"
                    type="button"
                    disabled={busy}
                    className="w-full"
                    onClick={() => {
                        void verify();
                    }}
                >
                    {busy ? <Spinner /> : null}
                    Verify email
                </BrandButton>
                <button
                    type="button"
                    disabled={busy}
                    className="text-sm font-semibold text-brand-ink underline decoration-brand-line hover:decoration-brass disabled:opacity-60"
                    onClick={() => {
                        void resend();
                    }}
                >
                    Resend code
                </button>
                <TextLink
                    href={logout()}
                    className="text-center text-sm text-brand-muted"
                >
                    Log out
                </TextLink>
            </div>
        </>
    );
}

VerifyOtp.layout = {
    title: 'Verify your email',
    description: 'Enter the code from your email to continue.',
};
