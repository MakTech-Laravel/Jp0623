import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { BrandButton } from '@/components/frontend/brand-button';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { cn } from '@/lib/utils';

const fieldClass =
    'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink shadow-none focus-visible:border-brass focus-visible:ring-brass/30';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
};

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: Props) {
    return (
        <>
            <Head title="Sign in" />

            {status ? (
                <div className="mb-4 rounded-lg bg-good-bg p-3.5 text-sm font-semibold text-good">
                    {status}
                </div>
            ) : null}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-4"
            >
                {({ processing, errors }) => (
                    <>
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Email address
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="username"
                                placeholder="you@company.com"
                                className={fieldClass}
                            />
                            <InputError message={errors.email} />
                        </label>

                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Password
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                placeholder="Password"
                                className={cn(fieldClass, 'pr-10')}
                            />
                            <InputError message={errors.password} />
                        </label>

                        <label className="inline-flex cursor-pointer items-start gap-2 rounded-lg border-2 border-brand-line px-3.5 py-2 text-sm font-medium text-brand-ink">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                tabIndex={3}
                                className="mt-0.5 accent-brass"
                            />
                            Keep me signed in
                        </label>

                        <BrandButton
                            as="button"
                            type="submit"
                            tabIndex={4}
                            disabled={processing}
                            className="w-full"
                            data-test="login-button"
                        >
                            {processing ? <Spinner /> : null}
                            Sign in
                        </BrandButton>

                        <div className="mt-2 flex flex-wrap justify-between gap-2 text-sm">
                            {canResetPassword ? (
                                <TextLink
                                    href={request()}
                                    className="text-brand-muted underline decoration-brand-line hover:text-brand-ink hover:decoration-brass"
                                    tabIndex={5}
                                >
                                    Forgot password?
                                </TextLink>
                            ) : (
                                <span />
                            )}
                            {canRegister ? (
                                <span className="text-brand-muted">
                                    Not subscribed?{' '}
                                    <TextLink
                                        href={register()}
                                        className="text-brand-ink underline decoration-brand-line hover:decoration-brass"
                                        tabIndex={6}
                                    >
                                        Sign up now
                                    </TextLink>
                                </span>
                            ) : null}
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Login.layout = {
    title: 'Sign in',
    description: 'Enter your email and password to continue.',
};
