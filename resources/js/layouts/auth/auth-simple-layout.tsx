import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center bg-brand-bg px-4 py-8 text-brand-ink">
            <div className="w-full max-w-[440px] rounded-2xl border border-brand-line bg-brand-surface p-6 md:p-7">
                <div className="mb-6 flex flex-col gap-4">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 font-medium text-brand-ink no-underline"
                    >
                        <div className="flex h-9 w-9 items-center justify-center rounded-md">
                            <AppLogoIcon className="size-9 fill-current text-brand-ink" />
                        </div>
                        <span className="sr-only">{title}</span>
                    </Link>

                    <div className="space-y-1.5">
                        <h1 className="font-display text-3xl font-extrabold tracking-tight text-brand-ink">
                            {title}
                        </h1>
                        {description ? (
                            <p className="text-sm text-brand-muted">
                                {description}
                            </p>
                        ) : null}
                    </div>
                </div>
                {children}
            </div>
        </div>
    );
}
