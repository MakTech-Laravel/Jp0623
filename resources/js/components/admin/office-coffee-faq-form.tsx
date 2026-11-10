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

interface OfficeCoffeeFaqFormProps {
    action: UrlMethodPair;
    isEdit?: boolean;
    defaults?: {
        question: string;
        answer: string;
        sort_order: number;
        is_active: boolean;
    };
    onCancel?: () => void;
}

export function OfficeCoffeeFaqForm({
    action,
    isEdit = false,
    defaults,
    onCancel,
}: OfficeCoffeeFaqFormProps) {
    const form = useForm(action, {
        question: defaults?.question ?? '',
        answer: defaults?.answer ?? '',
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
                <Label htmlFor="question">Question</Label>
                <Input
                    id="question"
                    value={form.data.question}
                    onChange={(e) => form.setData('question', e.target.value)}
                    onBlur={() => form.validate('question')}
                    aria-invalid={form.invalid('question')}
                    placeholder="e.g. Is coffee service free?"
                />
                <InputError message={form.errors.question} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="answer">Answer</Label>
                <Textarea
                    id="answer"
                    value={form.data.answer}
                    onChange={(e) => form.setData('answer', e.target.value)}
                    onBlur={() => form.validate('answer')}
                    aria-invalid={form.invalid('answer')}
                    placeholder="Write the answer shown on the office coffee page"
                    rows={5}
                />
                <InputError message={form.errors.answer} />
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
                    {isEdit ? 'Save changes' : 'Create FAQ'}
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
