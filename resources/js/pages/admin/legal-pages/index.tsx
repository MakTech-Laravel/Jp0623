import { Head, Link } from '@inertiajs/react';
import { ExternalLink, Pencil, Scale } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import legalPages from '@/routes/admin/legal-pages';
import type { AdminLegalPageSummary } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

export default function LegalPagesIndex({
    legalPages: pages,
}: {
    legalPages: AdminLegalPageSummary[];
}) {
    const { can } = usePermission();

    return (
        <>
            <Head title="Legal pages" />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Legal pages"
                    description="Manage the Privacy policy, Terms & conditions and Operator terms shown on the public site."
                    icon={Scale}
                />

                <div className="overflow-hidden rounded-xl border bg-card shadow-sm">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Page</TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Sections
                                    </TableHead>
                                    <TableHead className="hidden md:table-cell">
                                        Last updated
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pages.map((page) => (
                                    <TableRow key={page.id}>
                                        <TableCell>
                                            <p className="font-medium">
                                                {page.title}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                /legal/{page.slug}
                                            </p>
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            {page.sections_count}
                                        </TableCell>
                                        <TableCell className="hidden md:table-cell">
                                            {page.last_updated_on ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                <Button
                                                    asChild
                                                    variant="ghost"
                                                    size="icon"
                                                >
                                                    <a
                                                        href={`/legal/${page.slug}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        title="View public page"
                                                    >
                                                        <ExternalLink className="h-4 w-4" />
                                                    </a>
                                                </Button>
                                                {can(
                                                    PERMISSIONS.LEGAL_PAGES
                                                        .EDIT,
                                                ) && (
                                                    <Button
                                                        asChild
                                                        variant="ghost"
                                                        size="icon"
                                                    >
                                                        <Link
                                                            href={
                                                                legalPages.edit(
                                                                    page.id,
                                                                ).url
                                                            }
                                                            title="Edit"
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </>
    );
}

LegalPagesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Legal pages', href: legalPages.index() },
    ],
};
