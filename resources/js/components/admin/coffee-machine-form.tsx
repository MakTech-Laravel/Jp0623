import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { motion } from 'motion/react';
import FileUpload from '@/components/file-upload';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

function imageMimeFromUrl(url: string): string {
    const path = url.split('?')[0]?.toLowerCase() ?? '';

    if (path.endsWith('.png')) {
        return 'image/png';
    }

    if (path.endsWith('.webp')) {
        return 'image/webp';
    }

    if (path.endsWith('.gif')) {
        return 'image/gif';
    }

    if (path.endsWith('.svg')) {
        return 'image/svg+xml';
    }

    return 'image/jpeg';
}

interface CoffeeMachineFormDefaults {
    title: string;
    text: string;
    tag: string;
    is_active: boolean;
    sort_order: number;
}

interface CoffeeMachineFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    currentImageUrl?: string | null;
    defaults?: CoffeeMachineFormDefaults;
    defaultSortOrder?: number;
    onCancel?: () => void;
}

export function CoffeeMachineForm({
    action,
    isEdit = false,
    currentImageUrl = null,
    defaults,
    defaultSortOrder = 0,
    onCancel,
}: CoffeeMachineFormProps) {
    const form = useForm(action, {
        title: defaults?.title ?? '',
        text: defaults?.text ?? '',
        tag: defaults?.tag ?? '',
        is_active: defaults?.is_active ?? true,
        sort_order: defaults?.sort_order ?? defaultSortOrder,
        image: null as File | null,
        remove_image: false as boolean,
    });

    const existingImage =
        isEdit &&
        currentImageUrl &&
        !form.data.image &&
        !form.data.remove_image
            ? [
                  {
                      id: 'current',
                      path: currentImageUrl,
                      url: currentImageUrl,
                      mime_type: imageMimeFromUrl(currentImageUrl),
                  },
              ]
            : [];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const hasNewImage = form.data.image instanceof File;

        form.submit({
            // Only force FormData when uploading a file. Always forcing sends an
            // empty `image` field that fails Laravel's `image` validation rule.
            ...(hasNewImage ? { forceFormData: true } : {}),
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
                <Label>Machine image</Label>
                <FileUpload
                    accept="image/*"
                    maxSize={4}
                    value={form.data.image}
                    onChange={(file) => {
                        form.setData('image', (file as File | null) ?? null);
                        form.setData('remove_image', false);
                    }}
                    existingFiles={existingImage}
                    onRemoveExisting={() => form.setData('remove_image', true)}
                    placeholder="Drag & drop a machine photo, or click to browse"
                    hint="PNG, JPG or WEBP up to 4MB"
                    error={form.errors.image}
                    classNames={{ wrapper: 'sm:max-w-md' }}
                />
            </div>

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="title">Title</Label>
                <Input
                    id="title"
                    value={form.data.title}
                    onChange={(e) => form.setData('title', e.target.value)}
                    onBlur={() => form.validate('title')}
                    aria-invalid={form.invalid('title')}
                    placeholder="Single-cup brewer"
                />
                <InputError message={form.errors.title} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="text">Description</Label>
                <Textarea
                    id="text"
                    value={form.data.text}
                    onChange={(e) => form.setData('text', e.target.value)}
                    onBlur={() => form.validate('text')}
                    aria-invalid={form.invalid('text')}
                    rows={3}
                    placeholder="Fast, no-mess cups for small teams."
                />
                <InputError message={form.errors.text} />
            </div>

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="tag">Tag</Label>
                <Input
                    id="tag"
                    value={form.data.tag}
                    onChange={(e) => form.setData('tag', e.target.value)}
                    onBlur={() => form.validate('tag')}
                    aria-invalid={form.invalid('tag')}
                    placeholder="Up to 25 people"
                />
                <p className="text-xs text-muted-foreground">
                    Small label shown at the bottom of the card.
                </p>
                <InputError message={form.errors.tag} />
            </div>

            <div className="grid gap-5 sm:grid-cols-2 sm:max-w-md">
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
                    {isEdit ? 'Save changes' : 'Create coffee machine'}
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
