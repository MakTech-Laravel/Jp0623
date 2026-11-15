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

interface LocationTypeFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    currentImageUrl?: string | null;
    kindOptions?: string[];
    defaults?: {
        name: string;
        slug: string;
        description: string;
        kind: string;
        sort_order: number;
        is_active: boolean;
        show_on_industries: boolean;
    };
    onCancel?: () => void;
}

export function LocationTypeForm({
    action,
    isEdit = false,
    currentImageUrl = null,
    kindOptions = [],
    defaults,
    onCancel,
}: LocationTypeFormProps) {
    const form = useForm(action, {
        name: defaults?.name ?? '',
        slug: defaults?.slug ?? '',
        description: defaults?.description ?? '',
        kind: defaults?.kind ?? '',
        sort_order: defaults?.sort_order ?? 0,
        is_active: defaults?.is_active ?? true,
        show_on_industries: defaults?.show_on_industries ?? true,
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
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    onBlur={() => form.validate('name')}
                    aria-invalid={form.invalid('name')}
                    placeholder="e.g. Offices"
                    className="sm:max-w-md"
                />
                <p className="text-xs text-muted-foreground">
                    Shown as the “Type of location” option on the request forms
                    and as the industry name on the website.
                </p>
                <InputError message={form.errors.name} />
            </div>

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="slug">Slug</Label>
                <Input
                    id="slug"
                    value={form.data.slug}
                    onChange={(e) => form.setData('slug', e.target.value)}
                    onBlur={() => form.validate('slug')}
                    aria-invalid={form.invalid('slug')}
                    placeholder="offices"
                />
                <p className="text-xs text-muted-foreground">
                    Used in the page link (/industries/slug). Leave empty to
                    generate it from the name.
                </p>
                <InputError message={form.errors.slug} />
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
                    rows={3}
                    placeholder="Quiet machines, healthy options and coffee for daytime teams."
                />
                <InputError message={form.errors.description} />
            </div>

            <div className="grid gap-2">
                <Label>Image</Label>
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
                    placeholder="Drag & drop an image, or click to browse"
                    hint="PNG, JPG or WEBP up to 4MB"
                    error={form.errors.image}
                    classNames={{ wrapper: 'sm:max-w-md' }}
                />
            </div>

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="kind">Machine kind</Label>
                <Input
                    id="kind"
                    list="machine-kind-options"
                    value={form.data.kind}
                    onChange={(e) => form.setData('kind', e.target.value)}
                    onBlur={() => form.validate('kind')}
                    aria-invalid={form.invalid('kind')}
                    placeholder="combo"
                />
                <datalist id="machine-kind-options">
                    {kindOptions.map((kind) => (
                        <option key={kind} value={kind} />
                    ))}
                </datalist>
                <p className="text-xs text-muted-foreground">
                    Picks the machines suggested on this industry’s page.
                </p>
                <InputError message={form.errors.kind} />
            </div>

            <div className="grid gap-5 sm:grid-cols-3">
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

                <div className="flex items-center gap-2 pt-7">
                    <Checkbox
                        id="show_on_industries"
                        checked={form.data.show_on_industries}
                        onCheckedChange={(checked) =>
                            form.setData('show_on_industries', checked === true)
                        }
                    />
                    <Label
                        htmlFor="show_on_industries"
                        className="font-normal"
                    >
                        Show on website
                    </Label>
                </div>
            </div>
            <p className="-mt-3 text-xs text-muted-foreground">
                “Show on website” lists it on the home page and Industries
                pages. Untick it to keep it in the request form only (like
                “Other”).
            </p>

            <div className="flex flex-wrap items-center gap-3">
                <Button type="submit" disabled={form.processing}>
                    <Save className="h-4 w-4" />
                    {isEdit ? 'Save changes' : 'Create location type'}
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
