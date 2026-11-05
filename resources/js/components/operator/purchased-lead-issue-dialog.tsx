import { useEffect, useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { PurchasedLead } from '@/types/operator-dashboard';

const ISSUE_CATEGORIES = [
    'I ordered this lead by mistake',
    'I could not reach the location',
    'I reached them but could not propose',
    'I discussed or proposed my service',
] as const;

const SPECIFIC_ISSUES = [
    'Phone number is wrong',
    'Contact no longer works there',
    'Location already has vending',
    'Location says they never asked',
    'Other',
] as const;

interface PurchasedLeadIssueDialogProps {
    lead: PurchasedLead | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSubmitted: (leadId: string) => void;
}

export function PurchasedLeadIssueDialog({
    lead,
    open,
    onOpenChange,
    onSubmitted,
}: PurchasedLeadIssueDialogProps) {
    const [category, setCategory] = useState<string>(ISSUE_CATEGORIES[0]);
    const [specificIssue, setSpecificIssue] = useState<string>(
        SPECIFIC_ISSUES[0],
    );
    const [comments, setComments] = useState('');
    const [verified, setVerified] = useState(false);

    useEffect(() => {
        if (open) {
            setCategory(ISSUE_CATEGORIES[0]);
            setSpecificIssue(SPECIFIC_ISSUES[0]);
            setComments('');
            setVerified(false);
        }
    }, [open, lead?.id]);

    if (!lead) {
        return null;
    }

    const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!comments.trim()) {
            toast.error('Please add comments describing the issue.');
            return;
        }

        if (!verified) {
            toast.error('Please confirm you understand we will verify this report.');
            return;
        }

        const ticketId = 'T-214';
        toast.success(
            `Issue submitted as ticket ${ticketId}. We'll verify it with the location and email you.`,
        );
        onSubmitted(lead.id);
        onOpenChange(false);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <form onSubmit={handleSubmit} className="space-y-5">
                    <DialogHeader>
                        <DialogTitle>
                            Report an issue with {lead.id}
                        </DialogTitle>
                        <DialogDescription>
                            {lead.name} · {lead.type}. Misleading reports may
                            lead to account suspension.
                        </DialogDescription>
                    </DialogHeader>

                    <fieldset className="space-y-3">
                        <legend className="text-sm font-medium">
                            Choose the category that fits best
                        </legend>
                        <div className="space-y-2">
                            {ISSUE_CATEGORIES.map((option) => (
                                <label
                                    key={option}
                                    className="flex cursor-pointer items-start gap-3 rounded-md border px-3 py-2 text-sm has-checked:border-brass has-checked:bg-brass/5"
                                >
                                    <input
                                        type="radio"
                                        name="issue-category"
                                        value={option}
                                        checked={category === option}
                                        onChange={() => setCategory(option)}
                                        className="mt-0.5 size-4 accent-brass"
                                    />
                                    <span>{option}</span>
                                </label>
                            ))}
                        </div>
                    </fieldset>

                    <div className="space-y-2">
                        <Label htmlFor="specific-issue">Specific issue</Label>
                        <Select
                            value={specificIssue}
                            onValueChange={setSpecificIssue}
                        >
                            <SelectTrigger
                                id="specific-issue"
                                className="w-full"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {SPECIFIC_ISSUES.map((option) => (
                                    <SelectItem key={option} value={option}>
                                        {option}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="issue-comments">Comments</Label>
                        <Textarea
                            id="issue-comments"
                            rows={3}
                            required
                            value={comments}
                            onChange={(event) =>
                                setComments(event.target.value)
                            }
                            placeholder="Describe what went wrong…"
                        />
                    </div>

                    <label className="flex items-start gap-3 text-sm">
                        <Checkbox
                            checked={verified}
                            onCheckedChange={(value) =>
                                setVerified(value === true)
                            }
                            className="mt-0.5"
                        />
                        <span>
                            I understand you&apos;ll verify this report with the
                            location.
                        </span>
                    </label>

                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            className="bg-brass text-brass-ink hover:bg-brass/90"
                        >
                            Submit issue
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
