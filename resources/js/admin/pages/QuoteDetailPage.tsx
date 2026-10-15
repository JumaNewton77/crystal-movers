import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api, ApiError, errorMessage } from '@/shared/api';
import { Alert, Badge, Button, Card, Field, formatDate, Select, Spinner, Textarea } from '../components/ui';
import type { Quote } from '../types';

interface Option { value: string; label: string }

export default function QuoteDetailPage() {
    const { id } = useParams();
    const [quote, setQuote] = useState<Quote | null>(null);
    const [statuses, setStatuses] = useState<Option[]>([]);
    const [status, setStatus] = useState('');
    const [statusNote, setStatusNote] = useState('');
    const [notes, setNotes] = useState('');
    const [error, setError] = useState('');
    const [saved, setSaved] = useState('');
    const [busy, setBusy] = useState(false);

    function apply(q: Quote) {
        setQuote(q);
        setStatus(q.status);
        setNotes(q.admin_notes ?? '');
    }

    useEffect(() => {
        api<Quote>(`/admin/quotes/${id}`).then((r) => apply(r.data)).catch((e) => setError(errorMessage(e)));
        api<{ statuses: Option[] }>('/admin/lookups/quote-options').then((r) => setStatuses(r.data.statuses)).catch(() => undefined);
    }, [id]);

    async function save(body: Record<string, unknown>, message: string) {
        setBusy(true);
        setError('');
        setSaved('');
        try {
            const r = await api<Quote>(`/admin/quotes/${id}`, { method: 'PATCH', body });
            apply(r.data);
            setStatusNote('');
            setSaved(message);
        } catch (e) {
            setError(e instanceof ApiError ? Object.values(e.errors).flat()[0] ?? e.message : errorMessage(e));
        } finally {
            setBusy(false);
        }
    }

    if (!quote) return error ? <Alert>{error}</Alert> : <Spinner />;

    const rows: [string, string][] = [
        ['Customer', quote.name],
        ['Phone', quote.phone],
        ['Email', quote.email ?? '—'],
        ['Service', quote.move_type_label],
        ['Category', quote.category?.name ?? '—'],
        ...(quote.from_location || quote.to_location ? ([['From', quote.from_location ?? '—'], ['To', quote.to_location ?? '—']] as [string, string][]) : []),
        ...(quote.location ? ([['Location', quote.location]] as [string, string][]) : []),
        ['Preferred date', `${formatDate(quote.preferred_date)}${quote.preferred_time ? `, ${quote.preferred_time}` : ''}`],
        ...Object.entries(quote.details ?? {}).map(([k, v]) => [k.replace(/_/g, ' ').replace(/^./, (c) => c.toUpperCase()), String(v)] as [string, string]),
        ['Agreed to be contacted', quote.contact_consent ? 'Yes' : 'Not recorded'],
        ['Received', formatDate(quote.created_at, true)],
    ];

    return (
        <>
            <Link to="/quotes" className="text-sm text-brand-dark hover:underline">← All enquiries</Link>
            <div className="mb-5 mt-2 flex flex-wrap items-center gap-3">
                <h1 className="text-2xl font-bold">{quote.reference}</h1>
                <Badge value={quote.status} label={quote.status_label} />
            </div>
            <Alert>{error}</Alert>
            <Alert kind="success">{saved}</Alert>

            <div className="mt-4 grid gap-4 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <dl className="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                        {rows.map(([k, v]) => (
                            <div key={k}><dt className="text-xs font-semibold uppercase text-ink/60">{k}</dt><dd className="text-sm">{v}</dd></div>
                        ))}
                    </dl>
                    <h2 className="mt-5 text-xs font-semibold uppercase text-ink/60">Customer description</h2>
                    <p className="mt-1 whitespace-pre-wrap text-sm">{quote.message || '—'}</p>

                    <h2 className="mt-5 text-xs font-semibold uppercase text-ink/60">Photos and videos ({quote.attachments?.length ?? 0})</h2>
                    {quote.attachments?.length === 0 && <p className="mt-1 text-sm text-ink/60">No files uploaded.</p>}
                    <ul className="mt-2 grid gap-3 sm:grid-cols-2">
                        {quote.attachments?.map((a) => (
                            <li key={a.id} className="rounded-md border border-ink/10 p-2 text-sm">
                                {a.is_image ? (
                                    <a href={a.url} target="_blank" rel="noreferrer"><img src={a.url} alt={a.original_name} loading="lazy" className="h-36 w-full rounded object-cover" /></a>
                                ) : (
                                    <video src={a.url} controls preload="none" className="h-36 w-full rounded bg-ink" />
                                )}
                                <a href={a.url} target="_blank" rel="noreferrer" className="mt-1 block truncate font-medium text-brand-dark hover:underline">{a.original_name}</a>
                                <span className="text-xs text-ink/60">{(a.size_bytes / 1024 / 1024).toFixed(1)} MB</span>
                            </li>
                        ))}
                    </ul>
                </Card>

                <div className="space-y-4">
                    <Card>
                        <h2 className="mb-3 font-bold">Status</h2>
                        <Field label="Change status">
                            <Select value={status} onChange={(e) => setStatus(e.target.value)}>
                                {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                            </Select>
                        </Field>
                        <div className="mt-3"><Field label="Note (optional)"><Textarea rows={2} maxLength={500} value={statusNote} onChange={(e) => setStatusNote(e.target.value)} /></Field></div>
                        <Button className="mt-3" disabled={busy || status === quote.status} onClick={() => void save({ status, status_note: statusNote || null }, 'Status updated.')}>Update status</Button>
                    </Card>
                    <Card>
                        <h2 className="mb-3 font-bold">Internal notes</h2>
                        <p className="mb-2 text-xs text-ink/60">Only visible to admins. Never shown to the customer.</p>
                        <Textarea rows={5} maxLength={5000} value={notes} onChange={(e) => setNotes(e.target.value)} aria-label="Internal notes" />
                        <Button className="mt-3" disabled={busy || notes === (quote.admin_notes ?? '')} onClick={() => void save({ admin_notes: notes }, 'Notes saved.')}>Save notes</Button>
                    </Card>
                    <Card>
                        <h2 className="mb-3 font-bold">History</h2>
                        <ul className="space-y-2 text-sm">
                            {quote.history?.map((h, i) => (
                                <li key={i} className="border-l-2 border-brand pl-3">
                                    <div className="font-medium">{h.from ? `${h.from} → ${h.to}` : h.to}</div>
                                    <div className="text-xs text-ink/60">{formatDate(h.created_at, true)}{h.by ? ` · ${h.by}` : ''}</div>
                                    {h.note && <div className="text-xs">{h.note}</div>}
                                </li>
                            ))}
                        </ul>
                    </Card>
                </div>
            </div>
        </>
    );
}
