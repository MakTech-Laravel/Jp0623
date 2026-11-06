import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { slugify } from '@/lib/slugify';

type NameSlugFieldsProps = {
    name: string;
    slug: string;
    nameError?: string;
    slugError?: string;
    nameInvalid?: boolean;
    slugInvalid?: boolean;
    namePlaceholder?: string;
    autoSyncInitially?: boolean;
    onNameChange: (value: string) => void;
    onSlugChange: (value: string) => void;
    onNameBlur?: () => void;
    onSlugBlur?: () => void;
};

export function NameSlugFields({
    name,
    slug,
    nameError,
    slugError,
    nameInvalid,
    slugInvalid,
    namePlaceholder,
    autoSyncInitially = true,
    onNameChange,
    onSlugChange,
    onNameBlur,
    onSlugBlur,
}: NameSlugFieldsProps) {
    const [autoSync, setAutoSync] = useState(autoSyncInitially);

    const handleNameChange = (value: string) => {
        onNameChange(value);

        if (autoSync) {
            onSlugChange(slugify(value));
        }
    };

    const handleSlugChange = (value: string) => {
        setAutoSync(false);
        onSlugChange(value);
    };

    const handleGenerateSlug = () => {
        onSlugChange(slugify(name));
        setAutoSync(true);
    };

    return (
        <div className="grid gap-5 sm:grid-cols-2">
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    value={name}
                    onChange={(e) => handleNameChange(e.target.value)}
                    onBlur={onNameBlur}
                    aria-invalid={nameInvalid}
                    placeholder={namePlaceholder}
                />
                <InputError message={nameError} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="slug">
                    Slug
                    <span className="ml-1 text-xs font-normal text-muted-foreground">
                        optional
                    </span>
                </Label>
                <div className="flex gap-2">
                    <Input
                        id="slug"
                        value={slug}
                        onChange={(e) => handleSlugChange(e.target.value)}
                        onBlur={onSlugBlur}
                        aria-invalid={slugInvalid}
                        placeholder="auto-generated from name"
                        className="min-w-0 flex-1"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        onClick={handleGenerateSlug}
                        className="shrink-0"
                    >
                        Generate slug
                    </Button>
                </div>
                <InputError message={slugError} />
            </div>
        </div>
    );
}
