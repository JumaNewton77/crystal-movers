import { useState, type FormEvent } from 'react';
import { api, ApiError, errorMessage } from '@/shared/api';
import { t } from '@/shared/i18n';
import { ErrorBanner, Field, Honeypot, inputClass } from './formFields';

const empty = { customer_name: '', location: '', rating: '5', review: '' };

/** Lets a real customer share a review. It is saved as "pending" and appears only after an admin approves it. */
export default function TestimonialForm() {
    const [values, setValues] = useState(empty);
    const [honeypot, setHoneypot] = useState('');
    const [errors, setErrors] = useState<ApiError | null>(null);
    const [banner, setBanner] = useState<string | null>(null);
    const [sent, setSent] = useState(false);
    const [busy, setBusy] = useState(false);

    const error = (name: string) => errors?.field(name);
    const props = (name: keyof typeof empty) => ({
        id: `review-${name}`,
        name,
        value: values[name],
        onChange: (e: { target: { value: string } }) => setValues((v) => ({ ...v, [name]: e.target.value })),
        className: inputClass,
        'aria-invalid': Boolean(error(name)) || undefined,
    });

    async function onSubmit(e: FormEvent) {
        e.preventDefault();
        setBusy(true);
        setErrors(null);
        setBanner(null);
        try {
            await api('/testimonials', { method: 'POST', body: { ...values, rating: Number(values.rating), website: honeypot } });
            setSent(true);
            setValues(empty);
        } catch (err) {
            if (err instanceof ApiError && err.status === 422) {
                setErrors(err);
                setBanner(t('form.fixErrors'));
            } else {
                setBanner(errorMessage(err));
            }
        } finally {
            setBusy(false);
        }
    }

    if (sent) {
        return (
            <p className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                {t('review.success')}
            </p>
        );
    }

    return (
        <form onSubmit={onSubmit} className="relative space-y-3 rounded-lg border border-ink/10 bg-white p-5 shadow-sm" noValidate>
            <h3 className="text-lg font-extrabold text-ink">{t('review.title')}</h3>
            <ErrorBanner message={banner} />
            <div className="grid gap-3 sm:grid-cols-2">
                <Field id="review-customer_name" label={t('review.name')} error={error('customer_name')} required>
                    <input {...props('customer_name')} type="text" maxLength={120} autoComplete="name" required />
                </Field>
                <Field id="review-location" label={t('review.location')} error={error('location')}>
                    <input {...props('location')} type="text" maxLength={120} />
                </Field>
            </div>
            <Field id="review-rating" label={t('review.rating')} error={error('rating')} required>
                <select {...props('rating')}>
                    {[5, 4, 3, 2, 1].map((n) => (
                        <option key={n} value={n}>{'★'.repeat(n)} ({n})</option>
                    ))}
                </select>
            </Field>
            <Field id="review-review" label={t('review.text')} error={error('review')} required>
                <textarea {...props('review')} rows={4} maxLength={1000} required />
            </Field>
            <Honeypot value={honeypot} onChange={setHoneypot} />
            <button type="submit" disabled={busy} className="rounded-md bg-brand px-6 py-3 text-base font-semibold text-white hover:bg-brand-dark disabled:opacity-60">
                {busy ? t('review.sending') : t('review.submit')}
            </button>
            <p className="text-xs text-ink/60">{t('review.note')}</p>
        </form>
    );
}
