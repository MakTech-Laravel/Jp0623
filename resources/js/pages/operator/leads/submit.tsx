import { Head } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { submit as leadsSubmit } from '@/routes/leads';

const STEPS = [
    {
        title: 'Submit lead',
        body: 'Send us the location details. You must be subscribed.',
    },
    {
        title: 'We verify',
        body: 'We call the business to confirm.',
    },
    {
        title: 'Listed for sale',
        body: 'Nearby operators get notified.',
    },
    {
        title: 'You get paid',
        body: '30% of the sale, paid monthly by PayPal.',
    },
] as const;

const SOURCES = [
    'Referral',
    'Cold call',
    'Walk-in',
    'My website',
    'Other',
] as const;

export default function LeadSubmit() {
    const [source, setSource] = useState<string>(SOURCES[0]);
    const [spoken, setSpoken] = useState('Yes');
    const [assigned, setAssigned] = useState(false);

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!assigned) {
            toast.error('Please confirm you assign the rights to this lead.');
            return;
        }

        toast.success(
            "Lead submitted. We'll call the business to verify it within 2 business days.",
        );
        event.currentTarget.reset();
        setSource(SOURCES[0]);
        setSpoken('Yes');
        setAssigned(false);
    };

    return (
        <>
            <Head title="Submit location lead for sale" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Submit location lead for sale
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        By submitting a lead you assign its rights to us. We
                        contact the business and, if it&apos;s a good lead, list
                        it for sale. When it sells, you get 30% of the price.
                    </p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {STEPS.map((step) => (
                        <div
                            key={step.title}
                            className="rounded-xl border bg-card p-4 shadow-sm"
                        >
                            <h2 className="font-semibold">{step.title}</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {step.body}
                            </p>
                        </div>
                    ))}
                </div>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <form
                        onSubmit={handleSubmit}
                        className="grid gap-4 sm:grid-cols-2"
                    >
                        <div className="space-y-2">
                            <Label htmlFor="location-name">Location name</Label>
                            <Input id="location-name" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="contact-name">Contact name</Label>
                            <Input id="contact-name" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="phone">Phone</Label>
                            <Input id="phone" inputMode="tel" required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input id="email" type="email" />
                        </div>
                        <div className="space-y-2 sm:col-span-2">
                            <Label htmlFor="address">Location address</Label>
                            <Input id="address" required />
                        </div>
                        <div className="space-y-2">
                            <Label>Where did the lead come from?</Label>
                            <Select value={source} onValueChange={setSource}>
                                <SelectTrigger className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {SOURCES.map((option) => (
                                        <SelectItem key={option} value={option}>
                                            {option}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="date-received">Date received</Label>
                            <Input id="date-received" type="date" />
                        </div>

                        <fieldset className="space-y-2 sm:col-span-2">
                            <legend className="text-sm font-medium">
                                Have you spoken with them?
                            </legend>
                            <div className="flex flex-wrap gap-4">
                                {['Yes', 'No'].map((option) => (
                                    <label
                                        key={option}
                                        className="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            type="radio"
                                            name="spoken"
                                            value={option}
                                            checked={spoken === option}
                                            onChange={() => setSpoken(option)}
                                            className="size-4 accent-brass"
                                        />
                                        {option}
                                    </label>
                                ))}
                            </div>
                        </fieldset>

                        <div className="space-y-2 sm:col-span-2">
                            <Label htmlFor="discussed">
                                What did you discuss?
                            </Label>
                            <Textarea id="discussed" rows={3} />
                        </div>
                        <div className="space-y-2 sm:col-span-2">
                            <Label htmlFor="notes">Additional notes</Label>
                            <Textarea id="notes" rows={2} />
                        </div>

                        <label className="flex items-start gap-3 text-sm sm:col-span-2">
                            <Checkbox
                                checked={assigned}
                                onCheckedChange={(value) =>
                                    setAssigned(value === true)
                                }
                                className="mt-0.5"
                            />
                            <span>
                                I assign the rights to this lead and confirm the
                                details are accurate
                            </span>
                        </label>

                        <div className="sm:col-span-2">
                            <Button
                                type="submit"
                                className="bg-brass text-brass-ink hover:bg-brass/90"
                            >
                                Submit lead
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}

LeadSubmit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Submit lead for sale', href: leadsSubmit() },
    ],
};
