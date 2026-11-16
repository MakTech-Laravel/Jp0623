import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { motion } from 'motion/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { LocationAutocomplete } from '@/components/location/location-autocomplete';
import type { LocationSuggestion } from '@/components/location/location-autocomplete';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface CityFormDefaults {
    city: string;
    state: string;
    postal_code?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    is_active: boolean;
    sort_order: number;
}

interface CityFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    defaults?: CityFormDefaults;
    defaultSortOrder?: number;
    onCancel?: () => void;
}

export function CityForm({
    action,
    isEdit = false,
    defaults,
    defaultSortOrder = 0,
    onCancel,
}: CityFormProps) {
    const form = useForm(action, {
        city: defaults?.city ?? '',
        state: defaults?.state ?? '',
        postal_code: (defaults?.postal_code ?? null) as string | null,
        latitude: (defaults?.latitude ?? null) as number | null,
        longitude: (defaults?.longitude ?? null) as number | null,
        is_active: defaults?.is_active ?? true,
        sort_order: defaults?.sort_order ?? defaultSortOrder,
    });

    const initialLabel = defaults?.city
        ? `${defaults.city}, ${defaults.state}`
        : '';
    const [locationQuery, setLocationQuery] = useState(initialLabel);
    const [selectedLocation, setSelectedLocation] =
        useState<LocationSuggestion | null>(
            defaults?.city
                ? {
                      label: initialLabel,
                      address: null,
                      city: defaults.city,
                      state: defaults.state,
                      postal_code: defaults.postal_code ?? null,
                      latitude: defaults.latitude ?? 0,
                      longitude: defaults.longitude ?? 0,
                  }
                : null,
        );

    const applyLocation = (suggestion: LocationSuggestion) => {
        setSelectedLocation(suggestion);
        setLocationQuery(suggestion.label);
        form.setData((data) => ({
            ...data,
            city: suggestion.city,
            state: suggestion.state,
            postal_code: suggestion.postal_code,
            latitude: suggestion.latitude,
            longitude: suggestion.longitude,
        }));
    };

    // Typing again drops the picked suggestion (and its extra info) but keeps the
    // text as the city name, so a city can still be entered by hand.
    const changeQuery = (value: string) => {
        setLocationQuery(value);
        setSelectedLocation(null);
        form.setData((data) => ({
            ...data,
            city: value,
            postal_code: null,
            latitude: null,
            longitude: null,
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.submit({
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
                    setLocationQuery('');
                    setSelectedLocation(null);
                }
            },
        });
    };

    return (
        <motion.form
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.25 }}
            onSubmit={handleSubmit}
            className="max-w-2xl space-y-8"
        >
            <section className="space-y-5">
                <h3 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                    Location
                </h3>

                <div className="grid gap-2">
                    <LocationAutocomplete
                        id="city"
                        label="City"
                        suggestType="city"
                        value={locationQuery}
                        selected={selectedLocation}
                        placeholder="Start typing a city, e.g. Hou…"
                        onQueryChange={changeQuery}
                        onSelect={applyLocation}
                        onClearSelection={() => setSelectedLocation(null)}
                        inputClassName="border-input bg-background text-foreground shadow-xs focus-visible:border-ring focus-visible:ring-ring/50"
                        error={form.errors.city ?? null}
                    />
                </div>

                <div className="grid gap-5 sm:grid-cols-2">
                    <div className="grid content-start gap-2">
                        <Label htmlFor="state">State</Label>
                        <Input
                            id="state"
                            value={form.data.state}
                            maxLength={2}
                            onChange={(e) =>
                                form.setData(
                                    'state',
                                    e.target.value.toUpperCase(),
                                )
                            }
                            onBlur={() => form.validate('state')}
                            aria-invalid={form.invalid('state')}
                            placeholder="TX"
                        />
                        <p className="text-xs text-muted-foreground">
                            Auto-filled when you pick a city.
                        </p>
                        <InputError message={form.errors.state} />
                    </div>

                    <div className="grid content-start gap-2">
                        <Label htmlFor="postal_code">Postal code</Label>
                        <Input
                            id="postal_code"
                            value={form.data.postal_code ?? ''}
                            maxLength={20}
                            onChange={(e) =>
                                form.setData(
                                    'postal_code',
                                    e.target.value === ''
                                        ? null
                                        : e.target.value,
                                )
                            }
                            aria-invalid={form.invalid('postal_code')}
                            placeholder="77002"
                        />
                        <p className="text-xs text-muted-foreground">
                            Optional.
                        </p>
                        <InputError message={form.errors.postal_code} />
                    </div>
                </div>

                {/* Latitude / longitude are hidden: they are filled from the
                    picked suggestion and submitted with the form. */}
                <InputError message={form.errors.latitude} />
                <InputError message={form.errors.longitude} />
            </section>

            <section className="space-y-5">
                <h3 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                    Display
                </h3>

                <div className="grid gap-5 sm:grid-cols-2">
                    <div className="grid content-start gap-2">
                        <Label htmlFor="sort_order">Sort order</Label>
                        <Input
                            id="sort_order"
                            type="number"
                            min={0}
                            value={form.data.sort_order}
                            onChange={(e) =>
                                form.setData(
                                    'sort_order',
                                    Number(e.target.value) || 0,
                                )
                            }
                            onBlur={() => form.validate('sort_order')}
                            aria-invalid={form.invalid('sort_order')}
                        />
                        <p className="text-xs text-muted-foreground">
                            Lower numbers appear first.
                        </p>
                        <InputError message={form.errors.sort_order} />
                    </div>

                    <div className="grid content-start gap-2">
                        <Label htmlFor="is_active">Status</Label>
                        <label
                            htmlFor="is_active"
                            className="flex h-9 cursor-pointer items-center justify-between rounded-md border px-3 text-sm"
                        >
                            <span>
                                Active
                                <span className="ml-2 text-xs text-muted-foreground">
                                    Show on the website
                                </span>
                            </span>
                            <Checkbox
                                id="is_active"
                                checked={form.data.is_active}
                                onCheckedChange={(checked) =>
                                    form.setData('is_active', checked === true)
                                }
                            />
                        </label>
                    </div>
                </div>
            </section>

            <div className="flex flex-wrap items-center gap-3 border-t pt-5">
                <Button type="submit" disabled={form.processing}>
                    <Save className="h-4 w-4" />
                    {isEdit ? 'Save changes' : 'Create city'}
                </Button>
                {onCancel && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={form.processing}
                    >
                        Cancel
                    </Button>
                )}
            </div>
        </motion.form>
    );
}
