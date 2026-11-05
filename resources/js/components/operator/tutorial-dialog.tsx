import { Play } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface TutorialDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function TutorialDialog({ open, onOpenChange }: TutorialDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>How leads work</DialogTitle>
                    <DialogDescription>
                        A short walkthrough of buying and working location leads
                        (3:12).
                    </DialogDescription>
                </DialogHeader>

                <div className="flex aspect-video items-center justify-center rounded-xl bg-gradient-to-br from-petrol to-petrol-2 text-on-petrol">
                    <span className="inline-flex items-center gap-2 rounded-full bg-brass px-4 py-2 text-sm font-semibold text-brass-ink">
                        <Play className="h-4 w-4" /> Watch tutorial
                    </span>
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
