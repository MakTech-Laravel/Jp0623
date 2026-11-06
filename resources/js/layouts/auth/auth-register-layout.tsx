import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { Wrap } from '@/components/frontend/wrap';
import { home } from '@/routes';

export default function AuthRegisterLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-svh bg-brand-bg text-brand-ink">
            <header className="border-b border-brand-line bg-brand-surface">
                <Wrap className="flex h-14 items-center">
                    <Link
                        href={home()}
                        className="flex items-center font-medium text-brand-ink no-underline"
                    >
                        <img
                            src="/images/logo-final-dark2.png"
                            alt="Logo"
                            className="h-16 w-auto object-contain object-left"
                        />
                    </Link>
                </Wrap>
            </header>
            {children}
        </div>
    );
}
