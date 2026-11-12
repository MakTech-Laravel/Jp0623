import type { OperatorLead } from '@/types/operator-dashboard';

/**
 * Distance label in miles, or an em dash when it cannot be calculated.
 */
export function formatDistance(dist: number | null): string {
    if (dist === null) {
        return '—';
    }

    return dist < 1 ? '<1 mi' : `${dist} mi`;
}

/**
 * Public area label for a lead, e.g. "Houston, TX 770xx". Skips empty parts.
 */
export function leadArea(
    lead: Pick<OperatorLead, 'city' | 'st' | 'zip'>,
): string {
    const place = [lead.city, lead.st].filter(Boolean).join(', ');
    const zip = lead.zip ? `${lead.zip.slice(0, 3)}xx` : '';

    return [place, zip].filter(Boolean).join(' ') || '—';
}
