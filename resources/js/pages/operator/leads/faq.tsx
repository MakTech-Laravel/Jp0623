import { Head } from '@inertiajs/react';
import { dashboard } from '@/routes';
import { faq as leadsFaq } from '@/routes/leads';

interface FaqItem {
    question: string;
    answer: string;
}

export default function LeadFaq({ faqs }: { faqs: FaqItem[] }) {
    return (
        <>
            <Head title="FAQ" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        FAQ
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        How location leads and the portal work.
                    </p>
                </div>

                <div className="space-y-3">
                    {faqs.map((item, index) => (
                        <details
                            key={item.question}
                            open={index === 0}
                            className="group rounded-xl border bg-card px-4 py-3 shadow-sm open:pb-4"
                        >
                            <summary className="cursor-pointer list-none font-semibold marker:content-none [&::-webkit-details-marker]:hidden">
                                <span className="flex items-center justify-between gap-3">
                                    {item.question}
                                    <span className="text-muted-foreground transition group-open:rotate-45">
                                        +
                                    </span>
                                </span>
                            </summary>
                            <p className="mt-3 text-sm text-muted-foreground">
                                {item.answer}
                            </p>
                        </details>
                    ))}
                </div>
            </div>
        </>
    );
}

LeadFaq.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'FAQ', href: leadsFaq() },
    ],
};
