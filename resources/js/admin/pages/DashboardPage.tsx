import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api, errorMessage } from '@/shared/api';
import { Alert, Badge, Card, formatDate, PageHeader, Spinner } from '../components/ui';
import type { Message, Quote } from '../types';

interface Dashboard {
    counts: Record<string, number>;
    recent_quotes: Quote[];
    recent_messages: Message[];
    upcoming_moves: Quote[];
}

const tiles: [string, string, string][] = [
    ['new_quote_requests', 'New enquiries', '/quotes'],
    ['quote_requests', 'All enquiries', '/quotes'],
    ['new_messages', 'New messages', '/messages'],
    ['upcoming_moves', 'Upcoming appointments', '/quotes'],
    ['pending_testimonials', 'Testimonials awaiting approval', '/testimonials'],
    ['gallery_images', 'Gallery images', '/gallery'],
];

export default function DashboardPage() {
    const [data, setData] = useState<Dashboard | null>(null);
    const [error, setError] = useState('');

    useEffect(() => {
        api<Dashboard>('/admin/dashboard').then((r) => setData(r.data)).catch((e) => setError(errorMessage(e)));
    }, []);

    if (error) return <Alert>{error}</Alert>;
    if (!data) return <Spinner />;

    return (
        <>
            <PageHeader title="Dashboard" />
            <div className="grid grid-cols-2 gap-3 lg:grid-cols-3">
                {tiles.map(([key, label, to]) => (
                    <Link key={key} to={to} className="rounded-lg border border-ink/10 bg-white p-4 shadow-sm hover:border-brand">
                        <div className="text-3xl font-extrabold text-brand-dark">{data.counts[key] ?? 0}</div>
                        <div className="mt-1 text-sm text-ink/70">{label}</div>
                    </Link>
                ))}
            </div>

            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                <Card>
                    <h2 className="mb-3 font-bold">Latest enquiries</h2>
                    {data.recent_quotes.length === 0 && <p className="text-sm text-ink/60">No requests yet.</p>}
                    <ul className="divide-y divide-ink/10">
                        {data.recent_quotes.map((q) => (
                            <li key={q.id} className="flex items-center justify-between gap-2 py-2 text-sm">
                                <Link to={`/quotes/${q.id}`} className="font-medium text-brand-dark hover:underline">{q.reference} · {q.name}</Link>
                                <Badge value={q.status} label={q.status_label} />
                            </li>
                        ))}
                    </ul>
                </Card>
                <Card>
                    <h2 className="mb-3 font-bold">Upcoming dates</h2>
                    {data.upcoming_moves.length === 0 && <p className="text-sm text-ink/60">No upcoming appointments.</p>}
                    <ul className="divide-y divide-ink/10">
                        {data.upcoming_moves.map((q) => (
                            <li key={q.id} className="flex items-center justify-between gap-2 py-2 text-sm">
                                <Link to={`/quotes/${q.id}`} className="font-medium text-brand-dark hover:underline">{q.name} · {q.move_type_label}</Link>
                                <span className="shrink-0 text-ink/70">{formatDate(q.preferred_date)}</span>
                            </li>
                        ))}
                    </ul>
                </Card>
                <Card className="lg:col-span-2">
                    <h2 className="mb-3 font-bold">Latest messages</h2>
                    {data.recent_messages.length === 0 && <p className="text-sm text-ink/60">No messages yet.</p>}
                    <ul className="divide-y divide-ink/10">
                        {data.recent_messages.map((m) => (
                            <li key={m.id} className="py-2 text-sm">
                                <Link to="/messages" className="font-medium text-brand-dark hover:underline">{m.name}</Link>
                                <span className="ml-2 text-ink/70">{m.subject ?? m.message.slice(0, 80)}</span>
                                <span className="ml-2 text-xs text-ink/50">{formatDate(m.created_at, true)}</span>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>
        </>
    );
}
