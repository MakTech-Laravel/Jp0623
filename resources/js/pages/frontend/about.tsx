import { Head } from '@inertiajs/react';
import { AboutCtaSection } from '@/components/about/cta-section';
import { AboutStorySection } from '@/components/about/story-section';
import { PageHero } from '@/components/frontend/page-hero';
import type { AboutPageProps } from '@/types/frontend';

export default function About({ hero, story, cta }: AboutPageProps) {
    return (
        <>
            <Head title="About Us">
                <meta
                    head-key="description"
                    name="description"
                    content="We make vending simple for businesses with one location or hundreds, and we help independent operators grow."
                />
            </Head>

            <PageHero
                title={hero.title}
                lead={hero.lead}
                image={hero.image}
            />
            <AboutStorySection data={story} />
            <AboutCtaSection data={cta} />
        </>
    );
}
