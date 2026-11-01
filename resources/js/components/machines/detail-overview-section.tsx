import { BrandButton } from '@/components/frontend/brand-button';
import { MachineArt } from '@/components/frontend/machine-art';
import { Wrap } from '@/components/frontend/wrap';
import type { MachineDetailSpecItem, MachineItem, NavLink } from '@/types/frontend';

export function DetailOverviewSection({
    machine,
    status,
    specs,
    checks,
    cta,
}: {
    machine: MachineItem;
    status: string;
    specs: MachineDetailSpecItem[];
    checks: string[];
    cta: NavLink;
}) {
    return (
        <section className="pt-10 pb-16 md:pb-18">
            <Wrap>
                <p className="mb-3 text-sm text-brand-muted">
                    <a href="/" className="no-underline hover:underline">
                        Home
                    </a>
                    {' / '}
                    <a href="/machines" className="no-underline hover:underline">
                        Machines
                    </a>
                    {' / '}
                    {machine.name}
                </p>

                <div className="grid items-start gap-10 md:grid-cols-[4fr_6fr]">
                    <div className="grid place-items-center rounded-2xl border border-brand-line bg-brand-surface p-7">
                        <MachineArt
                            kind={machine.kind}
                            className="h-auto w-full max-w-xs"
                        />
                    </div>

                    <div>
                        <div className="flex flex-wrap items-center gap-1.5">
                            {machine.tags.map((tag) => (
                                <span
                                    key={tag}
                                    className="rounded bg-brand-sunk px-1.5 py-0.5 font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase"
                                >
                                    {tag}
                                </span>
                            ))}
                            <span className="inline-block rounded bg-good-bg px-2 py-0.5 font-mono text-xs font-semibold whitespace-nowrap text-good">
                                {status}
                            </span>
                        </div>

                        <h1 className="mt-2.5 font-display text-4xl leading-tight font-extrabold tracking-tight md:text-5xl">
                            {machine.name}
                        </h1>
                        <p className="mt-2.5 text-lg text-brand-muted">
                            {machine.desc}
                        </p>

                        <table className="my-4 w-full min-w-0 border-collapse text-sm">
                            <tbody>
                                {specs.map((row) => (
                                    <tr
                                        key={row.label}
                                        className="border-b border-brand-line"
                                    >
                                        <th className="w-2/5 py-2.5 text-left font-mono text-xs font-semibold tracking-wide text-brand-muted uppercase">
                                            {row.label}
                                        </th>
                                        <td className="py-2.5 text-left text-brand-ink">
                                            {row.value}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <ul className="mb-6 flex list-none flex-col gap-2 p-0">
                            {checks.map((item) => (
                                <li
                                    key={item}
                                    className="flex gap-2 text-brand-ink"
                                >
                                    <span
                                        aria-hidden
                                        className="font-bold text-good"
                                    >
                                        ✓
                                    </span>
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>

                        <BrandButton href={cta.href}>{cta.label}</BrandButton>
                    </div>
                </div>
            </Wrap>
        </section>
    );
}
