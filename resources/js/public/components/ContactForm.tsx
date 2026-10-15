import { useRef, useState, type FormEvent } from 'react';
import { api, ApiError, errorMessage } from '@/shared/api';
import { t } from '@/shared/i18n';
import { ErrorBanner, Field, Honeypot, inputClass } from './formFields';

const empty = { name: '', phone: '', email: '', subject: '', message: '' };

/** General enquiry form. Posts to POST /api/contact. */
export default function ContactForm() {
    const [values, setValues] = useState(empty);
    const [honeypot, setHoneypot] = useState('');
    const [consent, setConsent] = useState(false);
    const [errors, setErrors] = useState<ApiError | null>(null);
    const [banner, setBanner] = useState<string | null>(null);
    const [sent, setSent] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const inFlight = useRef(false); // blocks a second submit before React re-renders the disabled button

    const error = (name: string) => errors?.field(name);
    const props = (name: keyof typeof empty) => ({
        id: `contact-${name}`,
        name,
        value: values[name],
        onChange: (e: { target: { value: string } }) => setValues((v) => ({ ...v, [name]: e.target.value })),
        className: inputClass,
        'aria-invalid': Boolean(error(name)) || undefined,
        'aria-describedby': error(name) ? `contact-${name}-error` : undefined,
    });

    async function onSubmit(e: FormEvent) {
        e.preventDefault();
        if (inFlight.current) return;
        inFlight.current = true;
        setSubmitting(true);
        setErrors(null);
        setBanner(null);

        try {
            await api('/contact', { method: 'POST', body: { ...values, contact_consent: consent ? '1' : '', website: honeypot } });
            setSent(true);
            setValues(empty);
            setConsent(false);
        } catch (err) {
            if (err instanceof ApiError && err.status === 422) {
                setErrors(err);
                setBanner(t('form.fixErrors'));
            } else {
                setBanner(errorMessage(err));
            }
        } finally {
            inFlight.current = false;
            setSubmitting(false);
        }
    }

    return (
        <form onSubmit={onSubmit} className="relative space-y-3 rounded-lg border border-ink/10 bg-white p-5 shadow-sm sm:p-6" noValidate>
            <h2 className="text-xl font-extrabold text-ink">{t('contactForm.title')}</h2>

            {sent && (
                <p className="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800" role="status">
                    {t('contactForm.success')}
                </p>
            )}
            <ErrorBanner message={banner} />

            <Field id="contact-name" label={t('contactForm.name')} error={error('name')} required>
                <input {...props('name')} type="text" autoComplete="name" maxLength={120} required />
            </Field>
            <div className="grid gap-3 sm:grid-cols-2">
                <Field id="contact-phone" label={t('contactForm.phone')} error={error('phone')}>
                    <input {...props('phone')} type="tel" inputMode="tel" autoComplete="tel" maxLength={30} />
                </Field>
                <Field id="contact-email" label={t('contactForm.email')} error={error('email')} required>
                    <input {...props('email')} type="email" inputMode="email" autoComplete="email" maxLength={190} required />
                </Field>
            </div>
            <Field id="contact-subject" label={t('contactForm.subject')} error={error('subject')} required>
                <input {...props('subject')} type="text" maxLength={190} required />
            </Field>
            <Field id="contact-message" label={t('contactForm.message')} error={error('message')} required>
                <textarea {...props('message')} rows={5} maxLength={4000} required />
            </Field>

            <div>
                <label className="flex items-start gap-2 text-sm text-ink">
                    <input type="checkbox" checked={consent} onChange={(e) => setConsent(e.target.checked)} className="mt-1 h-4 w-4 accent-brand" required />
                    <span>{t('contactForm.consent')}</span>
                </label>
                {error('contact_consent') && (
                    <p className="mt-1 text-sm text-red-600" role="alert">
                        {error('contact_consent')}
                    </p>
                )}
            </div>

            <Honeypot value={honeypot} onChange={setHoneypot} />

            <button
                type="submit"
                disabled={submitting}
                className="w-full rounded-md bg-brand px-6 py-3 text-base font-semibold text-white hover:bg-brand-dark disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
            >
                {submitting ? t('contactForm.submitting') : t('contactForm.submit')}
            </button>
        </form>
    );
}
