import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type LeadPriceEditorProps = {
    price: number;
    locked: boolean;
    updateUrl: string;
};

export function LeadPriceEditor({
    price,
    locked,
    updateUrl,
}: LeadPriceEditorProps) {
    const [value, setValue] = useState(String(price));
    const [error, setError] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setValue(String(price));
    }, [price]);

    function handleSave() {
        const nextPrice = Number(value);

        if (!Number.isInteger(nextPrice) || nextPrice < 1) {
            setError('Enter a whole-dollar price of at least 1.');
            return;
        }

        setSaving(true);
        setError(null);

        router.put(
            updateUrl,
            { price: nextPrice },
            {
                preserveScroll: true,
                onError: (errors) => {
                    setError(errors.price ?? 'Unable to update price.');
                },
                onFinish: () => setSaving(false),
            },
        );
    }

    return (
        <div className="space-y-3 rounded-lg border p-4">
            <div>
                <Label htmlFor="lead-price">Lead price (USD)</Label>
                <p className="mt-1 text-xs text-muted-foreground">
                    {locked
                        ? 'Price is locked because this lead has already been sold.'
                        : 'Operators pay this amount to unlock the lead.'}
                </p>
            </div>
            <div className="flex flex-wrap items-end gap-2">
                <Input
                    id="lead-price"
                    type="number"
                    min={1}
                    value={value}
                    disabled={locked || saving}
                    onChange={(e) => setValue(e.target.value)}
                    className="max-w-[10rem]"
                />
                {!locked ? (
                    <Button
                        type="button"
                        disabled={saving || Number(value) === price}
                        onClick={handleSave}
                    >
                        {saving ? 'Saving…' : 'Update price'}
                    </Button>
                ) : null}
            </div>
            <InputError message={error} />
        </div>
    );
}
