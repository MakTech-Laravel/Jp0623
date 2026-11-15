export const OPERATOR_MACHINES_NEEDED = [
    '1',
    '2',
    '3',
    '4',
    '5',
    '6+',
] as const;

export type OperatorMachinesNeeded =
    (typeof OPERATOR_MACHINES_NEEDED)[number];
