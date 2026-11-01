import { SectionHead } from '@/components/frontend/section-head';
import { Band } from '@/components/frontend/wrap';
import type { ReviewsData } from '@/types/frontend';

export function ReviewsSection({ data }: { data: ReviewsData }) {
    return (
        <Band alt id="reviews-sec">
            <SectionHead eyebrow={data.eyebrow} title={data.title} />
            <div className="grid gap-4 md:grid-cols-3">
                {data.items.map((review) => (
                    <figure
                        key={review.role + review.org}
                        className="m-0 flex flex-col gap-3 rounded-brand border border-brand-line bg-brand-surface p-6"
                    >
                        <span className="tracking-widest text-brass">
                            {review.stars}
                        </span>
                        <p className="text-brand-ink">
                            &ldquo;{review.quote}&rdquo;
                        </p>
                        <cite className="not-italic text-sm text-brand-muted">
                            <b className="block text-brand-ink">
                                {review.role}
                            </b>
                            {review.org}
                        </cite>
                    </figure>
                ))}
            </div>
        </Band>
    );
}
