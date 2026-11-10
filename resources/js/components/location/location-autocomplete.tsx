import { useEffect, useId, useRef, useState } from 'react';
import { LoaderCircle } from 'lucide-react';
import { cn } from '@/lib/utils';

export type LocationSuggestion = {
    label: string;
    address: string | null;
    city: string;
    state: string;
    postal_code: string | null;
    latitude: number;
    longitude: number;
};

type LocationAutocompleteProps = {
    id?: string;
    label?: string;
    value: string;
    selected: LocationSuggestion | null;
    onQueryChange: (value: string) => void;
    onSelect: (suggestion: LocationSuggestion) => void;
    onClearSelection: () => void;
    placeholder?: string;
    className?: string;
    inputClassName?: string;
    error?: string | null;
    suggestUrl?: string;
};

const DEFAULT_SUGGEST_URL = '/register/location-suggest';
const MIN_CHARS = 3;
const DEBOUNCE_MS = 450;

export function LocationAutocomplete({
    id,
    label,
    value,
    selected,
    onQueryChange,
    onSelect,
    onClearSelection,
    placeholder = 'Type city or address, e.g. Houston, TX',
    className,
    inputClassName,
    error,
    suggestUrl = DEFAULT_SUGGEST_URL,
}: LocationAutocompleteProps) {
    const generatedId = useId();
    const inputId = id ?? generatedId;
    const listId = `${inputId}-list`;
    const rootRef = useRef<HTMLDivElement>(null);
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [fetchError, setFetchError] = useState<string | null>(null);
    const [suggestions, setSuggestions] = useState<LocationSuggestion[]>([]);
    const [activeIndex, setActiveIndex] = useState(-1);
    const requestIdRef = useRef(0);

    useEffect(() => {
        function handlePointerDown(event: MouseEvent) {
            if (!rootRef.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        }

        document.addEventListener('mousedown', handlePointerDown);

        return () => document.removeEventListener('mousedown', handlePointerDown);
    }, []);

    useEffect(() => {
        const query = value.trim();

        if (selected && selected.label === value) {
            setSuggestions([]);
            setFetchError(null);
            setLoading(false);
            return;
        }

        if (query.length < MIN_CHARS) {
            setSuggestions([]);
            setFetchError(null);
            setLoading(false);
            return;
        }

        const timer = window.setTimeout(async () => {
            const requestId = ++requestIdRef.current;
            setLoading(true);
            setFetchError(null);

            try {
                const response = await fetch(
                    `${suggestUrl}?q=${encodeURIComponent(query)}`,
                    {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    },
                );

                const payload = (await response.json().catch(() => null)) as {
                    message?: string;
                    suggestions?: LocationSuggestion[];
                } | null;

                if (requestId !== requestIdRef.current) {
                    return;
                }

                if (!response.ok) {
                    setSuggestions([]);
                    setFetchError(
                        payload?.message ??
                            'Location suggestions are temporarily unavailable.',
                    );
                    setOpen(true);
                    return;
                }

                const next = payload?.suggestions ?? [];
                setSuggestions(next);
                setFetchError(null);
                setOpen(true);
                setActiveIndex(-1);
            } catch {
                if (requestId !== requestIdRef.current) {
                    return;
                }

                setSuggestions([]);
                setFetchError('Unable to load location suggestions.');
                setOpen(true);
            } finally {
                if (requestId === requestIdRef.current) {
                    setLoading(false);
                }
            }
        }, DEBOUNCE_MS);

        return () => window.clearTimeout(timer);
    }, [value, selected, suggestUrl]);

    function choose(suggestion: LocationSuggestion) {
        onSelect(suggestion);
        setOpen(false);
        setSuggestions([]);
        setFetchError(null);
        setActiveIndex(-1);
    }

    function handleChange(next: string) {
        onQueryChange(next);

        if (selected && next !== selected.label) {
            onClearSelection();
        }
    }

    const showEmpty =
        open &&
        !loading &&
        !fetchError &&
        value.trim().length >= MIN_CHARS &&
        suggestions.length === 0 &&
        !(selected && selected.label === value);

    return (
        <div ref={rootRef} className={cn('relative flex flex-col gap-1', className)}>
            {label ? (
                <label
                    htmlFor={inputId}
                    className="text-sm font-semibold text-brand-ink"
                >
                    {label}
                </label>
            ) : null}
            <div className="relative">
                <input
                    id={inputId}
                    role="combobox"
                    aria-expanded={open}
                    aria-controls={listId}
                    aria-autocomplete="list"
                    aria-activedescendant={
                        activeIndex >= 0
                            ? `${listId}-option-${activeIndex}`
                            : undefined
                    }
                    value={value}
                    onChange={(event) => handleChange(event.target.value)}
                    onFocus={() => {
                        if (
                            suggestions.length > 0 ||
                            fetchError ||
                            value.trim().length >= MIN_CHARS
                        ) {
                            setOpen(true);
                        }
                    }}
                    onKeyDown={(event) => {
                        if (!open && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
                            setOpen(true);
                            return;
                        }

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            setActiveIndex((current) =>
                                Math.min(current + 1, suggestions.length - 1),
                            );
                        } else if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            setActiveIndex((current) => Math.max(current - 1, 0));
                        } else if (event.key === 'Enter' && activeIndex >= 0) {
                            event.preventDefault();
                            const suggestion = suggestions[activeIndex];

                            if (suggestion) {
                                choose(suggestion);
                            }
                        } else if (event.key === 'Escape') {
                            setOpen(false);
                        }
                    }}
                    placeholder={placeholder}
                    autoComplete="off"
                    className={cn(
                        'w-full rounded-lg border border-brand-line bg-brand-surface px-3 py-2.5 pr-10 text-sm font-medium text-brand-ink shadow-none focus-visible:border-brass focus-visible:ring-brass/30',
                        inputClassName,
                    )}
                />
                {loading ? (
                    <LoaderCircle className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 animate-spin text-brand-muted" />
                ) : null}
            </div>

            {open && (loading || fetchError || showEmpty || suggestions.length > 0) ? (
                <ul
                    id={listId}
                    role="listbox"
                    className="absolute top-full z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-brand-line bg-brand-surface py-1 shadow-lg"
                >
                    {loading ? (
                        <li className="px-3 py-2 text-sm text-brand-muted">
                            Searching locations…
                        </li>
                    ) : null}
                    {!loading && fetchError ? (
                        <li className="px-3 py-2 text-sm text-red-600">{fetchError}</li>
                    ) : null}
                    {!loading && !fetchError && showEmpty ? (
                        <li className="px-3 py-2 text-sm text-brand-muted">
                            No US locations found. Try a different city or address.
                        </li>
                    ) : null}
                    {!loading &&
                        !fetchError &&
                        suggestions.map((suggestion, index) => (
                            <li key={`${suggestion.label}-${suggestion.latitude}-${suggestion.longitude}`}>
                                <button
                                    id={`${listId}-option-${index}`}
                                    type="button"
                                    role="option"
                                    aria-selected={activeIndex === index}
                                    className={cn(
                                        'block w-full px-3 py-2 text-left text-sm text-brand-ink hover:bg-brand-line/40',
                                        activeIndex === index && 'bg-brand-line/40',
                                    )}
                                    onMouseDown={(event) => event.preventDefault()}
                                    onClick={() => choose(suggestion)}
                                >
                                    {suggestion.label}
                                </button>
                            </li>
                        ))}
                </ul>
            ) : null}

            {selected ? (
                <p className="text-xs font-medium text-brand-muted">
                    Selected: {selected.city}, {selected.state}
                    {selected.postal_code ? ` ${selected.postal_code}` : ''}
                </p>
            ) : value.trim().length >= MIN_CHARS ? (
                <p className="text-xs font-medium text-amber-700">
                    Select a location from the suggestions to continue.
                </p>
            ) : (
                <p className="text-xs font-medium text-brand-muted">
                    Type at least {MIN_CHARS} characters, then choose a suggestion.
                </p>
            )}

            {error ? (
                <p className="text-sm text-red-600" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}
