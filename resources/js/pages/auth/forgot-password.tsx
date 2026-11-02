import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { BrandButton } from '@/components/frontend/brand-button';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

const fieldClass =
    'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink shadow-none focus-visible:border-brass focus-visible:ring-brass/30';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Forgot password" />

            {status ? (
                <div className="mb-4 rounded-lg bg-good-bg p-3.5 text-sm font-semibold text-good">
                    {status}
                </div>
            ) : null}

            <Form {...email.form()} className="flex flex-col gap-4">
                {({ processing, errors }) => (
                    <>
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Email address
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="off"
                                autoFocus
                                placeholder="you@company.com"
                                className={fieldClass}
                            />
                            <InputError message={errors.email} />
                        </label>

                        <BrandButton
                            as="button"
                            type="submit"
                            disabled={processing}
                            className="w-full"
                            data-test="email-password-reset-link-button"
                        >
                            {processing ? <Spinner /> : null}
                            Send reset link
                        </BrandButton>
                    </>
                )}
            </Form>

            <p className="mt-4 text-sm text-brand-muted">
                <TextLink
                    href={login()}
                    className="text-brand-ink underline decoration-brand-line hover:decoration-brass"
                >
                    Back to sign in
                </TextLink>
            </p>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Reset your password',
    description: "Enter your email and we'll send a reset link.",
};
