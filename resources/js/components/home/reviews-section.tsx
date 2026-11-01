import Autoplay from 'embla-carousel-autoplay';
import { useRef } from 'react';
import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import type { ReviewsData } from '@/types/frontend';

export function ReviewsSection({ data }: { data: ReviewsData }) {
    const autoplay = useRef(
        Autoplay({
            delay: 4000,
            stopOnInteraction: true,
            stopOnMouseEnter: true,
        }),
    );

    return (
        <Band alt id="reviews-sec">
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <Carousel
                opts={{ align: 'start', loop: true }}
                plugins={[autoplay.current]}
                className="w-full max-w-full min-w-0 overflow-hidden"
            >
                <CarouselContent className="-ml-4">
                    {data.items.map((review) => (
                        <CarouselItem
                            key={review.role + review.org}
                            className="basis-full pl-4 md:basis-1/2 lg:basis-1/3"
                        >
                            <figure className="m-0 flex h-full flex-col gap-3 rounded-brand border border-brand-line bg-brand-surface p-6">
                                <span className="tracking-widest text-brass">
                                    {review.stars}
                                </span>
                                <p className="text-brand-ink">
                                    &ldquo;{review.quote}&rdquo;
                                </p>
                                <cite className="mt-auto not-italic text-sm text-brand-muted">
                                    <b className="block text-brand-ink">
                                        {review.role}
                                    </b>
                                    {review.org}
                                </cite>
                            </figure>
                        </CarouselItem>
                    ))}
                </CarouselContent>
                <CarouselPrevious className="hidden border-brand-line bg-brand-surface text-brand-ink hover:bg-brand-sunk md:flex md:left-4" />
                <CarouselNext className="hidden border-brand-line bg-brand-surface text-brand-ink hover:bg-brand-sunk md:flex md:right-4" />
            </Carousel>
        </Band>
    );
}
