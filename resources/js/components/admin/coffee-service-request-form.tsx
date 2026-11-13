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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type CoffeeServiceRequestFormProps = {
    action: UrlMethodPair;
    defaultPrice: number;
    onCancel?: () => void;
};

export function CoffeeServiceRequestForm({
    action,
    defaultPrice,
    onCancel,
}: CoffeeServiceRequestFormProps) {
    const form = useForm(action, {
        business_name: '',
        zip_code: '',
        address: null as string | null,
        city: '',
        state: '',
        postal_code: null as string | null,
        latitude: null as number | null,
        longitude: null as number | null,
        contact_name: '',
        phone: '',
        email: '',
        notes: '',
        price: defaultPrice,
    });

    const [locationQuery, setLocationQuery] = useState('');
    const [selectedLocation, setSelectedLocation] =
        useState<LocationSuggestion | null>(null);

    function applyLocation(suggestion: LocationSuggestion | null) {
        setSelectedLocation(suggestion);
        setLocationQuery(suggestion?.label ?? '');
        form.setData((data) => ({
            ...data,
            zip_code: suggestion?.postal_code ?? suggestion?.label ?? '',
            address: suggestion?.address ?? null,
            city: suggestion?.city ?? '',
            state: suggestion?.state ?? '',
            postal_code: suggestion?.postal_code ?? null,
            latitude: suggestion?.latitude ?? null,
            longitude: suggestion?.longitude ?? null,
        }));
    }

    function handleSubmit(event: React.FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            notes: data.notes || null,
            price: Number(data.price),
        }));
        form.submit({ preserveScroll: true });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-4 sm:grid-cols-2">
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
                            form.errors.zip_code ??
                            null
                        }
                    />
                </div>

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
                    <Label htmlFor="phone">Phone</Label>
                    <Input
                        id="phone"
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                        aria-invalid={form.invalid('phone')}
                    />
                    <InputError message={form.errors.phone} />
                </div>
                <div className="grid gap-2 sm:col-span-2">
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
                <div className="grid gap-2 sm:col-span-2">
                    <Label htmlFor="notes">Notes</Label>
                    <Textarea
                        id="notes"
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                        rows={3}
                    />
                    <InputError message={form.errors.notes} />
                </div>
            </div>

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
