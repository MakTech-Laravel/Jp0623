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

interface ArticleFormDefaults {
    category: string;
    title: string;
    slug: string;
    excerpt: string;
    body: string;
    is_active: boolean;
    sort_order: number;
}

interface ArticleFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    currentImageUrl?: string | null;
    defaults?: ArticleFormDefaults;
    defaultSortOrder?: number;
    onCancel?: () => void;
}

export function ArticleForm({
    action,
    isEdit = false,
    currentImageUrl = null,
    defaults,
    defaultSortOrder = 0,
    onCancel,
}: ArticleFormProps) {
    const form = useForm(action, {
        category: defaults?.category ?? '',
        title: defaults?.title ?? '',
        slug: defaults?.slug ?? '',
        excerpt: defaults?.excerpt ?? '',
        body: defaults?.body ?? '',
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
                <Label>Cover image (optional)</Label>
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
                    placeholder="Drag & drop a cover image, or click to browse"
                    hint="PNG, JPG or WEBP up to 4MB"
                    error={form.errors.image}
                    classNames={{ wrapper: 'sm:max-w-md' }}
                />
            </div>

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="category">Category</Label>
                <Input
                    id="category"
                    value={form.data.category}
                    onChange={(e) => form.setData('category', e.target.value)}
                    onBlur={() => form.validate('category')}
                    aria-invalid={form.invalid('category')}
                    placeholder="Vending management"
                />
                <InputError message={form.errors.category} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="title">Title</Label>
                <Input
                    id="title"
                    value={form.data.title}
                    onChange={(e) => form.setData('title', e.target.value)}
                    onBlur={() => form.validate('title')}
                    aria-invalid={form.invalid('title')}
                    placeholder="How to choose a vending company for multiple sites"
                />
                <InputError message={form.errors.title} />
            </div>

            <div className="grid gap-2 sm:max-w-md">
                <Label htmlFor="slug">Slug</Label>
                <Input
                    id="slug"
                    value={form.data.slug}
                    onChange={(e) => form.setData('slug', e.target.value)}
                    onBlur={() => form.validate('slug')}
                    aria-invalid={form.invalid('slug')}
                    placeholder="auto-generated from the title"
                />
                <p className="text-xs text-muted-foreground">
                    Used in the page link: /resources/your-slug. Leave blank to
                    generate it from the title.
                </p>
                <InputError message={form.errors.slug} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="excerpt">Excerpt</Label>
                <Textarea
                    id="excerpt"
                    value={form.data.excerpt}
                    onChange={(e) => form.setData('excerpt', e.target.value)}
                    onBlur={() => form.validate('excerpt')}
                    aria-invalid={form.invalid('excerpt')}
                    rows={2}
                    placeholder="Short summary shown on the article card."
                />
                <InputError message={form.errors.excerpt} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="body">Body</Label>
                <Textarea
                    id="body"
                    value={form.data.body}
                    onChange={(e) => form.setData('body', e.target.value)}
                    onBlur={() => form.validate('body')}
                    aria-invalid={form.invalid('body')}
                    rows={12}
                    placeholder="Write the article. Leave a blank line between paragraphs."
                />
                <p className="text-xs text-muted-foreground">
                    Plain text. A blank line starts a new paragraph.
                </p>
                <InputError message={form.errors.body} />
            </div>

            <div className="grid gap-5 sm:max-w-md sm:grid-cols-2">
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
                    {isEdit ? 'Save changes' : 'Create article'}
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
