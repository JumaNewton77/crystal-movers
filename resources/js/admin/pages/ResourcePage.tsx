import { useCallback, useEffect, useState, type FormEvent, type ReactNode } from 'react';
import { api, ApiError, errorMessage } from '@/shared/api';
import { Alert, Button, Field, Input, mediaUrl, Modal, PageHeader, Select, Spinner, Textarea } from '../components/ui';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type Row = Record<string, any> & { id: number };

export interface FieldDef {
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'number' | 'select' | 'checkbox' | 'image';
    hint?: string;
    required?: boolean;
    options?: { value: string; label: string }[];
    /** Lookup endpoint (e.g. /admin/lookups/service-categories) used to fill a select. */
    lookup?: string;
    /** For image fields: the record property holding the stored path. */
    column?: string;
    defaultValue?: string | boolean;
}

export interface ResourceConfig {
    path: string; // e.g. /admin/faqs
    title: string;
    singular: string;
    fields: FieldDef[];
    /** Short summary shown in the list. */
    summary: (row: Row) => ReactNode;
    /** Quick status buttons shown in each list row (e.g. approve / hide). */
    quickActions?: (row: Row) => { label: string; body: Record<string, unknown> }[];
    help?: string;
    /** Set false for lists ordered by date (the blog): hides the up/down arrows. */
    reorderable?: boolean;
}

type Values = Record<string, string | boolean | File | null>;

function initialValues(config: ResourceConfig, row: Row | null): Values {
    const values: Values = {};
    for (const f of config.fields) {
        if (f.type === 'image') values[f.name] = null;
        else if (f.type === 'checkbox') values[f.name] = row ? Boolean(row[f.name]) : (f.defaultValue as boolean | undefined) ?? true;
        else values[f.name] = row ? String(row[f.name] ?? '') : String(f.defaultValue ?? '');
    }
    return values;
}

export default function ResourcePage({ config }: { config: ResourceConfig }) {
    const [rows, setRows] = useState<Row[] | null>(null);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [editing, setEditing] = useState<Row | 'new' | null>(null);

    const load = useCallback(() => {
        api<Row[]>(config.path).then((r) => { setRows(r.data); setError(''); }).catch((e) => setError(errorMessage(e)));
    }, [config.path]);
    useEffect(() => { setRows(null); load(); }, [load]);

    async function remove(row: Row) {
        if (!window.confirm(`Delete this ${config.singular}? This cannot be undone.`)) return;
        try {
            await api(`${config.path}/${row.id}`, { method: 'DELETE' });
            setNotice(`${config.singular} deleted.`);
            load();
        } catch (e) { setError(errorMessage(e)); }
    }

    async function move(index: number, delta: number) {
        if (!rows) return;
        const next = [...rows];
        const target = index + delta;
        if (target < 0 || target >= next.length) return;
        [next[index], next[target]] = [next[target], next[index]];
        setRows(next);
        try {
            await api(`${config.path}/reorder`, { method: 'POST', body: { ids: next.map((r) => r.id) } });
        } catch (e) { setError(errorMessage(e)); load(); }
    }

    async function quick(row: Row, body: Record<string, unknown>) {
        try {
            await api(`${config.path}/${row.id}`, { method: 'PATCH', body });
            load();
        } catch (e) { setError(errorMessage(e)); }
    }

    return (
        <>
            <PageHeader title={config.title}>
                <Button onClick={() => { setNotice(''); setEditing('new'); }}>Add {config.singular}</Button>
            </PageHeader>
            {config.help && <p className="mb-4 text-sm text-ink/70">{config.help}</p>}
            <Alert>{error}</Alert>
            <Alert kind="success">{notice}</Alert>
            {!rows && !error && <Spinner />}
            {rows?.length === 0 && <p className="mt-4 text-sm text-ink/60">Nothing here yet.</p>}
            <ul className="mt-3 space-y-2">
                {rows?.map((row, i) => (
                    <li key={row.id} className="flex flex-wrap items-center gap-3 rounded-lg border border-ink/10 bg-white p-3">
                        {config.reorderable !== false && <div className="flex flex-col">
                            <button type="button" aria-label="Move up" disabled={i === 0} onClick={() => void move(i, -1)} className="px-1 text-xs disabled:opacity-30">▲</button>
                            <button type="button" aria-label="Move down" disabled={i === rows.length - 1} onClick={() => void move(i, 1)} className="px-1 text-xs disabled:opacity-30">▼</button>
                        </div>}
                        <div className="min-w-0 flex-1 text-sm">{config.summary(row)}</div>
                        <div className="flex flex-wrap gap-2">
                            {config.quickActions?.(row).map((a) => (
                                <Button key={a.label} variant="ghost" onClick={() => void quick(row, a.body)}>{a.label}</Button>
                            ))}
                            <Button variant="ghost" onClick={() => { setNotice(''); setEditing(row); }}>Edit</Button>
                            <Button variant="danger" onClick={() => void remove(row)}>Delete</Button>
                        </div>
                    </li>
                ))}
            </ul>

            {editing && (
                <EditModal
                    config={config}
                    row={editing === 'new' ? null : editing}
                    onClose={() => setEditing(null)}
                    onSaved={() => { setEditing(null); setNotice(`${config.singular} saved.`); load(); }}
                />
            )}
        </>
    );
}

function EditModal({ config, row, onClose, onSaved }: { config: ResourceConfig; row: Row | null; onClose: () => void; onSaved: () => void }) {
    const [values, setValues] = useState<Values>(() => initialValues(config, row));
    const [removeImage, setRemoveImage] = useState<Record<string, boolean>>({});
    const [lookups, setLookups] = useState<Record<string, { value: string; label: string }[]>>({});
    const [errors, setErrors] = useState<ApiError | null>(null);
    const [error, setError] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        for (const f of config.fields) {
            if (!f.lookup) continue;
            api<{ id: number; name: string }[]>(f.lookup)
                .then((r) => setLookups((l) => ({ ...l, [f.name]: r.data.map((o) => ({ value: String(o.id), label: o.name })) })))
                .catch(() => undefined);
        }
    }, [config.fields]);

    const set = (name: string, value: string | boolean | File | null) => setValues((v) => ({ ...v, [name]: value }));

    async function submit(e: FormEvent) {
        e.preventDefault();
        setBusy(true);
        setError('');
        setErrors(null);

        // Always multipart so image uploads work; updates use POST + _method=PUT.
        const form = new FormData();
        for (const f of config.fields) {
            const v = values[f.name];
            if (f.type === 'image') {
                if (v instanceof File) form.append(f.name, v);
                else if (removeImage[f.name]) form.append(`remove_${f.name}`, '1');
            } else if (f.type === 'checkbox') form.append(f.name, v ? '1' : '0');
            else if (f.type === 'select' && f.lookup && v === '' && !row) continue;
            else form.append(f.name, String(v ?? ''));
        }
        if (row) form.append('_method', 'PUT');

        try {
            await api(row ? `${config.path}/${row.id}` : config.path, { method: 'POST', body: form });
            onSaved();
        } catch (err) {
            if (err instanceof ApiError && err.status === 422) setErrors(err);
            else setError(errorMessage(err));
            setBusy(false);
        }
    }

    return (
        <Modal title={`${row ? 'Edit' : 'Add'} ${config.singular}`} onClose={onClose}>
            <form onSubmit={submit} className="space-y-4">
                <Alert>{error || (errors ? 'Please fix the highlighted fields.' : '')}</Alert>
                {config.fields.map((f) => {
                    const err = errors?.field(f.name);
                    if (f.type === 'checkbox') {
                        return (
                            <label key={f.name} className="flex items-center gap-2 text-sm">
                                <input type="checkbox" checked={Boolean(values[f.name])} onChange={(e) => set(f.name, e.target.checked)} className="h-4 w-4 accent-brand" />
                                {f.label}
                            </label>
                        );
                    }
                    if (f.type === 'image') {
                        const current = row ? mediaUrl(row[f.column ?? 'image_path']) : null;
                        const file = values[f.name] instanceof File ? (values[f.name] as File) : null;
                        return (
                            <Field key={f.name} label={f.label} error={err} hint={f.hint ?? 'JPG, PNG or WebP, up to 8 MB. Replaces the current image.'}>
                                <div>
                                    {current && !removeImage[f.name] && !file && <img src={current} alt="" className="mb-2 h-28 rounded object-cover" />}
                                    {file && <p className="mb-2 text-xs text-ink/70">New file: {file.name}</p>}
                                    <input type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => { set(f.name, e.target.files?.[0] ?? null); setRemoveImage({ ...removeImage, [f.name]: false }); }} className="text-sm" />
                                    {current && !f.required && (
                                        <label className="mt-2 flex items-center gap-2 text-xs"><input type="checkbox" checked={Boolean(removeImage[f.name])} onChange={(e) => setRemoveImage({ ...removeImage, [f.name]: e.target.checked })} /> Remove current image</label>
                                    )}
                                </div>
                            </Field>
                        );
                    }
                    return (
                        <Field key={f.name} label={f.label} error={err} hint={f.hint}>
                            {f.type === 'textarea' ? (
                                <Textarea value={String(values[f.name] ?? '')} required={f.required} onChange={(e) => set(f.name, e.target.value)} />
                            ) : f.type === 'select' ? (
                                <Select value={String(values[f.name] ?? '')} required={f.required} onChange={(e) => set(f.name, e.target.value)}>
                                    {!f.required && <option value="">None</option>}
                                    {(f.options ?? lookups[f.name] ?? []).map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                                </Select>
                            ) : (
                                <Input type={f.type === 'number' ? 'number' : 'text'} value={String(values[f.name] ?? '')} required={f.required} onChange={(e) => set(f.name, e.target.value)} />
                            )}
                        </Field>
                    );
                })}
                <div className="flex justify-end gap-2">
                    <Button variant="ghost" onClick={onClose}>Cancel</Button>
                    <Button type="submit" disabled={busy}>{busy ? 'Saving…' : 'Save'}</Button>
                </div>
            </form>
        </Modal>
    );
}


