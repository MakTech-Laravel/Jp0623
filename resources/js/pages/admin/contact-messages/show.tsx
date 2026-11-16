import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Mail, Trash2 } from 'lucide-react';
import { motion } from 'motion/react';
import type { ReactNode } from 'react';
import { AdminPageHeader } from '@/components/admin/admin-page-header';
import { ConfirmDeleteDialog } from '@/components/admin/confirm-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePermission } from '@/hooks/use-permissions';
import { dashboard } from '@/routes';
import contactMessages from '@/routes/admin/contact-messages';
import type { AdminContactMessage } from '@/types/admin';
import { PERMISSIONS } from '@/types/permissions';

export default function ShowContactMessage({
    message,
}: {
    message: AdminContactMessage;
}) {
    const { can } = usePermission();

    return (
        <>
            <Head title={`Message from ${message.name}`} />

            <div className="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <AdminPageHeader
                    title="Message details"
                    description={message.name}
                    icon={Mail}
                >
                    <Button variant="outline" asChild>
                        <Link href={contactMessages.index().url}>
                            <ArrowLeft className="h-4 w-4" /> Back
                        </Link>
                    </Button>
                    {can(PERMISSIONS.CONTACT_MESSAGES.EDIT) && (
                        <Button
                            type="button"
                            variant={
                                message.status === 'reviewed'
                                    ? 'outline'
                                    : 'default'
                            }
                            onClick={() =>
                                router.put(
                                    contactMessages.update(message.id).url,
                                    {
                                        status:
                                            message.status === 'new'
                                                ? 'reviewed'
                                                : 'new',
                                    },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            {message.status === 'new'
                                ? 'Mark reviewed'
                                : 'Mark new'}
                        </Button>
                    )}
                    {can(PERMISSIONS.CONTACT_MESSAGES.DELETE) && (
                        <ConfirmDeleteDialog
                            description={
                                <>
                                    Delete message from{' '}
                                    <strong>{message.name}</strong>?
                                </>
                            }
                            onConfirm={() =>
                                router.delete(
                                    contactMessages.destroy(message.id).url,
                                )
                            }
                        >
                            <Button
                                variant="outline"
                                className="text-destructive"
                            >
                                <Trash2 className="h-4 w-4" /> Delete
                            </Button>
                        </ConfirmDeleteDialog>
                    )}
                </AdminPageHeader>

                <motion.div
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="space-y-6"
                >
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge
                            variant={
                                message.status === 'new'
                                    ? 'secondary'
                                    : 'outline'
                            }
                        >
                            {message.status === 'new' ? 'New' : 'Reviewed'}
                        </Badge>
                        <span className="text-sm text-muted-foreground">
                            Submitted{' '}
                            {new Date(message.created_at).toLocaleString()}
                        </span>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-2">
                        <Section title="Contact">
                            <Detail label="Name" value={message.name} />
                            <Detail
                                label="Company"
                                value={message.company ?? '—'}
                            />
                            <Detail label="Email" value={message.email} />
                            <Detail
                                label="Phone"
                                value={message.phone ?? '—'}
                            />
                        </Section>

                        <Section title="Request">
                            <Detail label="Topic" value={message.topic} />
                            <Detail
                                label="Terms accepted"
                                value={message.terms_accepted ? 'Yes' : 'No'}
                            />
                        </Section>
                    </div>

                    <section className="rounded-xl border bg-card p-5 shadow-sm">
                        <h2 className="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                            Message
                        </h2>
                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-foreground">
                            {message.message}
                        </p>
                    </section>
                </motion.div>
            </div>
        </>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <section className="rounded-xl border bg-card p-5 shadow-sm">
            <h2 className="mb-4 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                {title}
            </h2>
            <dl className="space-y-3">{children}</dl>
        </section>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div className="grid gap-1 sm:grid-cols-[8rem_1fr] sm:gap-3">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="text-sm font-medium break-words">{value}</dd>
        </div>
    );
}

ShowContactMessage.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Contact messages', href: contactMessages.index() },
        { title: 'Details' },
    ],
};
