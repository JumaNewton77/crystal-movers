import { useEffect, useState } from 'react';
import { api, ApiError, errorMessage } from '@/shared/api';
import { Alert, Button, Card, Field, Input, mediaUrl, PageHeader, Spinner, Textarea } from '../components/ui';

interface Item { key: string; value: string | null; group: string }

interface Props {
    title: string;
    help: string;
    /** API path, e.g. /admin/settings */
    path: string;
    /** Request body wrapper key. */
    bodyKey: 'settings' | 'content';
    isImage: (key: string) => boolean;
}

const LONG = /text|description|intro|body|insurance|answer|bio|summary|note/i;

function humanise(key: string): string {
    const label = key.replace(/[._]+/g, ' ').replace(/\bseo\b/i, 'SEO').replace(/\burl\b/i, 'URL').replace(/\bog\b/i, 'social sharing').trim();
    return label.charAt(0).toUpperCase() + label.slice(1);
}

export default function KeyValuePage({ title, help, path, bodyKey, isImage }: Props) {
    const [items, setItems] = useState<Item[] | null>(null);
    const [values, setValues] = useState<Record<string, string>>({});
    const [errors, setErrors] = useState<ApiError | null>(null);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [busy, setBusy] = useState(false);

    function apply(list: Item[]) {
        setItems(list);
        setValues(Object.fromEntries(list.map((i) => [i.key, i.value ?? ''])));
    }

    useEffect(() => {
        api<Item[]>(path).then((r) => apply(r.data)).catch((e) => setError(errorMessage(e)));
    }, [path]);

    async function save() {
        if (!items) return;
        setBusy(true);
        setError('');
        setNotice('');
        setErrors(null);
        const body: Record<string, string> = {};
        for (const i of items) if (!isImage(i.key)) body[i.key] = values[i.key] ?? '';
        try {
            const r = await api<Item[]>(path, { method: 'PUT', body: { [bodyKey]: body } });
            // Keep the image values already shown; refresh text from the server.
            apply(r.data);
            setNotice('Saved. Changes are live on the website straight away.');
        } catch (e) {
            if (e instanceof ApiError && e.status === 422) setErrors(e);
            else setError(errorMessage(e));
        } finally {
            setBusy(false);
        }
    }

    async function upload(key: string, file: File | undefined) {
        if (!file) return;
        setError('');
        setNotice('');
        const form = new FormData();
        form.append('image', file);
        try {
            const r = await api<{ key: string; value: string }>(`${path}/${key}/image`, { method: 'POST', body: form });
            setValues((v) => ({ ...v, [key]: r.data.value }));
            setItems((list) => list?.map((i) => (i.key === key ? { ...i, value: r.data.value } : i)) ?? null);
            setNotice('Image replaced. It is live on the website now.');
        } catch (e) {
            setError(e instanceof ApiError ? e.field('image') ?? e.message : errorMessage(e));
        }
    }

    async function removeImage(key: string) {
        if (!window.confirm('Remove this image? The website will show a neutral placeholder until you upload another.')) return;
        setError('');
        setNotice('');
        try {
            await api(`${path}/${key}/image`, { method: 'DELETE' });
            setValues((v) => ({ ...v, [key]: '' }));
            setItems((list) => list?.map((i) => (i.key === key ? { ...i, value: null } : i)) ?? null);
            setNotice('Image removed.');
        } catch (e) {
            setError(errorMessage(e));
        }
    }

    if (!items) return error ? <Alert>{error}</Alert> : <Spinner />;

    const groups = [...new Set(items.map((i) => i.group))];

    return (
        <>
            <PageHeader title={title}>
                <Button disabled={busy} onClick={() => void save()}>{busy ? 'Saving…' : 'Save changes'}</Button>
            </PageHeader>
            <p className="mb-4 text-sm text-ink/70">{help}</p>
            <Alert>{error || (errors ? 'Some values are not valid. Please check the highlighted fields.' : '')}</Alert>
            <Alert kind="success">{notice}</Alert>

            <div className="mt-4 space-y-5">
                {groups.map((group) => (
                    <Card key={group}>
                        <h2 className="mb-3 text-lg font-bold capitalize">{group.replace(/[._]/g, ' ')}</h2>
                        <div className="grid gap-4 md:grid-cols-2">
                            {items.filter((i) => i.group === group).map((i) => {
                                const err = errors?.field(`${bodyKey}.${i.key}`);
                                if (isImage(i.key)) {
                                    const src = mediaUrl(values[i.key]);
                                    return (
                                        <Field key={i.key} label={humanise(i.key)} hint="Choose a JPG, PNG or WebP (up to 8 MB). It replaces the current image immediately.">
                                            <div>
                                                {src ? <img src={src} alt="" className="mb-2 h-32 rounded object-cover" /> : <p className="mb-2 text-xs text-ink/60">No image set.</p>}
                                                <input type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => { void upload(i.key, e.target.files?.[0]); e.target.value = ''; }} className="text-sm" />
                                                {src && <Button variant="ghost" className="mt-2" onClick={() => void removeImage(i.key)}>Remove image</Button>}
                                            </div>
                                        </Field>
                                    );
                                }
                                const long = LONG.test(i.key) || (values[i.key] ?? '').length > 90;
                                return (
                                    <div key={i.key} className={long ? 'md:col-span-2' : ''}>
                                        <Field label={humanise(i.key)} error={err}>
                                            {long ? (
                                                <Textarea value={values[i.key] ?? ''} onChange={(e) => setValues({ ...values, [i.key]: e.target.value })} />
                                            ) : (
                                                <Input value={values[i.key] ?? ''} onChange={(e) => setValues({ ...values, [i.key]: e.target.value })} />
                                            )}
                                        </Field>
                                    </div>
                                );
                            })}
                        </div>
                    </Card>
                ))}
            </div>
            <div className="mt-5"><Button disabled={busy} onClick={() => void save()}>{busy ? 'Saving…' : 'Save changes'}</Button></div>
        </>
    );
}
