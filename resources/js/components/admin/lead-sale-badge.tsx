import { Badge } from '@/components/ui/badge';

export type LeadSaleFlags = {
    is_sold: boolean;
    is_held: boolean;
};

export type LeadBuyer = {
    name: string;
    email: string;
    purchase_status: string;
} | null;

export function leadSaleLabel(flags: LeadSaleFlags): string {
    if (flags.is_sold) {
        return 'Sold';
    }

    if (flags.is_held) {
        return 'Processing';
    }

    return 'Available';
}

export function LeadSaleBadge({ is_sold, is_held }: LeadSaleFlags) {
    const label = leadSaleLabel({ is_sold, is_held });

    if (label === 'Sold') {
        return (
            <Badge
                variant="outline"
                className="border-emerald-500/40 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200"
            >
                Sold
            </Badge>
        );
    }

    if (label === 'Processing') {
        return (
            <Badge
                variant="outline"
                className="border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200"
            >
                Processing
            </Badge>
        );
    }

    return (
        <Badge variant="outline" className="text-muted-foreground">
            Available
        </Badge>
    );
}

export function LeadBuyerNote({
    flags,
    buyer,
}: {
    flags: LeadSaleFlags;
    buyer: LeadBuyer;
}) {
    if (!buyer) {
        if (flags.is_sold || flags.is_held) {
            return (
                <p className="text-sm text-muted-foreground">
                    {flags.is_sold
                        ? 'This lead has been sold to an operator.'
                        : 'A payment is processing for this lead.'}
                </p>
            );
        }

        return (
            <p className="text-sm text-muted-foreground">
                This lead is still available for operators to buy.
            </p>
        );
    }

    if (flags.is_sold || buyer.purchase_status === 'paid') {
        return (
            <p className="text-sm text-muted-foreground">
                Sold to <span className="font-medium text-foreground">{buyer.name}</span>{' '}
                ({buyer.email}).
            </p>
        );
    }

    return (
        <p className="text-sm text-muted-foreground">
            Payment processing for{' '}
            <span className="font-medium text-foreground">{buyer.name}</span> (
            {buyer.email}).
        </p>
    );
}
