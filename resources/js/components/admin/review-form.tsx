import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { motion } from 'motion/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface ReviewFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    defaults?: {
        quote: string;
        role: string;
        org: string;
        rating: number;
        sort_order: number;
        is_active: boolean;
    };
    onCancel?: () => void;
}

export function ReviewForm({
    action,
    isEdit = false,
    defaults,
    onCancel,
}: ReviewFormProps) {
    const form = useForm(action, {
        quote: defaults?.quote ?? '',
        role: defaults?.role ?? '',
        org: defaults?.org ?? '',
        rating: defaults?.rating ?? 5,
        sort_order: defaults?.sort_order ?? 0,
        is_active: defaults?.is_active ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.submit({
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
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
            className="space-y-6"
        >
            <div className="grid gap-2">
                <Label htmlFor="quote">Review</Label>
                <Textarea
                    id="quote"
                    value={form.data.quote}
                    onChange={(e) => form.setData('quote', e.target.value)}
                    onBlur={() => form.validate('quote')}
                    aria-invalid={form.invalid('quote')}
                    placeholder="What the customer said"
                    rows={5}
                />
                <InputError message={form.errors.quote} />
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="role">Role</Label>
                    <Input
                        id="role"
                        value={form.data.role}
                        onChange={(e) => form.setData('role', e.target.value)}
                        onBlur={() => form.validate('role')}
                        aria-invalid={form.invalid('role')}
                        placeholder="e.g. Facilities Manager"
                    />
                    <InputError message={form.errors.role} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="org">Organization</Label>
                    <Input
                        id="org"
                        value={form.data.org}
                        onChange={(e) => form.setData('org', e.target.value)}
                        onBlur={() => form.validate('org')}
                        aria-invalid={form.invalid('org')}
                        placeholder="e.g. Logistics company, TX"
                    />
                    <InputError message={form.errors.org} />
                </div>
            </div>

            <div className="grid gap-5 sm:grid-cols-3">
                <div className="grid gap-2">
                    <Label htmlFor="rating">Rating (1-5 stars)</Label>
                    <Input
                        id="rating"
                        type="number"
                        min={1}
                        max={5}
                        value={form.data.rating}
                        onChange={(e) =>
                            form.setData('rating', Number(e.target.value) || 1)
                        }
                        onBlur={() => form.validate('rating')}
                        aria-invalid={form.invalid('rating')}
                    />
                    <InputError message={form.errors.rating} />
                </div>

                <div className="grid gap-2">
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
                    <InputError message={form.errors.sort_order} />
                </div>

                <div className="flex items-center gap-2 pt-7">
                    <Checkbox
                        id="is_active"
                        checked={form.data.is_active}
                        onCheckedChange={(checked) =>
                            form.setData('is_active', checked === true)
                        }
                    />
                    <Label htmlFor="is_active" className="font-normal">
                        Active
                    </Label>
                </div>
            </div>

            <div className="flex flex-wrap items-center gap-3">
                <Button type="submit" disabled={form.processing}>
                    <Save className="h-4 w-4" />
                    {isEdit ? 'Save changes' : 'Create review'}
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
