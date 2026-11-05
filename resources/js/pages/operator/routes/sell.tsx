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
import { sell as routesSell } from '@/routes/routes';

const LOCATION_TYPES = [
    'Office',
    'Warehouse',
    'Apartment',
    'School',
    'Gym',
] as const;

const EQUIPMENT = ['Snack', 'Drink', 'Combo', 'Cooler'] as const;

export default function RoutesSell() {
    const [step, setStep] = useState(0);
    const [financing, setFinancing] = useState('Yes');
    const [locationType, setLocationType] = useState<string>(LOCATION_TYPES[0]);
    const [equipment, setEquipment] = useState<string[]>([EQUIPMENT[0]]);
    const [cardReader, setCardReader] = useState('Yes');
    const [agreed, setAgreed] = useState(false);

    const toggleEquipment = (item: string) => {
        setEquipment((current) =>
            current.includes(item)
                ? current.filter((value) => value !== item)
                : [...current, item],
        );
    };

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!agreed) {
            toast.error(
                'Please agree to share your route details with a broker.',
            );
            return;
        }

        toast.success(
            'Route submitted. Estimated value: $95,000 – $120,000 (example). A route broker will contact you to discuss listing it.',
        );
        setStep(0);
        setAgreed(false);
    };

    return (
        <>
            <Head title="Submit route for sale" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight">
                        Submit route for sale
                    </h1>
                    <p className="mt-1 max-w-3xl text-muted-foreground">
                        Get an instant estimated value after you submit. Your
                        details go to an independent route sales professional
                        who may contact you. Routes they list can also be
                        advertised here at no charge.
                    </p>
                </div>

                <div className="rounded-xl border bg-card p-6 shadow-sm">
                    <div className="mb-6 flex flex-wrap gap-2 text-sm">
                        {['Route', 'Locations', 'Submit'].map((label, index) => (
                            <span
                                key={label}
                                className={`rounded-full px-3 py-1 font-medium ${
                                    index === step
                                        ? 'bg-brass text-brass-ink'
                                        : index < step
                                          ? 'bg-petrol/15 text-petrol dark:text-brass'
                                          : 'bg-muted text-muted-foreground'
                                }`}
                            >
                                {index + 1}. {label}
                            </span>
                        ))}
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-5">
                        {step === 0 ? (
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="primary-city">
                                        Primary city
                                    </Label>
                                    <Input
                                        id="primary-city"
                                        required
                                        placeholder="e.g. Charlotte, NC"
                                    />
                                </div>
                                <fieldset className="space-y-2 sm:col-span-2">
                                    <legend className="text-sm font-medium">
                                        Offer seller financing?
                                    </legend>
                                    <div className="flex gap-4">
                                        {['Yes', 'No'].map((option) => (
                                            <label
                                                key={option}
                                                className="flex items-center gap-2 text-sm"
                                            >
                                                <input
                                                    type="radio"
                                                    name="financing"
                                                    checked={
                                                        financing === option
                                                    }
                                                    onChange={() =>
                                                        setFinancing(option)
                                                    }
                                                    className="size-4 accent-brass"
                                                />
                                                {option}
                                            </label>
                                        ))}
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Seller financing can raise your
                                        route&apos;s value. Buyers usually put
                                        20–50% down.
                                    </p>
                                </fieldset>
                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="summary">Summary</Label>
                                    <Textarea id="summary" rows={2} required />
                                </div>
                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="equipment-extra">
                                        Additional equipment included
                                    </Label>
                                    <Input
                                        id="equipment-extra"
                                        placeholder="e.g. van, spare machines"
                                    />
                                </div>
                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="route-image">
                                        Route image
                                    </Label>
                                    <Input
                                        id="route-image"
                                        type="file"
                                        accept="image/*"
                                    />
                                </div>
                            </div>
                        ) : null}

                        {step === 1 ? (
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>Location 1 type</Label>
                                    <Select
                                        value={locationType}
                                        onValueChange={setLocationType}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {LOCATION_TYPES.map((option) => (
                                                <SelectItem
                                                    key={option}
                                                    value={option}
                                                >
                                                    {option}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="loc-address">
                                        Location address
                                    </Label>
                                    <Input id="loc-address" required />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="monthly-sales">
                                        Average monthly sales
                                    </Label>
                                    <Input
                                        id="monthly-sales"
                                        inputMode="numeric"
                                        required
                                        placeholder="$"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="commission-pct">
                                        Commission % paid to location
                                    </Label>
                                    <Input
                                        id="commission-pct"
                                        inputMode="numeric"
                                        placeholder="0 if none"
                                    />
                                </div>
                                <fieldset className="space-y-2 sm:col-span-2">
                                    <legend className="text-sm font-medium">
                                        Equipment type
                                    </legend>
                                    <div className="flex flex-wrap gap-3">
                                        {EQUIPMENT.map((item) => (
                                            <label
                                                key={item}
                                                className="flex items-center gap-2 text-sm"
                                            >
                                                <Checkbox
                                                    checked={equipment.includes(
                                                        item,
                                                    )}
                                                    onCheckedChange={() =>
                                                        toggleEquipment(item)
                                                    }
                                                />
                                                {item}
                                            </label>
                                        ))}
                                    </div>
                                </fieldset>
                                <div className="space-y-2 sm:col-span-2">
                                    <Label htmlFor="brand">
                                        Brand / model / year
                                    </Label>
                                    <Input id="brand" />
                                </div>
                                <fieldset className="space-y-2 sm:col-span-2">
                                    <legend className="text-sm font-medium">
                                        Card reader?
                                    </legend>
                                    <div className="flex gap-4">
                                        {['Yes', 'No'].map((option) => (
                                            <label
                                                key={option}
                                                className="flex items-center gap-2 text-sm"
                                            >
                                                <input
                                                    type="radio"
                                                    name="card-reader"
                                                    checked={
                                                        cardReader === option
                                                    }
                                                    onChange={() =>
                                                        setCardReader(option)
                                                    }
                                                    className="size-4 accent-brass"
                                                />
                                                {option}
                                            </label>
                                        ))}
                                    </div>
                                </fieldset>
                            </div>
                        ) : null}

                        {step === 2 ? (
                            <div className="space-y-4">
                                <p className="text-sm text-muted-foreground">
                                    Review and submit. You&apos;ll see your
                                    estimated valuation next.
                                </p>
                                <label className="flex items-start gap-3 text-sm">
                                    <Checkbox
                                        checked={agreed}
                                        onCheckedChange={(value) =>
                                            setAgreed(value === true)
                                        }
                                        className="mt-0.5"
                                    />
                                    <span>
                                        I agree to share my route details with
                                        an independent route broker
                                    </span>
                                </label>
                            </div>
                        ) : null}

                        <div className="flex flex-wrap gap-2">
                            {step > 0 ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setStep((value) => value - 1)}
                                >
                                    Back
                                </Button>
                            ) : null}
                            {step < 2 ? (
                                <Button
                                    type="button"
                                    className="bg-brass text-brass-ink hover:bg-brass/90"
                                    onClick={() => setStep((value) => value + 1)}
                                >
                                    Next
                                </Button>
                            ) : (
                                <Button
                                    type="submit"
                                    className="bg-brass text-brass-ink hover:bg-brass/90"
                                >
                                    Get my valuation
                                </Button>
                            )}
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}

RoutesSell.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Submit route for sale', href: routesSell() },
    ],
};
