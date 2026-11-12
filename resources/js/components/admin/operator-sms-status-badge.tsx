import { Badge } from '@/components/ui/badge';
import type { OperatorSmsStatus } from '@/types/admin';

const LABELS: Record<OperatorSmsStatus, string> = {
    pending: 'Pending',
    done: 'Done',
    partial: 'Partial',
    failed: 'Failed',
    skipped: 'Skipped',
};

const DESCRIPTIONS: Record<OperatorSmsStatus, string> = {
    pending: 'SMS alert to nearby operators is queued or still sending.',
    done: 'Nearby operators within 100 miles were notified by SMS.',
    partial: 'Some nearby operators were notified; others failed.',
    failed: 'SMS alert to nearby operators could not be sent.',
    skipped:
        'No SMS sent (Twilio off, missing location, or no operators nearby).',
};

export function operatorSmsStatusLabel(status: OperatorSmsStatus): string {
    return LABELS[status] ?? status;
}

export function OperatorSmsStatusBadge({
    status,
    note,
    providerStatus,
    sentCount,
}: {
    status: OperatorSmsStatus;
    note?: string | null;
    providerStatus?: string | null;
    sentCount?: number | null;
}) {
    const titleParts = [
        DESCRIPTIONS[status] ?? status,
        note || null,
        providerStatus ? `Twilio: ${providerStatus}` : null,
        typeof sentCount === 'number' && sentCount > 0
            ? `Sent: ${sentCount}`
            : null,
    ].filter(Boolean);

    const variant =
        status === 'done'
            ? 'default'
            : status === 'failed'
              ? 'destructive'
              : status === 'pending'
                ? 'secondary'
                : 'outline';

    return (
        <Badge variant={variant} title={titleParts.join(' ')}>
            {operatorSmsStatusLabel(status)}
            {providerStatus ? (
                <span className="ml-1 opacity-80">· {providerStatus}</span>
            ) : null}
        </Badge>
    );
}

export const OPERATOR_SMS_COLUMN_LABEL = 'Nearby operator SMS';
export const OPERATOR_SMS_COLUMN_HINT =
    'Shows whether operators within 100 miles were texted about this lead.';
