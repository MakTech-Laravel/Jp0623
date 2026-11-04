import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import { CitiesSection } from '@/components/home/cities-section';
import { FaqSection } from '@/components/home/faq-section';
import { FeaturedMachinesSection } from '@/components/home/featured-machines-section';
import { HeroSection } from '@/components/home/hero-section';
import { HowItWorksSection } from '@/components/home/how-it-works-section';
import { IndustriesSection } from '@/components/home/industries-section';
import { ProductsTeaserSection } from '@/components/home/products-teaser-section';
import { RequestSection } from '@/components/home/request-section';
import { ResourcesSection } from '@/components/home/resources-section';
import { ReviewsSection } from '@/components/home/reviews-section';
import { ServicesSection } from '@/components/home/services-section';
import type { HomePageProps } from '@/types/frontend';

export default function Home({
    site,
    hero,
    howItWorks,
    services,
    featuredMachines,
    products,
    requestForm,
    reviews,
    industries,
    faqs,
    articles,
    cities,
}: HomePageProps) {
    useEffect(() => {
        const id = window.location.hash.slice(1);

        if (!id) {
            return;
        }

        const timer = window.setTimeout(() => {
            document
                .getElementById(id)
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);

        return () => window.clearTimeout(timer);
    }, []);

    return (
        <>
            <Head title="Home">
                <meta
                    head-key="description"
                    name="description"
                    content="Nationwide vending services with free machine installation, stocking and service. Vending machines, micro markets, Vendera smart coolers, office coffee and ATMs for businesses in all 50 states."
                />
            </Head>

            <HeroSection hero={hero} phone={site.phone} />
            <HowItWorksSection data={howItWorks} />
            <ServicesSection data={services} />
            <FeaturedMachinesSection data={featuredMachines} />
            <ProductsTeaserSection data={products} />
            <RequestSection data={requestForm} />
            <ReviewsSection data={reviews} />
            <IndustriesSection data={industries} />
            <FaqSection data={faqs} />
            <ResourcesSection data={articles} />
            <CitiesSection data={cities} />
        </>
    );
}
