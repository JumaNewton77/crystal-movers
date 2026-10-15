import type { ReactNode } from 'react';
import { Link } from 'react-router-dom';
import Icon from './Icon';

export function Container({ children, className = '' }: { children: ReactNode; className?: string }) {
    return <div className={`mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 ${className}`}>{children}</div>;
}

export function SectionHeading({
    eyebrow,
    title,
    intro,
    center = false,
    light = false,
}: {
    eyebrow?: string;
    title: string;
    intro?: string;
    center?: boolean;
    light?: boolean;
}) {
    return (
        <div className={center ? 'mx-auto max-w-2xl text-center' : 'max-w-xl'}>
            {eyebrow && (
                <p className={`flex items-center gap-2 text-sm font-semibold ${center ? 'justify-center' : ''} ${light ? 'text-brand' : 'text-brand-dark'}`}>
                    <span className="h-0.5 w-8 bg-brand" aria-hidden="true" />
                    {eyebrow}
                </p>
            )}
            <h2 className={`mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl ${light ? 'text-white' : 'text-ink'}`}>{title}</h2>
            {intro && <p className={`mt-3 text-base ${light ? 'text-white/80' : 'text-ink/70'}`}>{intro}</p>}
        </div>
    );
}

const base =
    'inline-flex items-center justify-center gap-2 rounded-md px-6 py-3 text-base font-semibold transition-colors focus-visible:outline-offset-2';

const variants = {
    primary: 'bg-brand text-white hover:bg-brand-dark',
    outline: 'border border-white/70 text-white hover:border-brand hover:text-brand',
    outlineDark: 'border border-ink/30 text-ink hover:border-brand hover:text-brand-dark',
    dark: 'bg-ink text-white hover:bg-black',
} as const;

export function ButtonLink({
    to,
    href,
    variant = 'primary',
    children,
    arrow = false,
    className = '',
    external = false,
}: {
    to?: string;
    href?: string;
    variant?: keyof typeof variants;
    children: ReactNode;
    arrow?: boolean;
    className?: string;
    external?: boolean;
}) {
    const classes = `${base} ${variants[variant]} ${className}`;
    const content = (
        <>
            {children}
            {arrow && <Icon name="arrow" className="h-4 w-4" />}
        </>
    );

    if (href) {
        return (
            <a href={href} className={classes} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>
                {content}
            </a>
        );
    }

    return (
        <Link to={to ?? '/'} className={classes}>
            {content}
        </Link>
    );
}

export function ComingSoonBadge({ label }: { label: string }) {
    return <span className="inline-block rounded bg-brand-soft px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-brand-dark">{label}</span>;
}
