import { Head, router, useForm } from '@inertiajs/react';
import { FileText, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { OperatorSmsTabs } from '@/components/admin/operator-sms-tabs';
import { Badge } from '@/components/ui/badge';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import templates from '@/routes/admin/operator-sms/templates';
import { PERMISSIONS } from '@/types/permissions';

interface SmsTemplateRow {
    id: number;
    name: string;
    purpose: 'auto_vending' | 'auto_coffee' | 'manual';
    body: string;
    is_active: boolean;
    sort_order: number;
}

interface PurposeOption {
    value: string;
    label: string;
    description: string;
}

interface VariableOption {
    key: string;
    label: string;
    example: string;
}

interface Props {
    templates: SmsTemplateRow[];
    purposes: PurposeOption[];
    variables: VariableOption[];
    defaultSortOrder: number;
    instructions: string[];
}

type FormState = {
    name: string;
    purpose: string;
    body: string;
    is_active: boolean;
    sort_order: number;
};

const emptyForm = (sortOrder: number): FormState => ({
    name: '',
    purpose: 'manual',
    body: 'New lead near you: {{business_name}}, {{location}}. Contact {{contact_name}} at {{phone}}.',
    is_active: true,
    sort_order: sortOrder,
});

export default function OperatorSmsTemplates({
    templates: rows,
    purposes,
    variables,
    defaultSortOrder,
    instructions,
}: Props) {
    const { can } = usePermission();
    const [editingId, setEditingId] = useState<number | null>(null);
    const form = useForm<FormState>(emptyForm(defaultSortOrder));

    const purposeLabel = (value: string) =>
        purposes.find((purpose) => purpose.value === value)?.label ?? value;

    const startCreate = () => {
        setEditingId(null);
        form.setData(emptyForm(defaultSortOrder));
        form.clearErrors();
    };

    const startEdit = (template: SmsTemplateRow) => {
        setEditingId(template.id);
        form.setData({
            name: template.name,
            purpose: template.purpose,
            body: template.body,
            is_active: template.is_active,
            sort_order: template.sort_order,
        });
        form.clearErrors();
    };

    const insertVariable = (key: string) => {
        form.setData('body', `${form.data.body}{{${key}}}`.trim());
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editingId) {
            form.put(templates.update(editingId).url, {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingId(null);
                    form.setData(emptyForm(defaultSortOrder + 1));
                },
            });

            return;
        }

        form.post(templates.store().url, {
            preserveScroll: true,
            onSuccess: () => {
                form.setData(emptyForm(defaultSortOrder + 1));
            },
        });
    };

    return (
        <>
            <Head title="SMS templates" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="SMS templates"
                    description="Configure what nearby operators receive automatically, and keep ready-made messages for manual sends."
                    icon={FileText}
                />

                <OperatorSmsTabs active="templates" />

                <div className="rounded-xl border border-sky-300/50 bg-sky-50 px-4 py-4 text-sm text-sky-950 dark:border-sky-500/30 dark:bg-sky-950/30 dark:text-sky-100">
                    <p className="mb-2 font-medium">
                        How nearby operator SMS works
                    </p>
                    <ul className="list-disc space-y-1 pl-5">
                        {instructions.map((item) => (
                            <li key={item}>{item}</li>
                        ))}
                    </ul>
                </div>

                <div className="rounded-xl border bg-card p-4 shadow-sm">
                    <p className="mb-2 text-sm font-medium">
                        Available placeholders
                    </p>
                    <div className="flex flex-wrap gap-2">
                        {variables.map((variable) => (
                            <Button
                                key={variable.key}
                                type="button"
                                size="sm"
                                variant="outline"
                                onClick={() => insertVariable(variable.key)}
                                title={`Example: ${variable.example}`}
                            >
                                {`{{${variable.key}}}`}
                            </Button>
                        ))}
                    </div>
                </div>

                {can(PERMISSIONS.OPERATOR_SMS.SEND) && (
                    <form
                        onSubmit={submit}
                        className="space-y-4 rounded-xl border bg-card p-4 shadow-sm"
                    >
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <h2 className="font-medium">
                                {editingId
                                    ? 'Edit template'
                                    : 'Create template'}
                            </h2>
                            {editingId && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={startCreate}
                                >
                                    <Plus className="h-4 w-4" />
                                    New template
                                </Button>
                            )}
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label htmlFor="template-name">Name</Label>
                                <Input
                                    id="template-name"
                                    value={form.data.name}
                                    onChange={(e) =>
                                        form.setData('name', e.target.value)
                                    }
                                    placeholder="e.g. Manual — follow up reminder"
                                />
                                {form.errors.name && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label>Purpose</Label>
                                <Select
                                    value={form.data.purpose}
                                    onValueChange={(value) =>
                                        form.setData('purpose', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Choose purpose" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {purposes.map((purpose) => (
                                            <SelectItem
                                                key={purpose.value}
                                                value={purpose.value}
                                            >
                                                {purpose.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <p className="text-xs text-muted-foreground">
                                    {
                                        purposes.find(
                                            (purpose) =>
                                                purpose.value ===
                                                form.data.purpose,
                                        )?.description
                                    }
                                </p>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="template-body">Message body</Label>
                            <Textarea
                                id="template-body"
                                value={form.data.body}
                                onChange={(e) =>
                                    form.setData('body', e.target.value)
                                }
                                rows={4}
                                maxLength={1600}
                            />
                            {form.errors.body && (
                                <p className="text-sm text-destructive">
                                    {form.errors.body}
                                </p>
                            )}
                        </div>

                        <div className="flex flex-wrap items-center gap-4">
                            <label className="inline-flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={form.data.is_active}
                                    onCheckedChange={(value) =>
                                        form.setData(
                                            'is_active',
                                            value === true,
                                        )
                                    }
                                />
                                Active
                            </label>

                            <div className="flex items-center gap-2">
                                <Label htmlFor="sort-order">Sort</Label>
                                <Input
                                    id="sort-order"
                                    type="number"
                                    className="w-24"
                                    value={form.data.sort_order}
                                    onChange={(e) =>
                                        form.setData(
                                            'sort_order',
                                            Number(e.target.value || 0),
                                        )
                                    }
                                />
                            </div>
                        </div>

                        <Button type="submit" disabled={form.processing}>
                            {editingId ? 'Update template' : 'Save template'}
                        </Button>
                    </form>
                )}

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/50 hover:bg-muted/50">
                                <TableHead>Template</TableHead>
                                <TableHead>Purpose</TableHead>
                                <TableHead className="hidden md:table-cell">
                                    Active
                                </TableHead>
                                <TableHead className="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {rows.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={4}
                                        className="py-10 text-center text-muted-foreground"
                                    >
                                        No templates yet. Create auto and manual
                                        templates above.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                rows.map((template) => (
                                    <TableRow key={template.id}>
                                        <TableCell>
                                            <p className="font-medium">
                                                {template.name}
                                            </p>
                                            <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">
                                                {template.body}
                                            </p>
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {purposeLabel(template.purpose)}
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            <Badge
                                                variant={
                                                    template.is_active
                                                        ? 'default'
                                                        : 'outline'
                                                }
                                            >
                                                {template.is_active
                                                    ? 'Active'
                                                    : 'Off'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1">
                                                {can(
                                                    PERMISSIONS.OPERATOR_SMS
                                                        .SEND,
                                                ) && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() =>
                                                            startEdit(template)
                                                        }
                                                        title="Edit"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                )}
                                                {can(
                                                    PERMISSIONS.OPERATOR_SMS
                                                        .SEND,
                                                ) && (
                                                    <ConfirmDeleteDialog
                                                        title="Delete SMS template?"
                                                        description={`Delete “${template.name}”? This cannot be undone.`}
                                                        onConfirm={() =>
                                                            router.delete(
                                                                templates.destroy(
                                                                    template.id,
                                                                ).url,
                                                            )
                                                        }
                                                    >
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="icon"
                                                            title="Delete"
                                                        >
                                                            <Trash2 className="h-4 w-4 text-destructive" />
                                                        </Button>
                                                    </ConfirmDeleteDialog>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}

OperatorSmsTemplates.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Operator SMS', href: '/admin/operator-sms' },
        { title: 'Templates', href: '#' },
    ],
};
