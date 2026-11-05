import { Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { AnimatePresence, motion, useReducedMotion } from 'motion/react';
import { useState } from 'react';
import { BrandButton } from '@/components/frontend/brand-button';
import { Wrap } from '@/components/frontend/wrap';
import type { SiteData } from '@/types/frontend';
import { cn } from '@/lib/utils';

type SiteHeaderProps = {
    site: SiteData;
};

export function SiteHeader({ site }: SiteHeaderProps) {
    const [open, setOpen] = useState(false);
    const reduceMotion = useReducedMotion();

    const closeMenu = () => setOpen(false);

    const panelTransition = {
        duration: reduceMotion ? 0 : 0.25,
        ease: 'easeOut' as const,
    };

    return (
        <header className="sticky top-0 z-40 bg-petrol text-on-petrol">
            <div className="bg-petrol-2 text-sm">
                <Wrap className="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 py-1.5">
                    <nav
                        aria-label="Secondary"
                        className="flex flex-wrap gap-3.5"
                    >
                        {site.topNav.map((item) => (
                            <a
                                key={item.label}
                                href={item.href}
                                className="opacity-85 no-underline hover:opacity-100 hover:underline hover:decoration-brass"
                            >
                                {item.label}
                            </a>
                        ))}
                    </nav>
                    <a
                        href={site.operatorCta.href}
                        className="font-semibold text-brass no-underline opacity-100"
                    >
                        {site.operatorCta.label}
                    </a>
                </Wrap>
            </div>

            <Wrap>
                <div className="flex items-center gap-5.5 py-3.5">
                    <Link
                        href="/"
                        onClick={closeMenu}
                        className="flex max-w-[14rem] items-center gap-2.5 font-display text-base font-extrabold leading-tight text-on-petrol no-underline sm:max-w-xs sm:text-lg lg:max-w-sm lg:text-xl"
                    >
                        <svg
                            width={34}
                            height={34}
                            viewBox="0 0 34 34"
                            aria-hidden
                        >
                            <rect
                                x={3}
                                y={2}
                                width={28}
                                height={30}
                                rx={5}
                                className="fill-brass"
                            />
                            <rect
                                x={7}
                                y={6}
                                width={13}
                                height={18}
                                rx={2}
                                className="fill-petrol"
                            />
                            <circle
                                cx={25}
                                cy={10}
                                r={2}
                                className="fill-petrol"
                            />
                            <rect
                                x={23}
                                y={15}
                                width={4}
                                height={7}
                                rx={1}
                                className="fill-petrol"
                            />
                            <rect
                                x={7}
                                y={26}
                                width={20}
                                height={3}
                                rx={1.5}
                                className="fill-petrol"
                            />
                        </svg>
                        <span>
                            {site.name}
                            <small className="mt-0.5 block font-mono text-xs leading-tight font-medium tracking-wider text-on-petrol/70 uppercase">
                                {site.tagline}
                            </small>
                        </span>
                    </Link>

                    <ul
                        className="ml-auto hidden list-none items-center gap-5 p-0 lg:flex"
                    >
                        {site.mainNav.map((item) => (
                            <li key={item.label}>
                                <a
                                    href={item.href}
                                    aria-current={
                                        item.current ? 'page' : undefined
                                    }
                                    className={cn(
                                        'text-sm font-semibold tracking-wide text-on-petrol/85 uppercase no-underline hover:text-on-petrol hover:underline hover:decoration-brass hover:decoration-2 hover:underline-offset-8',
                                        item.current &&
                                            'text-on-petrol underline decoration-brass decoration-2 underline-offset-8',
                                    )}
                                >
                                    {item.label}
                                </a>
                            </li>
                        ))}
                    </ul>

                    <BrandButton
                        href={site.headerCta.href}
                        className="hidden px-4 py-2.5 text-sm lg:inline-flex"
                    >
                        {site.headerCta.label}
                    </BrandButton>

                    <button
                        type="button"
                        className="ml-auto rounded-lg border-2 border-current p-2 lg:ml-0 lg:hidden"
                        aria-expanded={open}
                        aria-controls="site-main-nav-mobile"
                        aria-label={open ? 'Close menu' : 'Open menu'}
                        onClick={() => setOpen((value) => !value)}
                    >
                        {open ? (
                            <X className="size-5" aria-hidden />
                        ) : (
                            <Menu className="size-5" aria-hidden />
                        )}
                    </button>
                </div>

                <AnimatePresence initial={false}>
                    {open ? (
                        <motion.div
                            key="mobile-nav"
                            id="site-main-nav-mobile"
                            initial={
                                reduceMotion
                                    ? false
                                    : { height: 0, opacity: 0 }
                            }
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={
                                reduceMotion
                                    ? undefined
                                    : { height: 0, opacity: 0 }
                            }
                            transition={panelTransition}
                            className="overflow-hidden lg:hidden"
                        >
                            <ul className="flex list-none flex-col items-start gap-3 pb-4 p-0">
                                {site.mainNav.map((item) => (
                                    <li key={item.label}>
                                        <a
                                            href={item.href}
                                            aria-current={
                                                item.current
                                                    ? 'page'
                                                    : undefined
                                            }
                                            onClick={closeMenu}
                                            className={cn(
                                                'text-sm font-semibold tracking-wide text-on-petrol/85 uppercase no-underline hover:text-on-petrol hover:underline hover:decoration-brass hover:decoration-2 hover:underline-offset-8',
                                                item.current &&
                                                    'text-on-petrol underline decoration-brass decoration-2 underline-offset-8',
                                            )}
                                        >
                                            {item.label}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                            <BrandButton
                                href={site.headerCta.href}
                                onClick={closeMenu}
                                className="mb-4 inline-flex px-4 py-2.5 text-sm"
                            >
                                {site.headerCta.label}
                            </BrandButton>
                        </motion.div>
                    ) : null}
                </AnimatePresence>
            </Wrap>
        </header>
    );
}
