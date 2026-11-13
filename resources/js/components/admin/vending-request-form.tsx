import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import {
    LocationAutocomplete,
    type LocationSuggestion,
} from '@/components/location/location-autocomplete';
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
import { cn } from '@/lib/utils';

type VendingRequestFormProps = {
    action: UrlMethodPair;
    defaultPrice: number;
    locationTypes: string[];
    placements: string[];
    machineInterests: string[];
    machineCounts: string[];
    existingVending: string[];
    yesNoOptions: string[];
    onCancel?: () => void;
};

export function VendingRequestForm({
    action,
    defaultPrice,
    locationTypes,
    placements,
    machineInterests,
    machineCounts,
    existingVending,
    yesNoOptions,
    onCancel,
}: VendingRequestFormProps) {
    const form = useForm(action, {
        location_type: locationTypes[0] ?? '',
        business_name: '',
        address_or_zip: '',
        address: null as string | null,
        city: '',
        state: '',
        postal_code: null as string | null,
        latitude: null as number | null,
        longitude: null as number | null,
        employees_on_site: '' as string | number | null,
        daily_visitors: '' as string | number | null,
        placement: placements[0] ?? 'Inside',
        space_notes: '',
        food_on_site: yesNoOptions[1] ?? 'No',
        comparing_quotes: yesNoOptions[1] ?? 'No',
        machine_interests: [] as string[],
        machine_count: machineCounts[0] ?? '1',
        existing_vending: existingVending[0] ?? 'No',
        switch_reason: '',
        contact_name: '',
        job_title: '',
        email: '',
        phone: '',
        price: defaultPrice,
    });

    const [locationQuery, setLocationQuery] = useState('');
    const [selectedLocation, setSelectedLocation] =
        useState<LocationSuggestion | null>(null);

    const isSwitching = form.data.existing_vending !== 'No';

    function applyLocation(suggestion: LocationSuggestion | null) {
        setSelectedLocation(suggestion);
        setLocationQuery(suggestion?.label ?? '');
        form.setData((data) => ({
            ...data,
            address_or_zip: suggestion?.label ?? '',
            address: suggestion?.address ?? null,
            city: suggestion?.city ?? '',
            state: suggestion?.state ?? '',
            postal_code: suggestion?.postal_code ?? null,
            latitude: suggestion?.latitude ?? null,
            longitude: suggestion?.longitude ?? null,
        }));
    }

    function toggleInterest(interest: string) {
        const current = form.data.machine_interests;
        form.setData(
            'machine_interests',
            current.includes(interest)
                ? current.filter((item) => item !== interest)
                : [...current, interest],
        );
    }

    function handleSubmit(event: React.FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            employees_on_site:
                data.employees_on_site === '' || data.employees_on_site === null
                    ? null
                    : Number(data.employees_on_site),
            daily_visitors:
                data.daily_visitors === '' || data.daily_visitors === null
                    ? null
                    : Number(data.daily_visitors),
            switch_reason: isSwitching ? data.switch_reason : null,
            price: Number(data.price),
        }));
        form.submit({ preserveScroll: true });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-8">
            <section className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2 sm:col-span-2">
                    <Label htmlFor="business_name">Business name</Label>
                    <Input
                        id="business_name"
                        value={form.data.business_name}
                        onChange={(e) =>
                            form.setData('business_name', e.target.value)
                        }
                        aria-invalid={form.invalid('business_name')}
                    />
                    <InputError message={form.errors.business_name} />
                </div>

                <div className="grid gap-2">
                    <Label>Location type</Label>
                    <Select
                        value={form.data.location_type}
                        onValueChange={(value) =>
                            form.setData('location_type', value)
                        }
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                            {locationTypes.map((type) => (
                                <SelectItem key={type} value={type}>
                                    {type}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={form.errors.location_type} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="price">Lead price (USD)</Label>
                    <Input
                        id="price"
                        type="number"
                        min={1}
                        value={form.data.price}
                        onChange={(e) =>
                            form.setData('price', Number(e.target.value) || 0)
                        }
                        aria-invalid={form.invalid('price')}
                    />
                    <InputError message={form.errors.price} />
                </div>

                <div className="grid gap-2 sm:col-span-2">
                    <LocationAutocomplete
                        label="Address"
                        value={locationQuery}
                        selected={selectedLocation}
                        onQueryChange={(value) => {
                            setLocationQuery(value);
                            if (selectedLocation) {
                                applyLocation(null);
                            }
                        }}
                        onSelect={applyLocation}
                        onClearSelection={() => applyLocation(null)}
                        inputClassName="border-input bg-background text-foreground shadow-xs focus-visible:border-ring focus-visible:ring-ring/50"
                        error={
                            form.errors.city ??
                            form.errors.state ??
                            form.errors.latitude ??
                            form.errors.longitude ??
                            form.errors.address_or_zip ??
                            null
                        }
                    />
                </div>
            </section>

            <section className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="employees_on_site">Employees on site</Label>
                    <Input
                        id="employees_on_site"
                        type="number"
                        min={0}
                        value={form.data.employees_on_site ?? ''}
                        onChange={(e) =>
                            form.setData('employees_on_site', e.target.value)
                        }
                    />
                    <InputError message={form.errors.employees_on_site} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="daily_visitors">Daily visitors</Label>
                    <Input
                        id="daily_visitors"
                        type="number"
                        min={0}
                        value={form.data.daily_visitors ?? ''}
                        onChange={(e) =>
                            form.setData('daily_visitors', e.target.value)
                        }
                    />
                    <InputError message={form.errors.daily_visitors} />
                </div>
                <div className="grid gap-2">
                    <Label>Placement</Label>
                    <Select
                        value={form.data.placement}
                        onValueChange={(value) =>
                            form.setData('placement', value)
                        }
                    >
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {placements.map((option) => (
                                <SelectItem key={option} value={option}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={form.errors.placement} />
                </div>
                <div className="grid gap-2">
                    <Label>Food options on site</Label>
                    <Select
                        value={form.data.food_on_site}
                        onValueChange={(value) =>
                            form.setData('food_on_site', value)
                        }
                    >
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {yesNoOptions.map((option) => (
                                <SelectItem key={option} value={option}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={form.errors.food_on_site} />
                </div>
                <div className="grid gap-2">
                    <Label>Comparing quotes</Label>
                    <Select
                        value={form.data.comparing_quotes}
                        onValueChange={(value) =>
                            form.setData('comparing_quotes', value)
                        }
                    >
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {yesNoOptions.map((option) => (
                                <SelectItem key={option} value={option}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={form.errors.comparing_quotes} />
                </div>
                <div className="grid gap-2 sm:col-span-2">
                    <Label htmlFor="space_notes">Space notes</Label>
                    <Textarea
                        id="space_notes"
                        value={form.data.space_notes}
                        onChange={(e) =>
                            form.setData('space_notes', e.target.value)
                        }
                        rows={2}
                    />
                    <InputError message={form.errors.space_notes} />
                </div>
            </section>

            <section className="space-y-4">
                <div>
                    <Label>Machine interests</Label>
                    <div className="mt-2 flex flex-wrap gap-3">
                        {machineInterests.map((interest) => (
                            <label
                                key={interest}
                                className={cn(
                                    'inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm',
                                    form.data.machine_interests.includes(
                                        interest,
                                    ) && 'border-primary bg-primary/5',
                                )}
                            >
                                <Checkbox
                                    checked={form.data.machine_interests.includes(
                                        interest,
                                    )}
                                    onCheckedChange={() =>
                                        toggleInterest(interest)
                                    }
                                />
                                {interest}
                            </label>
                        ))}
                    </div>
                    <InputError message={form.errors.machine_interests} />
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label>Machine count</Label>
                        <Select
                            value={form.data.machine_count}
                            onValueChange={(value) =>
                                form.setData('machine_count', value)
                            }
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {machineCounts.map((count) => (
                                    <SelectItem key={count} value={count}>
                                        {count}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.machine_count} />
                    </div>
                    <div className="grid gap-2">
                        <Label>Existing vending</Label>
                        <Select
                            value={form.data.existing_vending}
                            onValueChange={(value) =>
                                form.setData('existing_vending', value)
                            }
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {existingVending.map((option) => (
                                    <SelectItem key={option} value={option}>
                                        {option}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.existing_vending} />
                    </div>
                    {isSwitching ? (
                        <div className="grid gap-2 sm:col-span-2">
                            <Label htmlFor="switch_reason">
                                Why changing providers?
                            </Label>
                            <Textarea
                                id="switch_reason"
                                value={form.data.switch_reason}
                                onChange={(e) =>
                                    form.setData('switch_reason', e.target.value)
                                }
                                rows={2}
                            />
                            <InputError message={form.errors.switch_reason} />
                        </div>
                    ) : null}
                </div>
            </section>

            <section className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="contact_name">Contact name</Label>
                    <Input
                        id="contact_name"
                        value={form.data.contact_name}
                        onChange={(e) =>
                            form.setData('contact_name', e.target.value)
                        }
                        aria-invalid={form.invalid('contact_name')}
                    />
                    <InputError message={form.errors.contact_name} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="job_title">Job title</Label>
                    <Input
                        id="job_title"
                        value={form.data.job_title}
                        onChange={(e) =>
                            form.setData('job_title', e.target.value)
                        }
                    />
                    <InputError message={form.errors.job_title} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        aria-invalid={form.invalid('email')}
                    />
                    <InputError message={form.errors.email} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="phone">Phone</Label>
                    <Input
                        id="phone"
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                        aria-invalid={form.invalid('phone')}
                    />
                    <InputError message={form.errors.phone} />
                </div>
            </section>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    <Save className="h-4 w-4" />
                    {form.processing ? 'Saving…' : 'Create request'}
                </Button>
                {onCancel ? (
                    <Button type="button" variant="outline" onClick={onCancel}>
                        Cancel
                    </Button>
                ) : null}
            </div>
        </form>
    );
}
