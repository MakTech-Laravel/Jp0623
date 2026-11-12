import { useCallback, useEffect, useRef, useState } from 'react';
import type {
    SquareCard,
    SquareConfig,
    SquareVerificationDetails,
} from '@/types/square';

const scriptPromises = new Map<string, Promise<void>>();

/**
 * Load Square's Web Payments SDK once per URL.
 */
function loadSquareScript(src: string): Promise<void> {
    if (window.Square) {
        return Promise.resolve();
    }

    const existing = scriptPromises.get(src);

    if (existing) {
        return existing;
    }

    const promise = new Promise<void>((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => {
            scriptPromises.delete(src);
            script.remove();
            reject(new Error('Could not load the Square payment form.'));
        };
        document.head.appendChild(script);
    });

    scriptPromises.set(src, promise);

    return promise;
}

type UseSquareCard = {
    /** Pass as the `ref` callback of the element that should hold the card form. */
    setContainer: (element: HTMLDivElement | null) => void;
    ready: boolean;
    error: string | null;
    /** Exchange the entered card for a single-use token (never log it). */
    tokenize: (details: SquareVerificationDetails) => Promise<string>;
};

/**
 * Mount Square's hosted card form into a container and tokenize it on demand.
 * Card data stays inside Square's iframe; only the token reaches our server.
 */
export function useSquareCard(
    config: SquareConfig | null,
    enabled: boolean,
): UseSquareCard {
    const [container, setContainer] = useState<HTMLDivElement | null>(null);
    const cardRef = useRef<SquareCard | null>(null);
    const [ready, setReady] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const applicationId = config?.applicationId;
    const locationId = config?.locationId;
    const sdkUrl = config?.sdkUrl;

    useEffect(() => {
        if (
            !enabled ||
            !container ||
            !applicationId ||
            !locationId ||
            !sdkUrl
        ) {
            return;
        }

        let cancelled = false;

        void (async () => {
            try {
                await loadSquareScript(sdkUrl);

                if (cancelled || !window.Square) {
                    return;
                }

                const payments = window.Square.payments(
                    applicationId,
                    locationId,
                );
                const card = await payments.card();

                if (cancelled) {
                    await card.destroy();

                    return;
                }

                await card.attach(container);
                cardRef.current = card;
                setError(null);
                setReady(true);
            } catch (caught) {
                if (!cancelled) {
                    const detail =
                        caught instanceof Error && caught.message.trim() !== ''
                            ? caught.message
                            : null;

                    // Never include tokens; Square's client errors are safe to show.
                    setError(
                        detail ??
                            'Could not load the secure card form. Please refresh and try again.',
                    );
                }
            }
        })();

        return () => {
            cancelled = true;
            setReady(false);
            const card = cardRef.current;
            cardRef.current = null;

            if (card) {
                void card.destroy().catch(() => undefined);
            }
        };
    }, [enabled, container, applicationId, locationId, sdkUrl]);

    const tokenize = useCallback(
        async (details: SquareVerificationDetails): Promise<string> => {
            const card = cardRef.current;

            if (!card) {
                throw new Error('The card form is not ready yet.');
            }

            const result = await card.tokenize(details);

            if (result.status === 'OK' && result.token) {
                return result.token;
            }

            throw new Error(
                result.errors?.[0]?.message ??
                    'Check your card details and try again.',
            );
        },
        [],
    );

    return { setContainer, ready, error, tokenize };
}
