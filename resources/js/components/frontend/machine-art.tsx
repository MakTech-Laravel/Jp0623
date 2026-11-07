const COLS = [
    'fill-c1',
    'fill-c2',
    'fill-c3',
    'fill-brass',
    'fill-c4',
] as const;

function Rows({
    x,
    y,
    w,
    h,
    r,
    c,
    healthy = false,
}: {
    x: number;
    y: number;
    w: number;
    h: number;
    r: number;
    c: number;
    healthy?: boolean;
}) {
    const rh = h / r;
    const cw = w / c;
    const nodes = [];

    for (let i = 0; i < r; i++) {
        nodes.push(
            <rect
                key={`shelf-${i}`}
                x={x}
                y={y + rh * (i + 1) - 3}
                width={w}
                height={3}
                className="fill-brand-sunk"
            />,
        );

        for (let j = 0; j < c; j++) {
            const ih = rh * 0.62;
            let fill = COLS[(i + j) % 5];

            if (healthy && (fill === 'fill-c1' || fill === 'fill-c4')) {
                fill = 'fill-c3';
            }

            nodes.push(
                <rect
                    key={`item-${i}-${j}`}
                    x={x + cw * j + cw * 0.18}
                    y={y + rh * (i + 1) - 3 - ih}
                    width={cw * 0.64}
                    height={ih}
                    rx={2}
                    className={fill}
                />,
            );
        }
    }

    return <>{nodes}</>;
}

function Bottles({
    x,
    y,
    w,
    h,
    r,
    c,
}: {
    x: number;
    y: number;
    w: number;
    h: number;
    r: number;
    c: number;
}) {
    const rh = h / r;
    const cw = w / c;
    const nodes = [];

    for (let i = 0; i < r; i++) {
        nodes.push(
            <rect
                key={`bshelf-${i}`}
                x={x}
                y={y + rh * (i + 1) - 3}
                width={w}
                height={3}
                className="fill-brand-sunk"
            />,
        );

        for (let j = 0; j < c; j++) {
            const bh = rh * 0.7;
            const bx = x + cw * j + cw * 0.25;
            const by = y + rh * (i + 1) - 3 - bh;
            const fill = COLS[(i * 2 + j) % 5];

            nodes.push(
                <rect
                    key={`body-${i}-${j}`}
                    x={bx}
                    y={by + bh * 0.25}
                    width={cw * 0.5}
                    height={bh * 0.75}
                    rx={3}
                    className={fill}
                />,
                <rect
                    key={`neck-${i}-${j}`}
                    x={bx + cw * 0.17}
                    y={by}
                    width={cw * 0.16}
                    height={bh * 0.3}
                    className={fill}
                />,
            );
        }
    }

    return <>{nodes}</>;
}

function Keypad({ x, y }: { x: number; y: number }) {
    const circles = [];

    for (let i = 0; i < 4; i++) {
        for (let j = 0; j < 3; j++) {
            circles.push(
                <circle
                    key={`${i}-${j}`}
                    cx={x + 6 + j * 11}
                    cy={y + 30 + i * 11}
                    r={3.6}
                    className="fill-brand-surface"
                />,
            );
        }
    }

    return (
        <>
            <rect
                x={x}
                y={y}
                width={34}
                height={18}
                rx={3}
                className="fill-brass"
            />
            {circles}
            <rect
                x={x + 4}
                y={y + 78}
                width={26}
                height={12}
                rx={2}
                className="fill-brand-ink"
            />
            <rect
                x={x + 8}
                y={y + 98}
                width={18}
                height={4}
                rx={2}
                className="fill-brand-ink"
            />
        </>
    );
}

function Base({ w }: { w: number }) {
    return (
        <>
            <rect
                x={6}
                y={6}
                width={w}
                height={226}
                rx={10}
                className="fill-petrol"
            />
            <rect
                x={14}
                y={230}
                width={18}
                height={8}
                className="fill-brand-ink"
            />
            <rect
                x={w - 24}
                y={230}
                width={18}
                height={8}
                className="fill-brand-ink"
            />
        </>
    );
}

type MachineArtProps = {
    kind: string;
    className?: string;
};

export function MachineArt({ kind, className }: MachineArtProps) {
    const common = {
        className: className ?? 'art block h-auto w-full max-w-full',
        role: 'img' as const,
    };

    switch (kind) {
        case 'cooler':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Vendera smart cooler"
                >
                    <Base w={168} />
                    <rect
                        x={56}
                        y={14}
                        width={68}
                        height={20}
                        rx={4}
                        className="fill-brass"
                    />
                    <rect
                        x={18}
                        y={42}
                        width={144}
                        height={180}
                        rx={6}
                        className="fill-brand-surface"
                    />
                    <Bottles x={24} y={46} w={132} h={172} r={4} c={6} />
                    <rect
                        x={150}
                        y={100}
                        width={6}
                        height={50}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'market':
            return (
                <svg
                    {...common}
                    viewBox="0 0 300 220"
                    aria-label="Micro market with shelves, cooler and self-checkout kiosk"
                >
                    <rect
                        x={0}
                        y={206}
                        width={300}
                        height={10}
                        rx={3}
                        className="fill-brand-sunk"
                    />
                    <rect
                        x={10}
                        y={40}
                        width={110}
                        height={168}
                        rx={6}
                        className="fill-petrol"
                    />
                    <Rows x={16} y={44} w={98} h={160} r={5} c={4} />
                    <rect
                        x={130}
                        y={20}
                        width={90}
                        height={188}
                        rx={6}
                        className="fill-petrol"
                    />
                    <rect
                        x={136}
                        y={26}
                        width={78}
                        height={176}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Bottles x={138} y={28} w={74} h={172} r={4} c={4} />
                    <rect
                        x={236}
                        y={100}
                        width={54}
                        height={108}
                        rx={6}
                        className="fill-petrol-2"
                    />
                    <rect
                        x={242}
                        y={60}
                        width={42}
                        height={46}
                        rx={4}
                        className="fill-brass"
                    />
                    <rect
                        x={247}
                        y={65}
                        width={32}
                        height={34}
                        rx={2}
                        className="fill-petrol"
                    />
                    <rect
                        x={250}
                        y={122}
                        width={26}
                        height={8}
                        rx={2}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'coffee':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Office coffee machine"
                >
                    <rect
                        x={20}
                        y={20}
                        width={140}
                        height={200}
                        rx={14}
                        className="fill-petrol"
                    />
                    <rect
                        x={34}
                        y={34}
                        width={112}
                        height={44}
                        rx={6}
                        className="fill-brass"
                    />
                    <rect
                        x={42}
                        y={42}
                        width={96}
                        height={28}
                        rx={3}
                        className="fill-petrol-2"
                    />
                    {[0, 1, 2, 3].map((i) => (
                        <circle
                            key={i}
                            cx={50 + i * 26}
                            cy={96}
                            r={7}
                            className={COLS[i]}
                        />
                    ))}
                    <rect
                        x={56}
                        y={114}
                        width={68}
                        height={70}
                        rx={6}
                        className="fill-brand-ink"
                    />
                    <rect
                        x={82}
                        y={118}
                        width={16}
                        height={16}
                        className="fill-brand-surface"
                    />
                    <path
                        d="M72 150h36l-4 28H76z"
                        className="fill-brand-surface"
                    />
                    <rect
                        x={40}
                        y={196}
                        width={100}
                        height={10}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'healthy':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Healthy vending machine"
                >
                    <Base w={168} />
                    <rect
                        x={16}
                        y={18}
                        width={106}
                        height={170}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Rows
                        x={20}
                        y={20}
                        w={98}
                        h={166}
                        r={5}
                        c={4}
                        healthy
                    />
                    <Keypad x={130} y={22} />
                    <circle cx={147} cy={170} r={10} className="fill-c3" />
                    <rect
                        x={16}
                        y={196}
                        width={106}
                        height={24}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'snack':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Snack vending machine"
                >
                    <Base w={168} />
                    <rect
                        x={16}
                        y={18}
                        width={110}
                        height={170}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Rows x={20} y={20} w={102} h={166} r={6} c={5} />
                    <Keypad x={132} y={22} />
                    <rect
                        x={16}
                        y={196}
                        width={110}
                        height={24}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'drink':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Cold drink vending machine"
                >
                    <Base w={168} />
                    <rect
                        x={16}
                        y={18}
                        width={110}
                        height={196}
                        rx={6}
                        className="fill-c2"
                    />
                    <rect
                        x={52}
                        y={54}
                        width={38}
                        height={120}
                        rx={14}
                        className="fill-brand-surface"
                    />
                    <rect
                        x={62}
                        y={34}
                        width={18}
                        height={26}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <rect
                        x={52}
                        y={96}
                        width={38}
                        height={30}
                        className="fill-c1"
                    />
                    {[0, 1, 2, 3, 4, 5].map((i) => (
                        <rect
                            key={i}
                            x={136}
                            y={26 + i * 22}
                            width={26}
                            height={14}
                            rx={3}
                            className={COLS[i % 5]}
                        />
                    ))}
                    <rect
                        x={136}
                        y={166}
                        width={26}
                        height={12}
                        rx={2}
                        className="fill-brand-ink"
                    />
                    <rect
                        x={40}
                        y={190}
                        width={62}
                        height={16}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'touch':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Touchscreen combo vending machine"
                >
                    <Base w={168} />
                    <rect
                        x={16}
                        y={18}
                        width={106}
                        height={200}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Rows x={20} y={20} w={98} h={140} r={4} c={4} />
                    <rect
                        x={130}
                        y={22}
                        width={34}
                        height={56}
                        rx={4}
                        className="fill-brass"
                    />
                    <rect
                        x={134}
                        y={28}
                        width={26}
                        height={44}
                        rx={2}
                        className="fill-petrol-2"
                    />
                    <rect
                        x={134}
                        y={90}
                        width={26}
                        height={12}
                        rx={2}
                        className="fill-brand-ink"
                    />
                    <rect
                        x={20}
                        y={170}
                        width={98}
                        height={40}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'food':
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Refrigerated fresh food vending machine"
                >
                    <Base w={168} />
                    <rect
                        x={16}
                        y={18}
                        width={110}
                        height={170}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Rows
                        x={20}
                        y={20}
                        w={102}
                        h={166}
                        r={4}
                        c={3}
                        healthy
                    />
                    <Keypad x={132} y={22} />
                    <rect
                        x={16}
                        y={196}
                        width={110}
                        height={24}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'slim':
            return (
                <svg
                    {...common}
                    viewBox="0 0 140 240"
                    aria-label="Space-saver vending machine"
                >
                    <rect
                        x={6}
                        y={6}
                        width={128}
                        height={226}
                        rx={10}
                        className="fill-petrol"
                    />
                    <rect
                        x={14}
                        y={230}
                        width={16}
                        height={8}
                        className="fill-brand-ink"
                    />
                    <rect
                        x={110}
                        y={230}
                        width={16}
                        height={8}
                        className="fill-brand-ink"
                    />
                    <rect
                        x={16}
                        y={18}
                        width={72}
                        height={170}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Rows x={20} y={20} w={64} h={166} r={5} c={3} />
                    <Keypad x={96} y={22} />
                    <rect
                        x={16}
                        y={196}
                        width={72}
                        height={24}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'combo':
        default:
            return (
                <svg
                    {...common}
                    viewBox="0 0 180 240"
                    aria-label="Snack and drink combo vending machine"
                >
                    <Base w={168} />
                    <rect
                        x={16}
                        y={18}
                        width={106}
                        height={170}
                        rx={4}
                        className="fill-brand-surface"
                    />
                    <Rows x={20} y={20} w={98} h={166} r={5} c={4} />
                    <Keypad x={130} y={22} />
                    <rect
                        x={16}
                        y={196}
                        width={106}
                        height={24}
                        rx={3}
                        className="fill-brand-ink"
                    />
                </svg>
            );
    }
}

export function PackArt({ kind }: { kind: string }) {
    switch (kind) {
        case 'chips':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <path
                        d="M14 10h32l-3 6 3 6-3 6 3 6-3 6 3 6-3 6H14l3-6-3-6 3-6-3-6 3-6-3-6z"
                        className="fill-c1"
                    />
                    <circle cx={30} cy={32} r={7} className="fill-brass" />
                </svg>
            );
        case 'sweets':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <rect
                        x={10}
                        y={22}
                        width={40}
                        height={16}
                        rx={3}
                        className="fill-c4"
                    />
                    <path d="M10 30l-6-6v12zM50 30l6-6v12z" className="fill-c4" />
                    <rect
                        x={18}
                        y={26}
                        width={24}
                        height={8}
                        rx={2}
                        className="fill-brand-surface"
                    />
                </svg>
            );
        case 'nuts':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <circle cx={22} cy={34} r={10} className="fill-c3" />
                    <circle cx={38} cy={32} r={9} className="fill-c1" />
                    <rect
                        x={34}
                        y={18}
                        width={3}
                        height={8}
                        className="fill-brand-ink"
                    />
                    <circle cx={30} cy={46} r={7} className="fill-brass" />
                </svg>
            );
        case 'pastry':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <path d="M8 40q22-30 44 0z" className="fill-brass" />
                    <path
                        d="M16 36q14-16 28 0"
                        className="fill-none stroke-c1"
                        strokeWidth={3}
                    />
                </svg>
            );
        case 'bars':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <rect
                        x={8}
                        y={24}
                        width={44}
                        height={14}
                        rx={3}
                        className="fill-c3"
                    />
                    <rect
                        x={14}
                        y={27}
                        width={20}
                        height={8}
                        rx={2}
                        className="fill-brand-surface"
                    />
                </svg>
            );
        case 'healthy':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <circle cx={30} cy={34} r={16} className="fill-c3" />
                    <path
                        d="M30 18q6-10 14-8-2 10-14 8z"
                        className="fill-c3"
                    />
                    <rect
                        x={29}
                        y={12}
                        width={2}
                        height={8}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'energy':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <rect
                        x={20}
                        y={12}
                        width={20}
                        height={40}
                        rx={4}
                        className="fill-brand-ink"
                    />
                    <path
                        d="M32 18l-8 14h6l-2 12 8-16h-6z"
                        className="fill-brass"
                    />
                </svg>
            );
        case 'food':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <path d="M8 40l22-24 22 24z" className="fill-brass" />
                    <path d="M12 40l18-18 18 18z" className="fill-c3" />
                    <rect
                        x={8}
                        y={40}
                        width={44}
                        height={6}
                        rx={2}
                        className="fill-c1"
                    />
                </svg>
            );
        case 'coffee':
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <path d="M14 22h30l-4 26H18z" className="fill-brand-surface" />
                    <path d="M14 22h30l-1 6H15z" className="fill-c1" />
                    <rect
                        x={12}
                        y={18}
                        width={34}
                        height={5}
                        rx={2}
                        className="fill-brand-ink"
                    />
                </svg>
            );
        case 'drink':
        default:
            return (
                <svg viewBox="0 0 60 60" width={60} height={60} aria-hidden>
                    <rect
                        x={20}
                        y={14}
                        width={20}
                        height={40}
                        rx={6}
                        className="fill-c2"
                    />
                    <rect
                        x={25}
                        y={6}
                        width={10}
                        height={10}
                        className="fill-c2"
                    />
                    <rect
                        x={20}
                        y={28}
                        width={20}
                        height={10}
                        className="fill-brand-surface"
                    />
                </svg>
            );
    }
}
