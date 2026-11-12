import { Link } from '@inertiajs/react';
import { FileText, Send } from 'lucide-react';
import { cn } from '@/lib/utils';
import operatorSms from '@/routes/admin/operator-sms';
import templates from '@/routes/admin/operator-sms/templates';

export function OperatorSmsTabs({ active }: { active: 'send' | 'templates' }) {
    const items = [
        {
            key: 'send' as const,
            label: 'Send SMS',
            href: operatorSms.index().url,
            icon: Send,
        },
        {
            key: 'templates' as const,
            label: 'SMS templates',
            href: templates.index().url,
            icon: FileText,
        },
    ];

    return (
        <div className="flex flex-wrap gap-2 border-b pb-3">
            {items.map((item) => {
                const Icon = item.icon;
                const isActive = item.key === active;

                return (
                    <Link
                        key={item.key}
                        href={item.href}
                        className={cn(
                            'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                            isActive
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted/60 text-muted-foreground hover:bg-muted hover:text-foreground',
                        )}
                    >
                        <Icon className="h-4 w-4" />
                        {item.label}
                    </Link>
                );
            })}
        </div>
    );
}
