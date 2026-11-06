export const OPERATOR_MACHINE_TYPES = [
    'Soda',
    'Snack',
    'Combo',
    'Healthy',
    'Micro market',
    'Coffee',
] as const;

export const OPERATOR_MACHINES_NEEDED = [
    '1',
    '2',
    '3',
    '4',
    '5',
    '6+',
] as const;

export type OperatorMachineType = (typeof OPERATOR_MACHINE_TYPES)[number];
export type OperatorMachinesNeeded =
    (typeof OPERATOR_MACHINES_NEEDED)[number];
