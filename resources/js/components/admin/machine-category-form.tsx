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

interface MachineCategoryFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    defaults?: {
        name: string;
        slug: string;
        description: string;
        sort_order: number;
        is_active: boolean;
    };
    onCancel?: () => void;
}

export function MachineCategoryForm({
    action,
    isEdit = false,
    defaults,
    onCancel,
}: MachineCategoryFormProps) {
    const form = useForm(action, {
        name: defaults?.name ?? '',
        slug: defaults?.slug ?? '',
        description: defaults?.description ?? '',
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
            <div className="grid gap-5 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        onBlur={() => form.validate('name')}
                        aria-invalid={form.invalid('name')}
                        placeholder="e.g. Combo"
                    />
                    <InputError message={form.errors.name} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="slug">
                        Slug
                        <span className="ml-1 text-xs font-normal text-muted-foreground">
                            optional
                        </span>
                    </Label>
                    <Input
                        id="slug"
                        value={form.data.slug}
                        onChange={(e) => form.setData('slug', e.target.value)}
                        onBlur={() => form.validate('slug')}
                        aria-invalid={form.invalid('slug')}
                        placeholder="auto-generated from name"
                    />
                    <InputError message={form.errors.slug} />
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    value={form.data.description}
                    onChange={(e) =>
                        form.setData('description', e.target.value)
                    }
                    onBlur={() => form.validate('description')}
                    aria-invalid={form.invalid('description')}
                    placeholder="Optional notes about this category"
                    rows={3}
                />
                <InputError message={form.errors.description} />
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
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
                    {isEdit ? 'Save changes' : 'Create category'}
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
