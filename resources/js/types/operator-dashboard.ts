export interface OperatorLead {
    id: string;
    source: 'vending' | 'coffee';
    type: string;
    cat: string;
    city: string;
    st: string;
    zip: string;
    dist: number | null;
    people: number | null;
    wants: string;
    io: string | null;
    price: number;
    listed: string;
}

export type PurchasedLeadStatus = 'ok' | 'issue_open';

export interface PurchasedLead {
    id: string;
    purchased_at: string;
    name: string;
    type: string;
    city: string;
    st: string;
    zip: string;
    address: string;
    contact_name: string;
    contact_phone: string;
    email: string;
    price: number;
    credit_used: number | null;
    status: PurchasedLeadStatus;
}

export interface OperatorKpi {
    label: string;
    value: string;
    hint: string | null;
}

export interface OperatorActivityItem {
    text: string;
    when: string;
}

export interface OperatorDashboardProps {
    firstName: string;
    primaryCity: string;
    subscription: {
        status: string;
        label: string;
    };
    kpis: OperatorKpi[];
    leads: OperatorLead[];
    newestLeads: OperatorLead[];
    activity: OperatorActivityItem[];
}
