import { useCallback, useEffect, useState } from 'react';
import { api, errorMessage } from '@/shared/api';
import { Alert, Badge, Button, Card, formatDate, PageHeader, Select, Spinner, Textarea } from '../components/ui';
import type { Message } from '../types';

export default function MessagesPage() {
    const [items, setItems] = useState<Message[] | null>(null);
    const [filter, setFilter] = useState('');
    const [open, setOpen] = useState<number | null>(null);
    const [notes, setNotes] = useState<Record<number, string>>({});
    const [error, setError] = useState('');

    const load = useCallback(() => {
        api<Message[]>(`/admin/messages${filter ? `?status=${filter}` : ''}`).then((r) => { setItems(r.data); setError(''); }).catch((e) => setError(errorMessage(e)));
    }, [filter]);
    useEffect(load, [load]);

    async function patch(m: Message, body: Record<string, unknown>) {
        try {
            const r = await api<Message>(`/admin/messages/${m.id}`, { method: 'PATCH', body });
            setItems((list) => list?.map((x) => (x.id === m.id ? r.data : x)) ?? null);
        } catch (e) { setError(errorMessage(e)); }
    }

    async function remove(m: Message) {
        if (!window.confirm(`Delete the message from ${m.name}? This cannot be undone.`)) return;
        try {
            await api(`/admin/messages/${m.id}`, { method: 'DELETE' });
            setItems((list) => list?.filter((x) => x.id !== m.id) ?? null);
        } catch (e) { setError(errorMessage(e)); }
    }

    function toggle(m: Message) {
        const next = open === m.id ? null : m.id;
        setOpen(next);
        if (next !== null && m.status === 'new') void patch(m, { status: 'read' });
    }

    return (
        <>
            <PageHeader title="Contact messages">
                <Select value={filter} onChange={(e) => setFilter(e.target.value)} aria-label="Filter">
                    <option value="">Inbox (not archived)</option>
                    <option value="new">New</option>
                    <option value="read">Read</option>
                    <option value="replied">Replied</option>
                    <option value="archived">Archived</option>
                </Select>
            </PageHeader>
            <Alert>{error}</Alert>
            {!items && !error && <Spinner />}
            {items?.length === 0 && <p className="text-sm text-ink/60">No messages.</p>}
            <div className="space-y-3">
                {items?.map((m) => (
                    <Card key={m.id}>
                        <button type="button" onClick={() => toggle(m)} aria-expanded={open === m.id} className="flex w-full flex-wrap items-center justify-between gap-2 text-left">
                            <span><span className="font-semibold">{m.name}</span> <span className="text-sm text-ink/60">· {m.subject ?? 'No subject'}</span></span>
                            <span className="flex items-center gap-2 text-xs text-ink/60">{formatDate(m.created_at, true)} <Badge value={m.status} /></span>
                        </button>
                        {open === m.id && (
                            <div className="mt-3 border-t border-ink/10 pt-3 text-sm">
                                <p className="whitespace-pre-wrap">{m.message}</p>
                                <p className="mt-3 text-ink/70">
                                    <a className="text-brand-dark hover:underline" href={`mailto:${m.email}`}>{m.email}</a>
                                    {m.phone && <> · <a className="text-brand-dark hover:underline" href={`tel:${m.phone}`}>{m.phone}</a></>}
                                </p>
                                <div className="mt-3">
                                    <Textarea rows={2} placeholder="Internal notes" aria-label="Internal notes" value={notes[m.id] ?? m.admin_notes ?? ''} onChange={(e) => setNotes({ ...notes, [m.id]: e.target.value })} />
                                    <Button variant="ghost" className="mt-2" onClick={() => void patch(m, { admin_notes: notes[m.id] ?? m.admin_notes ?? '' })}>Save notes</Button>
                                </div>
                                <div className="mt-3 flex flex-wrap gap-2">
                                    <Button variant="ghost" onClick={() => void patch(m, { status: 'replied' })}>Mark replied</Button>
                                    <Button variant="ghost" onClick={() => void patch(m, { status: 'new' })}>Mark unread</Button>
                                    <Button variant="ghost" onClick={() => void patch(m, { status: m.status === 'archived' ? 'read' : 'archived' })}>{m.status === 'archived' ? 'Unarchive' : 'Archive'}</Button>
                                    <Button variant="danger" onClick={() => void remove(m)}>Delete</Button>
                                </div>
                            </div>
                        )}
                    </Card>
                ))}
            </div>
        </>
    );
}
