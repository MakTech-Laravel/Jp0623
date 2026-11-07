import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Band } from '@/components/frontend/wrap';
import { Spinner } from '@/components/ui/spinner';

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

export default function RegisterPayment() {
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState<string | null>(null);

    async function continueToDashboard() {
        setBusy(true);
        setError(null);

        try {
            const response = await fetch('/register/trial', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/html, application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrfToken(),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            });

            if (response.redirected) {
                router.visit(response.url);
                return;
            }

            if (response.ok) {
                router.visit('/dashboard');
                return;
            }

            setError('Could not continue. Try again.');
        } catch {
            setError('Could not continue. Try again.');
        } finally {
            setBusy(false);
        }
    }

    return (
        <>
            <Head title="Start your trial" />
            <Band>
                <div className="mx-auto max-w-xl">
                    <p className="font-mono text-xs tracking-widest text-brass uppercase">
                        Payment
                    </p>
                    <h1 className="mt-2 font-display text-3xl font-extrabold text-brand-ink">
                        Start your free trial
                    </h1>
                    <p className="mt-3 text-brand-muted">
                        Your card is not charged today. Trial ends Dec 1, 2026,
                        then $5.99/month.
                    </p>
                    <div className="mt-6 rounded-xl border border-dashed border-brand-line bg-brand-sunk px-4 py-10 text-center text-sm text-brand-muted">
                        Secure card form from the payment provider goes here.
                    </div>
                    {error ? (
                        <p className="mt-4 text-sm font-medium text-bad">
                            {error}
                        </p>
                    ) : null}
                    <div className="mt-6">
                        <BrandButton
                            as="button"
                            type="button"
                            disabled={busy}
                            data-test="register-trial-button"
                            onClick={() => {
                                void continueToDashboard();
                            }}
                        >
                            {busy ? <Spinner /> : null}
                            Continue to dashboard
                        </BrandButton>
                    </div>
                </div>
            </Band>
        </>
    );
}
