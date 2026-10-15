import type { ReactNode } from 'react';

export const inputClass =
    'w-full rounded-md border border-ink/20 bg-white px-3 py-3 text-base text-ink placeholder-ink/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 aria-[invalid=true]:border-red-500';

/** Label + control + inline error. The control receives matching id / aria attributes from the caller. */
export function Field({ id, label, error, children, required = false }: { id: string; label: string; error?: string; children: ReactNode; required?: boolean }) {
    return (
        <div>
            <label htmlFor={id} className="mb-1 block text-sm font-medium text-ink">
                {label}
                {required && <span className="text-brand-dark"> *</span>}
            </label>
            {children}
            {error && (
                <p id={`${id}-error`} className="mt-1 text-sm text-red-600" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}

/** Hidden anti-spam field. Real visitors never see it; bots tend to fill every input. */
export function Honeypot({ value, onChange }: { value: string; onChange: (value: string) => void }) {
    return (
        <div aria-hidden="true" style={{ position: 'absolute', left: '-10000px', top: 'auto', width: 1, height: 1, overflow: 'hidden' }}>
            <label>
                Leave this field empty
                <input type="text" name="website" tabIndex={-1} autoComplete="off" value={value} onChange={(e) => onChange(e.target.value)} />
            </label>
        </div>
    );
}

export function ErrorBanner({ message }: { message: string | null }) {
    if (!message) return null;
    return (
        <p className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
            {message}
        </p>
    );
}
