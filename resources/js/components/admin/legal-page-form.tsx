import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Plus, Save, Trash2 } from 'lucide-react';
import { motion } from 'motion/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { AdminLegalPageSection } from '@/types/admin';

interface LegalPageFormProps {
    action: UrlMethodPair;
    defaults: {
        title: string;
        last_updated_on: string | null;
        sections: AdminLegalPageSection[];
    };
    onCancel?: () => void;
}

export function LegalPageForm({
    action,
    defaults,
    onCancel,
}: LegalPageFormProps) {
    const form = useForm(action, {
        title: defaults.title,
        last_updated_on: defaults.last_updated_on ?? '',
        sections: defaults.sections.map((section) => ({
            heading: section.heading ?? '',
            body: section.body,
        })),
    });

    const sections = form.data.sections;

    const updateSection = (
        index: number,
        field: 'heading' | 'body',
        value: string,
    ) => {
        form.setData(
            'sections',
            sections.map((section, i) =>
                i === index ? { ...section, [field]: value } : section,
            ),
        );
    };

    const moveSection = (index: number, direction: -1 | 1) => {
        const target = index + direction;

        if (target < 0 || target >= sections.length) {
            return;
        }

        const next = [...sections];
        [next[index], next[target]] = [next[target], next[index]];
        form.setData('sections', next);
    };

    const removeSection = (index: number) => {
        form.setData(
            'sections',
            sections.filter((_, i) => i !== index),
        );
    };

    const addSection = () => {
        form.setData('sections', [...sections, { heading: '', body: '' }]);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.submit({ preserveScroll: true });
    };

    const sectionError = (index: number, field: 'heading' | 'body') =>
        (form.errors as Record<string, string | undefined>)[
            `sections.${index}.${field}`
        ];

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
                    <Label htmlFor="title">Page title</Label>
                    <Input
                        id="title"
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        onBlur={() => form.validate('title')}
                        aria-invalid={form.invalid('title')}
                    />
                    <InputError message={form.errors.title} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="last_updated_on">Last updated date</Label>
                    <Input
                        id="last_updated_on"
                        type="date"
                        value={form.data.last_updated_on}
                        onChange={(e) =>
                            form.setData('last_updated_on', e.target.value)
                        }
                        onBlur={() => form.validate('last_updated_on')}
                        aria-invalid={form.invalid('last_updated_on')}
                    />
                    <p className="text-xs text-muted-foreground">
                        Leave empty to hide the "Last updated" line.
                    </p>
                    <InputError message={form.errors.last_updated_on} />
                </div>
            </div>

            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-sm font-semibold">Sections</h2>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={addSection}
                    >
                        <Plus className="h-4 w-4" /> Add section
                    </Button>
                </div>
                <InputError message={form.errors.sections} />

                {sections.map((section, index) => (
                    <div
                        key={index}
                        className="space-y-3 rounded-lg border bg-muted/20 p-4"
                    >
                        <div className="flex items-center justify-between gap-2">
                            <span className="text-xs font-medium text-muted-foreground">
                                Section {index + 1}
                            </span>
                            <div className="flex items-center gap-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    title="Move up"
                                    disabled={index === 0}
                                    onClick={() => moveSection(index, -1)}
                                >
                                    <ArrowUp className="h-4 w-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    title="Move down"
                                    disabled={index === sections.length - 1}
                                    onClick={() => moveSection(index, 1)}
                                >
                                    <ArrowDown className="h-4 w-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    title="Remove section"
                                    className="text-muted-foreground hover:text-destructive"
                                    disabled={sections.length === 1}
                                    onClick={() => removeSection(index)}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`heading-${index}`}>
                                Heading (optional)
                            </Label>
                            <Input
                                id={`heading-${index}`}
                                value={section.heading}
                                onChange={(e) =>
                                    updateSection(
                                        index,
                                        'heading',
                                        e.target.value,
                                    )
                                }
                            />
                            <InputError
                                message={sectionError(index, 'heading')}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`body-${index}`}>Content</Label>
                            <Textarea
                                id={`body-${index}`}
                                rows={8}
                                value={section.body}
                                onChange={(e) =>
                                    updateSection(index, 'body', e.target.value)
                                }
                                placeholder="Leave a blank line between paragraphs"
                            />
                            <InputError message={sectionError(index, 'body')} />
                        </div>
                    </div>
                ))}
            </div>

            <div className="flex flex-wrap items-center gap-3">
                <Button type="submit" disabled={form.processing}>
                    <Save className="h-4 w-4" />
                    Save changes
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
