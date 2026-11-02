import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { BrandButton } from '@/components/frontend/brand-button';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';
import { cn } from '@/lib/utils';

const fieldClass =
    'rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 text-sm font-medium text-brand-ink shadow-none focus-visible:border-brass focus-visible:ring-brass/30';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    return (
        <>
            <Head title="Reset password" />

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
                className="flex flex-col gap-4"
            >
                {({ processing, errors }) => (
                    <>
                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Email
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                readOnly
                                className={cn(fieldClass, 'bg-brand-sunk')}
                            />
                            <InputError message={errors.email} />
                        </label>

                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            New password
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="new-password"
                                autoFocus
                                placeholder="New password"
                                passwordrules={passwordRules}
                                className={cn(fieldClass, 'pr-10')}
                            />
                            <InputError message={errors.password} />
                        </label>

                        <label className="flex flex-col gap-1 text-sm font-semibold text-brand-ink">
                            Confirm password
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                autoComplete="new-password"
                                placeholder="Confirm password"
                                passwordrules={passwordRules}
                                className={cn(fieldClass, 'pr-10')}
                            />
                            <InputError message={errors.password_confirmation} />
                        </label>

                        <BrandButton
                            as="button"
                            type="submit"
                            disabled={processing}
                            className="w-full"
                            data-test="reset-password-button"
                        >
                            {processing ? <Spinner /> : null}
                            Save password
                        </BrandButton>
                    </>
                )}
            </Form>
        </>
    );
}

ResetPassword.layout = {
    title: 'Choose a new password',
    description: 'Enter a new password for your account.',
};
