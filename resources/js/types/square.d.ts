/**
 * Minimal typings for Square's Web Payments SDK (loaded from `square.sdkUrl`).
 * Only what the lead checkout uses; see https://developer.squareup.com/docs/web-payments
 */

/** Public Square settings shared by the server (never contains secrets). */
export type SquareConfig = {
    applicationId: string;
    locationId: string;
    sdkUrl: string;
    currency: string;
};

export type SquareTokenizeError = {
    type?: string;
    message: string;
    field?: string;
};

export type SquareTokenResult = {
    status: string;
    token?: string;
    errors?: SquareTokenizeError[];
};

export type SquareVerificationDetails = {
    amount: string;
    currencyCode: string;
    intent: 'CHARGE' | 'STORE' | 'CHARGE_AND_STORE';
    customerInitiated: boolean;
    sellerKeyedIn: boolean;
    billingContact?: {
        email?: string;
        givenName?: string;
        familyName?: string;
    };
};

export interface SquareCard {
    attach(container: string | HTMLElement): Promise<void>;
    tokenize(
        verificationDetails?: SquareVerificationDetails,
    ): Promise<SquareTokenResult>;
    destroy(): Promise<boolean>;
}

export interface SquarePayments {
    card(options?: Record<string, unknown>): Promise<SquareCard>;
}

export interface SquareSdk {
    payments(applicationId: string, locationId: string): SquarePayments;
}

declare global {
    interface Window {
        Square?: SquareSdk;
    }
}
