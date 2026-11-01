import { motion, useReducedMotion } from 'motion/react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export const brandCardHoverClass =
    'transition duration-300 hover:-translate-y-2 hover:border-brand-ink hover:shadow-[0_12px_28px_-16px_rgba(15,30,24,0.35)]';

type RevealProps = {
    children: ReactNode;
    className?: string;
    delay?: number;
};

export function Reveal({ children, className, delay = 0 }: RevealProps) {
    const reduceMotion = useReducedMotion();

    return (
        <motion.div
            className={cn('h-full min-w-0', className)}
            initial={reduceMotion ? false : { opacity: 0, y: 28 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, amount: 0.2 }}
            transition={{
                duration: reduceMotion ? 0 : 0.55,
                ease: [0.22, 1, 0.36, 1],
                delay: reduceMotion ? 0 : delay,
            }}
        >
            {children}
        </motion.div>
    );
}
