import { router, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    CheckCircle2,
    FileSpreadsheet,
    Loader2,
    RefreshCw,
    Upload,
    X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ChangeEvent, DragEvent } from 'react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';
import cities from '@/routes/admin/cities';
import type { CityImportReport } from '@/types/admin';

const MAX_MB = 2;
const ALLOWED_EXTENSIONS = ['csv', 'xlsx', 'xls'];

function formatBytes(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function validateFile(file: File): string | null {
    const ext = file.name.split('.').pop()?.toLowerCase() ?? '';

    if (!ALLOWED_EXTENSIONS.includes(ext)) {
        return 'Please choose a CSV, XLSX or XLS file.';
    }

    if (file.size > MAX_MB * 1024 * 1024) {
        return `The file is larger than ${MAX_MB} MB.`;
    }

    return null;
}

export function CityImportCard() {
    const form = useForm(cities.import(), { file: null as File | null });
    const inputRef = useRef<HTMLInputElement>(null);
    const dragDepth = useRef(0);
    const [isDragging, setIsDragging] = useState(false);
    const [localError, setLocalError] = useState<string | null>(null);
    const [report, setReport] = useState<CityImportReport | null>(null);

    // The import summary / row errors arrive as flash data after the redirect.
    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.import_report as CityImportReport | undefined;

            if (data) {
                setReport(data);
            }
        });
    }, []);

    const pickFile = (file: File | undefined) => {
        if (!file) {
            return;
        }

        const problem = validateFile(file);

        setLocalError(problem);
        form.clearErrors();

        if (!problem) {
            form.setData('file', file);
        }
    };

    const handleInput = (e: ChangeEvent<HTMLInputElement>) => {
        pickFile(e.target.files?.[0]);
        e.target.value = '';
    };

    const handleDrop = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        dragDepth.current = 0;
        setIsDragging(false);
        pickFile(e.dataTransfer.files?.[0]);
    };

    const clearFile = () => {
        form.setData('file', null);
        form.clearErrors();
        setLocalError(null);
    };

    const handleImport = (e: React.FormEvent) => {
        e.preventDefault();
        setReport(null);
        form.submit({
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const file = form.data.file;
    const error = localError ?? form.errors.file;

    return (
        <div className="space-y-4">
            <form
                onSubmit={handleImport}
                className="overflow-hidden rounded-xl border bg-card shadow-sm"
            >
                <div className="flex items-center gap-3 border-b bg-muted/30 px-5 py-4">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <FileSpreadsheet className="h-5 w-5" />
                    </div>
                    <div>
                        <h2 className="text-sm font-semibold">
                            Bulk upload cities
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Add or update many cities at once from a CSV or
                            Excel file.
                        </p>
                    </div>
                </div>

                <div className="p-5">
                    {/* Upload action */}
                    <div className="space-y-3">
                        {file ? (
                            <div className="flex items-center gap-3 rounded-lg border bg-muted/20 p-3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
                                    <FileSpreadsheet className="h-5 w-5" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p
                                        className="truncate text-sm font-medium"
                                        title={file.name}
                                    >
                                        {file.name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {formatBytes(file.size)}
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    onClick={clearFile}
                                    disabled={form.processing}
                                    aria-label="Remove file"
                                    className="shrink-0 text-muted-foreground hover:text-destructive"
                                >
                                    <X className="h-4 w-4" />
                                </Button>
                            </div>
                        ) : (
                            <div
                                role="button"
                                tabIndex={0}
                                aria-label="Choose a CSV or Excel file"
                                onClick={() => inputRef.current?.click()}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        e.preventDefault();
                                        inputRef.current?.click();
                                    }
                                }}
                                onDragOver={(e) => e.preventDefault()}
                                onDragEnter={(e) => {
                                    e.preventDefault();
                                    dragDepth.current += 1;
                                    setIsDragging(true);
                                }}
                                onDragLeave={(e) => {
                                    e.preventDefault();
                                    dragDepth.current = Math.max(
                                        0,
                                        dragDepth.current - 1,
                                    );

                                    if (dragDepth.current === 0) {
                                        setIsDragging(false);
                                    }
                                }}
                                onDrop={handleDrop}
                                className={cn(
                                    'flex h-40 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 text-center transition-colors select-none',
                                    'hover:border-primary hover:bg-primary/5',
                                    isDragging
                                        ? 'border-primary bg-primary/10'
                                        : 'border-border',
                                    error && 'border-destructive',
                                )}
                            >
                                <Upload className="h-7 w-7 text-muted-foreground" />
                                <p className="text-sm font-medium">
                                    Drag & drop your file here, or{' '}
                                    <span className="text-primary underline underline-offset-2">
                                        browse
                                    </span>
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    CSV, XLSX or XLS · max {MAX_MB} MB
                                </p>
                            </div>
                        )}

                        <input
                            ref={inputRef}
                            type="file"
                            accept=".csv,.xlsx,.xls"
                            onChange={handleInput}
                            className="sr-only"
                            tabIndex={-1}
                        />

                        {error && (
                            <p className="flex items-start gap-1.5 text-sm text-destructive">
                                <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
                                <span>{error}</span>
                            </p>
                        )}

                        <Button
                            type="submit"
                            disabled={form.processing || !file}
                            className="w-full sm:w-auto"
                        >
                            {form.processing ? (
                                <>
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                    Importing…
                                </>
                            ) : (
                                <>
                                    <Upload className="h-4 w-4" />
                                    Import cities
                                </>
                            )}
                        </Button>
                    </div>
                </div>
            </form>

            {report && (
                <div className="rounded-xl border bg-card p-5 shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex flex-wrap items-center gap-2 text-sm font-medium">
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-3 py-1 text-green-700 dark:text-green-400">
                                <CheckCircle2 className="h-4 w-4" />
                                {report.created} added
                            </span>
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-3 py-1 text-blue-700 dark:text-blue-400">
                                <RefreshCw className="h-4 w-4" />
                                {report.updated} updated
                            </span>
                            <span
                                className={cn(
                                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1',
                                    report.skipped > 0
                                        ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400'
                                        : 'bg-muted text-muted-foreground',
                                )}
                            >
                                <AlertTriangle className="h-4 w-4" />
                                {report.skipped} skipped
                            </span>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => setReport(null)}
                        >
                            <X className="h-4 w-4" /> Dismiss
                        </Button>
                    </div>

                    {report.errors.length > 0 && (
                        <div className="mt-4 max-h-64 overflow-y-auto rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/50 hover:bg-muted/50">
                                        <TableHead className="w-20">
                                            Row
                                        </TableHead>
                                        <TableHead>Problem</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {report.errors.slice(0, 50).map((item) => (
                                        <TableRow
                                            key={`${item.row}-${item.message}`}
                                        >
                                            <TableCell className="font-medium">
                                                {item.row}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {item.message}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {report.errors.length > 50 && (
                                        <TableRow>
                                            <TableCell
                                                colSpan={2}
                                                className="text-muted-foreground"
                                            >
                                                …and {report.errors.length - 50}{' '}
                                                more.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
