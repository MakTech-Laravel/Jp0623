import { Head } from '@inertiajs/react';
import { RegisterStepper } from '@/components/auth/register-stepper';
import TextLink from '@/components/text-link';
import { Band } from '@/components/frontend/wrap';
import { login } from '@/routes';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    return (
        <>
            <Head title="Join as an Operator" />

            <Band>
                <div className="grid items-start gap-10 md:grid-cols-[0.8fr_1.2fr]">
                    <div>
                        <p className="font-mono text-xs tracking-widest text-brass uppercase">
                            Operator sign up
                        </p>
                        <h1 className="mt-2 font-display text-4xl leading-tight font-extrabold tracking-tight text-brand-ink md:text-[40px]">
                            Join Vending Machine Locations For Sale
                        </h1>
                        <p className="mt-3 text-brand-muted">
                            30 days free, then $5.99/month. Cancel anytime.
                        </p>
                        <ul className="mt-5 flex list-none flex-col gap-2 p-0 text-sm text-brand-ink">
                            <li className="before:mr-2 before:font-bold before:text-good before:content-['✓']">
                                Verified location leads near you
                            </li>
                            <li className="before:mr-2 before:font-bold before:text-good before:content-['✓']">
                                Email alerts
                            </li>
                            <li className="before:mr-2 before:font-bold before:text-good before:content-['✓']">
                                Routes for sale
                            </li>
                        </ul>
                        <p className="mt-6 text-sm text-brand-muted">
                            Already have an account?{' '}
                            <TextLink
                                href={login()}
                                className="text-brand-ink underline decoration-brand-line hover:decoration-brass"
                            >
                                Log in
                            </TextLink>
                        </p>
                    </div>

                    <div className="min-w-0 rounded-2xl border border-brand-line bg-brand-surface p-6 md:p-7">
                        <RegisterStepper passwordRules={passwordRules} />
                    </div>
                </div>
            </Band>
        </>
    );
}
