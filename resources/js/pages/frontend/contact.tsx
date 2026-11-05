import { Head } from '@inertiajs/react';
import { ContactSection } from '@/components/contact/contact-section';
import { PageHero } from '@/components/frontend/page-hero';
import type { ContactPageProps } from '@/types/frontend';

export default function Contact({ hero, panel, form }: ContactPageProps) {
    return (
        <>
            <Head title="Contact Us">
                <meta
                    head-key="description"
                    name="description"
                    content="Contact Vending Machine Location Services. Complete the form or email our team."
                />
            </Head>

            <PageHero title={hero.title} lead={hero.lead} />
            <ContactSection panel={panel} form={form} />
        </>
    );
}
