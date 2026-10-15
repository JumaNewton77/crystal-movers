import { useEffect, useRef, type ButtonHTMLAttributes, type InputHTMLAttributes, type ReactNode, type SelectHTMLAttributes, type TextareaHTMLAttributes } from 'react';

export function mediaUrl(path: string | null | undefined): string | null {
    if (!path) return null;
    return path.startsWith('/') || path.startsWith('http') ? path : `/storage/${path}`;
}

export function formatDate(value: string | null | undefined, withTime = false): string {
    if (!value) return '—';
    const date = new Date(value.length === 10 ? `${value}T00:00:00` : value);
    return date.toLocaleString('en-KE', withTime ? { dateStyle: 'medium', timeStyle: 'short' } : { dateStyle: 'medium' });
}

const inputClass = 'w-full rounded-md border border-ink/20 bg-white px-3 py-2 text-sm focus:border-brand';

export function Button({ variant = 'primary', className = '', ...props }: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: 'primary' | 'ghost' | 'danger' }) {
    const styles = {
        primary: 'bg-brand text-white hover:bg-brand-dark',
        ghost: 'border border-ink/20 bg-white text-ink hover:bg-ink/5',
        danger: 'border border-ink bg-white text-ink hover:bg-ink hover:text-white',
    }[variant];
    return <button type="button" {...props} className={`rounded-md px-4 py-2 text-sm font-semibold disabled:opacity-50 ${styles} ${className}`} />;
}

export function Field({ label, error, hint, children }: { label: string; error?: string; hint?: string; children: ReactNode }) {
    return (
        <label className="block text-sm">
            <span className="mb-1 block font-medium text-ink">{label}</span>
            {children}
            {hint && !error && <span className="mt-1 block text-xs text-ink/60">{hint}</span>}
            {error && <span className="mt-1 block text-xs font-medium text-red-700">{error}</span>}
        </label>
    );
}

export const Input = (props: InputHTMLAttributes<HTMLInputElement>) => <input {...props} className={`${inputClass} ${props.className ?? ''}`} />;
export const Textarea = (props: TextareaHTMLAttributes<HTMLTextAreaElement>) => <textarea rows={4} {...props} className={`${inputClass} ${props.className ?? ''}`} />;
export const Select = (props: SelectHTMLAttributes<HTMLSelectElement>) => <select {...props} className={`${inputClass} ${props.className ?? ''}`} />;

export function Alert({ kind = 'error', children }: { kind?: 'error' | 'success'; children: ReactNode }) {
    if (!children) return null;
    return (
        <div role={kind === 'error' ? 'alert' : 'status'} className={`rounded-md border px-3 py-2 text-sm ${kind === 'error' ? 'border-red-300 bg-red-50 text-red-800' : 'border-brand/40 bg-brand-soft text-ink'}`}>
            {children}
        </div>
    );
}

export function Spinner({ label = 'Loading…' }: { label?: string }) {
    return <p className="py-8 text-center text-sm text-ink/60">{label}</p>;
}

export function PageHeader({ title, children }: { title: string; children?: ReactNode }) {
    return (
        <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h1 className="text-2xl font-bold text-ink">{title}</h1>
            <div className="flex gap-2">{children}</div>
        </div>
    );
}

export function Card({ children, className = '' }: { children: ReactNode; className?: string }) {
    return <div className={`rounded-lg border border-ink/10 bg-white p-4 shadow-sm ${className}`}>{children}</div>;
}

const badgeTone: Record<string, string> = {
    new: 'bg-brand text-white',
    pending: 'bg-brand text-white',
    approved: 'bg-ink text-white',
    active: 'bg-ink text-white',
    completed: 'bg-ink text-white',
};

export function Badge({ value, label }: { value: string; label?: string }) {
    const tone = badgeTone[value] ?? 'bg-brand-soft text-ink';
    return <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold ${tone}`}>{label ?? value.replace(/_/g, ' ')}</span>;
}

/** Modal dialog with focus on open and Escape to close. */
export function Modal({ title, onClose, children }: { title: string; onClose: () => void; children: ReactNode }) {
    const ref = useRef<HTMLDivElement>(null);
    useEffect(() => {
        ref.current?.focus();
        const onKey = (e: KeyboardEvent) => e.key === 'Escape' && onClose();
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [onClose]);

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink/60 p-4" onMouseDown={(e) => e.target === e.currentTarget && onClose()}>
            <div ref={ref} tabIndex={-1} role="dialog" aria-modal="true" aria-label={title} className="my-8 w-full max-w-2xl rounded-lg bg-white p-5 shadow-xl outline-none">
                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-bold">{title}</h2>
                    <button type="button" onClick={onClose} aria-label="Close" className="text-2xl leading-none text-ink/60 hover:text-ink">×</button>
                </div>
                {children}
            </div>
        </div>
    );
}
