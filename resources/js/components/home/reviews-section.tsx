import Autoplay from 'embla-carousel-autoplay';
import { useRef } from 'react';
import {
    Reveal,
    brandCardShadowHoverClass,
} from '@/components/frontend/reveal';
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
import { cn } from '@/lib/utils';

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
                opts={{ align: 'start', loop: true, slidesToScroll: 1 }}
                plugins={[autoplay.current]}
                className="w-full max-w-full min-w-0"
            >
                <div className="flex items-center gap-2 sm:gap-3">
                    <CarouselPrevious
                        disabled={false}
                        className="static top-auto left-auto size-9 shrink-0 translate-none border-brand-line bg-brand-surface text-brand-ink hover:bg-brand-sunk"
                    />
                    <div className="min-w-0 flex-1">
                        <CarouselContent className="-ml-4">
                            {data.items.map((review, index) => (
                                <CarouselItem
                                    key={review.role + review.org}
                                    className="basis-full py-3 pl-4 md:basis-1/2 lg:basis-1/3"
                                >
                                    <Reveal delay={index * 0.05}>
                                        <figure
                                            className={cn(
                                                'm-0 flex h-full flex-col gap-3 rounded-brand border border-brand-line bg-brand-surface p-6',
                                                brandCardShadowHoverClass,
                                            )}
                                        >
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
                                    </Reveal>
                                </CarouselItem>
                            ))}
                        </CarouselContent>
                    </div>
                    <CarouselNext
                        disabled={false}
                        className="static top-auto right-auto size-9 shrink-0 translate-none border-brand-line bg-brand-surface text-brand-ink hover:bg-brand-sunk"
                    />
                </div>
            </Carousel>
        </Band>
    );
}
