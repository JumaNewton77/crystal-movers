import { useEffect, useRef, useState, type FormEvent } from 'react';
import { api, ApiError, errorMessage } from '@/shared/api';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from './Icon';
import { ErrorBanner, Field, Honeypot, inputClass } from './formFields';

const MAX_FILES = 5;
const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
const MAX_VIDEO_BYTES = 50 * 1024 * 1024;

const empty = {
    name: '', phone: '', email: '', category: '', service_id: '', location: '', from_location: '', to_location: '',
    preferred_date: '', preferred_time: '', message: '',
};
const emptyDetails = {
    property_type: '', camera_count: '', tv_size: '', wall_type: '', issue_type: '', work_type: '', bracket: '',
    move_size: '', special_handling: '', appliance_type: '', existing_setup: '', furniture_type: '', quantity: '', assembly_type: '',
};

type Details = typeof emptyDetails;

/**
 * The quote / enquiry form (hero card, service pages and contact page). Posts to POST /api/quote-requests.
 * The customer picks a category and a service; a few extra questions appear only where they are useful.
 */
export default function QuoteForm({ compact = false, idPrefix = 'quote', defaultServiceSlug }: { compact?: boolean; idPrefix?: string; defaultServiceSlug?: string }) {
    const { services, service_categories } = useSite();
    const active = services.filter((s) => s.status === 'active');
    const categories = service_categories.filter((c) => active.some((s) => s.category === c.slug));

    const preset = active.find((s) => s.slug === defaultServiceSlug);
    const [values, setValues] = useState({ ...empty, category: preset?.category ?? '', service_id: preset ? String(preset.id) : '' });
    const [details, setDetails] = useState<Details>(emptyDetails);
    const [consent, setConsent] = useState(false);
    const [files, setFiles] = useState<File[]>([]);
    const [honeypot, setHoneypot] = useState('');
    const [errors, setErrors] = useState<ApiError | null>(null);
    const [banner, setBanner] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);
    const [reference, setReference] = useState<string | null>(null);
    const inFlight = useRef(false); // blocks a second submit before React re-renders the disabled button
    const [minDate, setMinDate] = useState<string | undefined>(undefined);

    // Set after mount so the server-rendered and browser markup stay identical.
    useEffect(() => {
        setMinDate(new Intl.DateTimeFormat('en-CA').format(new Date()));
    }, []);

    const categoryServices = active.filter((s) => s.category === values.category);
    const isMoving = values.category === 'moving';
    const chosenService = active.find((s) => String(s.id) === values.service_id);
    const isTvMounting = values.category === 'tv-satellite' && Boolean(chosenService?.slug.includes('mount'));

    const set = (name: keyof typeof empty) => (e: { target: { value: string } }) => setValues((v) => ({ ...v, [name]: e.target.value }));
    const setDetail = (name: keyof Details) => (e: { target: { value: string } }) => setDetails((d) => ({ ...d, [name]: e.target.value }));
    const error = (name: string) => errors?.field(name);
    const fieldProps = (name: keyof typeof empty) => ({
        id: `${idPrefix}-${name}`,
        name,
        value: values[name],
        onChange: set(name),
        className: inputClass,
        'aria-invalid': Boolean(error(name)) || undefined,
        'aria-describedby': error(name) ? `${idPrefix}-${name}-error` : undefined,
    });
    const detailProps = (name: keyof Details) => ({ id: `${idPrefix}-${name}`, name, value: details[name], onChange: setDetail(name), className: inputClass });

    function chooseCategory(slug: string) {
        const list = active.filter((s) => s.category === slug);
        // A category with a single service selects it straight away.
        setValues((v) => ({ ...v, category: slug, service_id: list.length === 1 ? String(list[0].id) : '' }));
        setDetails(emptyDetails);
    }

    function onFiles(list: FileList | null) {
        const picked = Array.from(list ?? []);
        setBanner(null);

        if (files.length + picked.length > MAX_FILES) {
            setBanner(t('form.filesTooMany'));
            return;
        }

        const tooBig = picked.find((f) => (f.type.startsWith('video/') ? f.size > MAX_VIDEO_BYTES : f.size > MAX_IMAGE_BYTES));
        if (tooBig) {
            setBanner(`"${tooBig.name}" is too large. Photos can be up to 5 MB and videos up to 50 MB.`);
            return;
        }

        setFiles((current) => [...current, ...picked]);
    }

    async function onSubmit(e: FormEvent) {
        e.preventDefault();
        if (inFlight.current) return;
        inFlight.current = true;
        setSubmitting(true);
        setErrors(null);
        setBanner(null);

        const body = new FormData();
        Object.entries(values).forEach(([key, value]) => {
            if (key !== 'category' && value !== '') body.append(key, value);
        });
        Object.entries(details).forEach(([key, value]) => {
            if (value !== '') body.append(`details[${key}]`, value);
        });
        if (consent) body.append('contact_consent', '1');
        body.append('website', honeypot);
        files.forEach((file) => body.append('attachments[]', file));

        try {
            const response = await api<{ reference: string }>('/quote-requests', { method: 'POST', body });
            setReference(response.data.reference);
            setValues(empty);
            setDetails(emptyDetails);
            setConsent(false);
            setFiles([]);
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

    if (reference) {
        return (
            <div className="rounded-lg bg-white p-6 text-center shadow-xl" role="status">
                <span className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-soft text-brand-dark">
                    <Icon name="check" className="h-8 w-8" />
                </span>
                <h2 className="mt-4 text-2xl font-extrabold text-ink">{t('form.successTitle')}</h2>
                <p className="mt-3 text-ink/80">
                    {t('form.success', { reference: '\u0000' })
                        .split('\u0000')
                        .map((part, i, all) => (
                            <span key={i}>
                                {part}
                                {i < all.length - 1 && <strong className="whitespace-nowrap text-brand-dark">{reference}</strong>}
                            </span>
                        ))}
                </p>
                <button type="button" onClick={() => setReference(null)} className="mt-5 text-sm font-semibold text-brand-dark hover:underline">
                    {t('form.another')}
                </button>
            </div>
        );
    }

    const extras = (
        <div className="space-y-3">
            <Field id={`${idPrefix}-message`} label={t('form.message')} error={error('message')}>
                <textarea {...fieldProps('message')} rows={3} maxLength={2000} />
            </Field>
            <div>
                <label htmlFor={`${idPrefix}-files`} className="mb-1 block text-sm font-medium text-ink">
                    {t('form.files')}
                </label>
                <input
                    id={`${idPrefix}-files`}
                    type="file"
                    multiple
                    accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                    onChange={(e) => {
                        onFiles(e.target.files);
                        e.target.value = '';
                    }}
                    className="block w-full text-sm text-ink file:mr-3 file:rounded-md file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:font-semibold file:text-brand-dark"
                />
                <p className="mt-1 text-xs text-ink/60">{t('form.filesHelp')}</p>
                {files.length > 0 && (
                    <ul className="mt-2 space-y-1 text-sm">
                        {files.map((file, index) => (
                            <li key={`${file.name}-${index}`} className="flex items-center justify-between gap-2 rounded bg-ink/5 px-2 py-1">
                                <span className="truncate">{file.name}</span>
                                <button type="button" onClick={() => setFiles((f) => f.filter((_, i) => i !== index))} className="shrink-0 text-red-600 hover:underline" aria-label={`Remove ${file.name}`}>
                                    <Icon name="x" className="h-4 w-4" />
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
                {error('attachments') && (
                    <p className="mt-1 text-sm text-red-600" role="alert">
                        {error('attachments')}
                    </p>
                )}
            </div>
        </div>
    );

    return (
        <form onSubmit={onSubmit} className="relative rounded-lg bg-white p-5 text-left shadow-xl sm:p-6" noValidate>
            <h2 className="text-xl font-extrabold text-ink">{t('form.title')}</h2>
            <p className="mt-1 text-sm text-ink/70">{t('form.subtitle')}</p>

            <div className="mt-4 space-y-3">
                <ErrorBanner message={banner} />

                <Field id={`${idPrefix}-name`} label={t('form.name')} error={error('name')} required>
                    <input {...fieldProps('name')} type="text" autoComplete="name" maxLength={120} required />
                </Field>
                <Field id={`${idPrefix}-phone`} label={t('form.phone')} error={error('phone')} required>
                    <input {...fieldProps('phone')} type="tel" inputMode="tel" autoComplete="tel" maxLength={30} required />
                </Field>
                <Field id={`${idPrefix}-email`} label={t('form.email')} error={error('email')}>
                    <input {...fieldProps('email')} type="email" inputMode="email" autoComplete="email" maxLength={190} />
                </Field>
                <Field id={`${idPrefix}-category`} label={t('form.category')} required>
                    <select id={`${idPrefix}-category`} value={values.category} onChange={(e) => chooseCategory(e.target.value)} className={inputClass} required>
                        <option value="">{t('form.selectCategory')}</option>
                        {categories.map((c) => (
                            <option key={c.slug} value={c.slug}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                </Field>
                {values.category && (
                    <Field id={`${idPrefix}-service_id`} label={t('form.service')} error={error('service_id')} required>
                        <select {...fieldProps('service_id')} required>
                            <option value="">{t('form.selectService')}</option>
                            {categoryServices.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.title}
                                </option>
                            ))}
                        </select>
                    </Field>
                )}

                {values.category &&
                    (isMoving ? (
                        <div className="grid gap-3 sm:grid-cols-2">
                            <Field id={`${idPrefix}-from_location`} label={t('form.from')} error={error('from_location')} required>
                                <input {...fieldProps('from_location')} type="text" maxLength={190} required />
                            </Field>
                            <Field id={`${idPrefix}-to_location`} label={t('form.to')} error={error('to_location')} required>
                                <input {...fieldProps('to_location')} type="text" maxLength={190} required />
                            </Field>
                        </div>
                    ) : (
                        <Field id={`${idPrefix}-location`} label={t('form.location')} error={error('location')} required>
                            <input {...fieldProps('location')} type="text" maxLength={190} required />
                        </Field>
                    ))}

                {isMoving && (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <Field id={`${idPrefix}-move_size`} label={t('form.moveSize')}>
                            <input {...detailProps('move_size')} type="text" maxLength={100} placeholder={t('form.moveSizePlaceholder')} />
                        </Field>
                        <Field id={`${idPrefix}-special_handling`} label={t('form.specialHandling')}>
                            <input {...detailProps('special_handling')} type="text" maxLength={300} placeholder={t('form.specialHandlingPlaceholder')} />
                        </Field>
                    </div>
                )}
                {values.category === 'cctv-security' && (
                    <div className="grid gap-3 sm:grid-cols-3">
                        <Field id={`${idPrefix}-property_type`} label={t('form.propertyType')}>
                            <select {...detailProps('property_type')}>
                                <option value="">-</option>
                                <option value="Home">{t('form.home')}</option>
                                <option value="Business">{t('form.business')}</option>
                            </select>
                        </Field>
                        <Field id={`${idPrefix}-camera_count`} label={t('form.cameraCount')}>
                            <input {...detailProps('camera_count')} type="text" inputMode="numeric" maxLength={10} />
                        </Field>
                        <Field id={`${idPrefix}-work_type`} label={t('form.cctvWork')}>
                            <select {...detailProps('work_type')}>
                                <option value="">-</option>
                                <option value="New installation">{t('form.cctvNew')}</option>
                                <option value="Existing-system repair or check">{t('form.cctvRepair')}</option>
                            </select>
                        </Field>
                    </div>
                )}
                {isTvMounting && (
                    <div className="grid gap-3 sm:grid-cols-3">
                        <Field id={`${idPrefix}-tv_size`} label={t('form.tvSize')}>
                            <input {...detailProps('tv_size')} type="text" inputMode="numeric" maxLength={10} />
                        </Field>
                        <Field id={`${idPrefix}-wall_type`} label={t('form.wallType')}>
                            <input {...detailProps('wall_type')} type="text" maxLength={100} />
                        </Field>
                        <Field id={`${idPrefix}-bracket`} label={t('form.bracket')}>
                            <select {...detailProps('bracket')}>
                                <option value="">-</option>
                                <option value="Yes">{t('form.yes')}</option>
                                <option value="No">{t('form.no')}</option>
                                <option value="Not sure">{t('form.notSure')}</option>
                            </select>
                        </Field>
                    </div>
                )}
                {values.category === 'internet-networking' && (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <Field id={`${idPrefix}-issue_type`} label={t('form.wifiWork')}>
                            <select {...detailProps('issue_type')}>
                                <option value="">-</option>
                                <option value="Installation or setup">{t('form.wifiInstall')}</option>
                                <option value="Troubleshooting">{t('form.wifiTroubleshoot')}</option>
                            </select>
                        </Field>
                        <Field id={`${idPrefix}-property_type`} label={t('form.propertyType')}>
                            <select {...detailProps('property_type')}>
                                <option value="">-</option>
                                <option value="Home">{t('form.home')}</option>
                                <option value="Business">{t('form.business')}</option>
                            </select>
                        </Field>
                    </div>
                )}
                {values.category === 'washing-machine' && (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <Field id={`${idPrefix}-appliance_type`} label={t('form.applianceType')}>
                            <input {...detailProps('appliance_type')} type="text" maxLength={100} placeholder={t('form.applianceTypePlaceholder')} />
                        </Field>
                        <Field id={`${idPrefix}-work_type`} label={t('form.homeWork')}>
                            <select {...detailProps('work_type')}>
                                <option value="">-</option>
                                <option value="Installation">{t('form.workInstall')}</option>
                                <option value="Repair">{t('form.workRepair')}</option>
                            </select>
                        </Field>
                    </div>
                )}
                {(values.category === 'lighting' || values.category === 'shower') && (
                    <div className="grid gap-3 sm:grid-cols-2">
                        <Field id={`${idPrefix}-work_type`} label={t('form.homeWork')}>
                            <select {...detailProps('work_type')}>
                                <option value="">-</option>
                                <option value="Installation">{t('form.workInstall')}</option>
                                <option value="Replacement">{t('form.workReplace')}</option>
                                <option value="Repair">{t('form.workRepair')}</option>
                            </select>
                        </Field>
                        <Field id={`${idPrefix}-existing_setup`} label={t('form.existingSetup')}>
                            <input {...detailProps('existing_setup')} type="text" maxLength={300} />
                        </Field>
                    </div>
                )}
                {values.category === 'furniture-assembly' && (
                    <div className="grid gap-3 sm:grid-cols-3">
                        <Field id={`${idPrefix}-furniture_type`} label={t('form.furnitureType')}>
                            <input {...detailProps('furniture_type')} type="text" maxLength={100} placeholder={t('form.furnitureTypePlaceholder')} />
                        </Field>
                        <Field id={`${idPrefix}-quantity`} label={t('form.quantity')}>
                            <input {...detailProps('quantity')} type="text" inputMode="numeric" maxLength={10} />
                        </Field>
                        <Field id={`${idPrefix}-assembly_type`} label={t('form.assemblyWork')}>
                            <select {...detailProps('assembly_type')}>
                                <option value="">-</option>
                                <option value="Assembly">{t('form.assemble')}</option>
                                <option value="Disassembly">{t('form.disassemble')}</option>
                                <option value="Both">{t('form.both')}</option>
                            </select>
                        </Field>
                    </div>
                )}

                <div className="grid gap-3 sm:grid-cols-2">
                    <Field id={`${idPrefix}-preferred_date`} label={t('form.date')} error={error('preferred_date')}>
                        <input {...fieldProps('preferred_date')} type="date" min={minDate} />
                    </Field>
                    <Field id={`${idPrefix}-preferred_time`} label={t('form.time')} error={error('preferred_time')}>
                        <input {...fieldProps('preferred_time')} type="time" />
                    </Field>
                </div>

                {compact ? (
                    <details className="rounded-md border border-ink/10 p-3">
                        <summary className="cursor-pointer text-sm font-semibold text-brand-dark">+ {t('form.message')} / {t('form.files')}</summary>
                        <div className="mt-3">{extras}</div>
                    </details>
                ) : (
                    extras
                )}

                <div>
                    <label className="flex items-start gap-2 text-sm text-ink">
                        <input type="checkbox" checked={consent} onChange={(e) => setConsent(e.target.checked)} className="mt-1 h-4 w-4 accent-brand" required />
                        <span>{t('form.consent')}</span>
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
                    className="flex w-full items-center justify-center gap-2 rounded-md bg-brand px-6 py-3 text-base font-semibold text-ink hover:bg-brand-dark hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {submitting ? t('form.submitting') : t('form.submit')}
                    {!submitting && <Icon name="arrow" className="h-4 w-4" />}
                </button>
            </div>
        </form>
    );
}
