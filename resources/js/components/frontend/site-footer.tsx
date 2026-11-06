import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Wrap } from '@/components/frontend/wrap';
import type { SiteData } from '@/types/frontend';

type SiteFooterProps = {
    site: SiteData;
};

function FooterLink({
    href,
    children,
    className,
}: {
    href: string;
    children: ReactNode;
    className: string;
}) {
    const isExternal = href.startsWith('http') || href.startsWith('mailto:');

    if (isExternal) {
        return (
            <a href={href} className={className}>
                {children}
            </a>
        );
    }

    return (
        <Link href={href} className={className}>
            {children}
        </Link>
    );
}

export function SiteFooter({ site }: SiteFooterProps) {
    return (
        <footer className="border-t border-brand-line bg-petrol text-on-petrol">
            <Wrap className="py-12">
                <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <Link
                            href="/"
                            className="font-display text-lg font-extrabold text-on-petrol no-underline"
                        >
                            {site.name}
                            <small className="mt-0.5 block font-mono text-xs font-medium tracking-wide text-on-petrol/70 uppercase">
                                {site.tagline}
                            </small>
                        </Link>
                        <p className="mt-3 max-w-lg text-sm text-on-petrol/80">
                            {site.footerBlurb}
                        </p>
                        <p className="mt-3">
                            <Link
                                href="/contact"
                                className="font-mono text-sm font-semibold text-on-petrol no-underline hover:underline hover:decoration-brass"
                            >
                                Contact us
                            </Link>
                        </p>
                    </div>

                    {site.footerColumns.map((column) => (
                        <div key={column.title}>
                            <h4 className="mb-3 font-display text-base font-bold">
                                {column.title}
                            </h4>
                            <ul className="m-0 flex list-none flex-col gap-2 p-0">
                                {column.links.map((link) => (
                                    <li key={link.label}>
                                        <FooterLink
                                            href={link.href}
                                            className="text-sm text-on-petrol/80 no-underline hover:text-on-petrol hover:underline hover:decoration-brass"
                                        >
                                            {link.label}
                                        </FooterLink>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>

                <div className="mt-10 flex flex-col gap-3 border-t border-on-petrol/15 pt-5 text-sm text-on-petrol/70 sm:flex-row sm:items-center sm:justify-between">
                    <span>{site.copyright}</span>
                    <nav
                        aria-label="Legal"
                        className="flex flex-wrap gap-x-4 gap-y-2"
                    >
                        {site.legalLinks.map((link) => (
                            <FooterLink
                                key={link.label}
                                href={link.href}
                                className="no-underline hover:text-on-petrol hover:underline hover:decoration-brass"
                            >
                                {link.label}
                            </FooterLink>
                        ))}
                    </nav>
                </div>
            </Wrap>
        </footer>
    );
}
