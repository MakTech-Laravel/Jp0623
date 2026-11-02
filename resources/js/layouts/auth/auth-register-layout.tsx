import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Wrap } from '@/components/frontend/wrap';
import { home } from '@/routes';

export default function AuthRegisterLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-svh bg-brand-bg text-brand-ink">
            <header className="border-b border-brand-line bg-brand-surface">
                <Wrap className="flex h-14 items-center">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 font-medium text-brand-ink no-underline"
                    >
                        <AppLogoIcon className="size-8 fill-current text-brand-ink" />
                        <span className="font-display text-sm font-bold tracking-tight">
                            Vending Machine Locations For Sale
                        </span>
                    </Link>
                </Wrap>
            </header>
            {children}
        </div>
    );
}
