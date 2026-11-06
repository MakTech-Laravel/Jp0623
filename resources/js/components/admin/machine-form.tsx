import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { motion } from 'motion/react';
import { NameSlugFields } from '@/components/admin/name-slug-fields';
import FileUpload from '@/components/file-upload';
import InputError from '@/components/input-error';
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
import type { MachineCategoryOption } from '@/types/admin';

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

interface MachineFormDefaults {
    machine_category_id: number | null;
    name: string;
    slug: string;
    kind: string;
    tags: string;
    description: string;
    capacity: string;
    dimensions: string;
    best_for: string;
    status: string;
    is_featured: boolean;
    is_active: boolean;
    sort_order: number;
}

interface MachineFormProps {
    action: UrlMethodPair;
    categories: MachineCategoryOption[];
    isEdit?: boolean;
    currentImageUrl?: string | null;
    defaults?: MachineFormDefaults;
    onCancel?: () => void;
}

export function MachineForm({
    action,
    categories,
    isEdit = false,
    currentImageUrl = null,
    defaults,
    onCancel,
}: MachineFormProps) {
    const form = useForm(action, {
        machine_category_id: defaults?.machine_category_id ?? null,
        name: defaults?.name ?? '',
        slug: defaults?.slug ?? '',
        kind: defaults?.kind ?? '',
        tags: defaults?.tags ?? '',
        description: defaults?.description ?? '',
        capacity: defaults?.capacity ?? '',
        dimensions: defaults?.dimensions ?? '',
        best_for: defaults?.best_for ?? '',
        status: defaults?.status ?? 'Available nationwide',
        is_featured: defaults?.is_featured ?? false,
        is_active: defaults?.is_active ?? true,
        sort_order: defaults?.sort_order ?? 0,
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

            <div className="grid gap-2">
                <Label htmlFor="machine_category_id">Category</Label>
                <Select
                    value={
                        form.data.machine_category_id
                            ? String(form.data.machine_category_id)
                            : undefined
                    }
                    onValueChange={(value) =>
                        form.setData('machine_category_id', Number(value))
                    }
                >
                    <SelectTrigger
                        id="machine_category_id"
                        className="w-full sm:max-w-md"
                        aria-invalid={form.invalid('machine_category_id')}
                    >
                        <SelectValue placeholder="Select a category" />
                    </SelectTrigger>
                    <SelectContent>
                        {categories.map((category) => (
                            <SelectItem
                                key={category.id}
                                value={String(category.id)}
                            >
                                {category.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={form.errors.machine_category_id} />
                {categories.length === 0 && (
                    <p className="text-xs text-destructive">
                        Create a category before adding a machine.
                    </p>
                )}
            </div>

            <NameSlugFields
                name={form.data.name}
                slug={form.data.slug}
                nameError={form.errors.name}
                slugError={form.errors.slug}
                nameInvalid={form.invalid('name')}
                slugInvalid={form.invalid('slug')}
                namePlaceholder="Snack & Drink Combo"
                autoSyncInitially={!isEdit}
                onNameChange={(value) => form.setData('name', value)}
                onSlugChange={(value) => form.setData('slug', value)}
                onNameBlur={() => form.validate('name')}
                onSlugBlur={() => form.validate('slug')}
            />

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="kind">Kind</Label>
                <Input
                    id="kind"
                    value={form.data.kind}
                    onChange={(e) => form.setData('kind', e.target.value)}
                    onBlur={() => form.validate('kind')}
                    aria-invalid={form.invalid('kind')}
                    placeholder="combo"
                />
                <InputError message={form.errors.kind} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="tags">Tags</Label>
                <Input
                    id="tags"
                    value={form.data.tags}
                    onChange={(e) => form.setData('tags', e.target.value)}
                    onBlur={() => form.validate('tags')}
                    aria-invalid={form.invalid('tags')}
                    placeholder="Combo, Card + phone"
                />
                <p className="text-xs text-muted-foreground">
                    Separate tags with commas.
                </p>
                <InputError message={form.errors.tags} />
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
                    rows={4}
                />
                <InputError message={form.errors.description} />
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="capacity">Capacity</Label>
                    <Input
                        id="capacity"
                        value={form.data.capacity}
                        onChange={(e) =>
                            form.setData('capacity', e.target.value)
                        }
                        onBlur={() => form.validate('capacity')}
                        aria-invalid={form.invalid('capacity')}
                    />
                    <InputError message={form.errors.capacity} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="dimensions">Dimensions</Label>
                    <Input
                        id="dimensions"
                        value={form.data.dimensions}
                        onChange={(e) =>
                            form.setData('dimensions', e.target.value)
                        }
                        onBlur={() => form.validate('dimensions')}
                        aria-invalid={form.invalid('dimensions')}
                    />
                    <InputError message={form.errors.dimensions} />
                </div>
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="best_for">Best for</Label>
                    <Input
                        id="best_for"
                        value={form.data.best_for}
                        onChange={(e) =>
                            form.setData('best_for', e.target.value)
                        }
                        onBlur={() => form.validate('best_for')}
                        aria-invalid={form.invalid('best_for')}
                    />
                    <InputError message={form.errors.best_for} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <Input
                        id="status"
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                        onBlur={() => form.validate('status')}
                        aria-invalid={form.invalid('status')}
                    />
                    <InputError message={form.errors.status} />
                </div>
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
                        id="is_featured"
                        checked={form.data.is_featured}
                        onCheckedChange={(checked) =>
                            form.setData('is_featured', checked === true)
                        }
                    />
                    <Label htmlFor="is_featured" className="font-normal">
                        Featured
                    </Label>
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
                <Button
                    type="submit"
                    disabled={form.processing || categories.length === 0}
                >
                    <Save className="h-4 w-4" />
                    {isEdit ? 'Save changes' : 'Create machine'}
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
