import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { OperatorLead } from '@/types/operator-dashboard';

interface LeadBuyDialogProps {
    lead: OperatorLead | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

function money(value: number): string {
    return `$${value.toLocaleString('en-US')}`;
}

export function LeadBuyDialog({
    lead,
    open,
    onOpenChange,
}: LeadBuyDialogProps) {
    if (!lead) {
        return null;
    }

    const handleBuy = () => {
        toast.success(`Lead ${lead.id} purchased (demo). Contact details unlocked.`);
        onOpenChange(false);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{lead.type}</DialogTitle>
                    <DialogDescription>
                        {lead.id} · {lead.cat} · {money(lead.price)}
                    </DialogDescription>
                </DialogHeader>

                <dl className="grid gap-3 text-sm sm:grid-cols-2">
                    <Detail
                        label="Location"
                        value={`${lead.city}, ${lead.st} ${lead.zip.slice(0, 3)}xx`}
                    />
                    <Detail label="Distance" value={`${lead.dist} mi`} />
                    <Detail label="People" value={String(lead.people)} />
                    <Detail label="Wants" value={lead.wants} />
                    <Detail label="Placement" value={lead.io} />
                    <Detail label="Hours" value={lead.hours} />
                    <Detail
                        label="Contact"
                        value={lead.contact}
                        className="sm:col-span-2"
                    />
                </dl>

                <DialogFooter className="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        Cancel
                    </Button>
                    <Button
                        className="bg-brass text-brass-ink hover:bg-brass/90"
                        onClick={handleBuy}
                    >
                        Buy lead · {money(lead.price)}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function Detail({
    label,
    value,
    className,
}: {
    label: string;
    value: string;
    className?: string;
}) {
    return (
        <div className={className}>
            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </dt>
            <dd className="mt-1">{value}</dd>
        </div>
    );
}
