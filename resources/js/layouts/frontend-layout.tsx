import type { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import { SiteFooter } from '@/components/frontend/site-footer';
import { SiteHeader } from '@/components/frontend/site-header';
import type { SiteData } from '@/types/frontend';

type FrontendPageProps = {
    site: SiteData;
};

export default function FrontendLayout({ children }: { children: ReactNode }) {
    const { site } = usePage<FrontendPageProps>().props;

    return (
        <div className="min-h-screen bg-brand-bg font-figtree text-brand-ink antialiased">
            <SiteHeader site={site} />
            <main>{children}</main>
            <SiteFooter site={site} />
        </div>
    );
}
