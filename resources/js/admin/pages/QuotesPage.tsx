import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api, errorMessage } from '@/shared/api';
import { Alert, Badge, Button, formatDate, Input, PageHeader, Select, Spinner } from '../components/ui';
import type { Quote } from '../types';

interface Option { value: string; label: string }
interface Lookup { id: number; name: string; category_id?: number | null }
interface Page { items: Quote[]; meta: { current_page: number; last_page: number; total: number } }

export default function QuotesPage() {
    const [statuses, setStatuses] = useState<Option[]>([]);
    const [status, setStatus] = useState('');
    const [categories, setCategories] = useState<Lookup[]>([]);
    const [serviceList, setServiceList] = useState<Lookup[]>([]);
    const [categoryId, setCategoryId] = useState('');
    const [serviceId, setServiceId] = useState('');
    const [search, setSearch] = useState('');
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState('created_at');
    const [direction, setDirection] = useState('desc');
    const [page, setPage] = useState(1);
    const [data, setData] = useState<Page | null>(null);
    const [error, setError] = useState('');

    useEffect(() => {
        api<{ statuses: Option[] }>('/admin/lookups/quote-options').then((r) => setStatuses(r.data.statuses)).catch(() => undefined);
        api<Lookup[]>('/admin/lookups/service-categories').then((r) => setCategories(r.data)).catch(() => undefined);
        api<Lookup[]>('/admin/lookups/services').then((r) => setServiceList(r.data)).catch(() => undefined);
    }, []);

    const load = useCallback(() => {
        const params = new URLSearchParams({ page: String(page), sort, direction });
        if (status) params.set('status', status);
        if (query) params.set('search', query);
        if (categoryId) params.set('category_id', categoryId);
        if (serviceId) params.set('service_id', serviceId);
        api<Page>(`/admin/quotes?${params}`).then((r) => { setData(r.data); setError(''); }).catch((e) => setError(errorMessage(e)));
    }, [page, sort, direction, status, query, categoryId, serviceId]);

    useEffect(load, [load]);

    function toggleSort(column: string) {
        if (sort === column) setDirection(direction === 'asc' ? 'desc' : 'asc');
        else { setSort(column); setDirection('desc'); }
        setPage(1);
    }
    const arrow = (c: string) => (sort === c ? (direction === 'asc' ? ' ▲' : ' ▼') : '');

    return (
        <>
            <PageHeader title="Enquiries" />
            <form className="mb-4 flex flex-wrap gap-2" onSubmit={(e) => { e.preventDefault(); setPage(1); setQuery(search.trim()); }}>
                <Input className="max-w-xs" placeholder="Search name, phone, reference, location…" value={search} onChange={(e) => setSearch(e.target.value)} aria-label="Search" />
                <Select className="max-w-[12rem]" value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }} aria-label="Filter by status">
                    <option value="">All statuses</option>
                    {statuses.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                </Select>
                <Select className="max-w-[14rem]" value={categoryId} onChange={(e) => { setCategoryId(e.target.value); setServiceId(''); setPage(1); }} aria-label="Filter by category">
                    <option value="">All categories</option>
                    {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                </Select>
                <Select className="max-w-[16rem]" value={serviceId} onChange={(e) => { setServiceId(e.target.value); setPage(1); }} aria-label="Filter by service">
                    <option value="">All services</option>
                    {serviceList.filter((s) => !categoryId || String(s.category_id) === categoryId).map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                </Select>
                <Button type="submit">Search</Button>
            </form>
            <Alert>{error}</Alert>
            {!data && !error && <Spinner />}
            {data && (
                <div className="overflow-x-auto rounded-lg border border-ink/10 bg-white">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-ink text-white">
                            <tr>
                                <th className="px-3 py-2">Reference</th>
                                <th className="px-3 py-2">Customer</th>
                                <th className="px-3 py-2">Service</th>
                                <th className="px-3 py-2"><button type="button" onClick={() => toggleSort('preferred_date')}>Date{arrow('preferred_date')}</button></th>
                                <th className="px-3 py-2"><button type="button" onClick={() => toggleSort('status')}>Status{arrow('status')}</button></th>
                                <th className="px-3 py-2"><button type="button" onClick={() => toggleSort('created_at')}>Received{arrow('created_at')}</button></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-ink/10">
                            {data.items.length === 0 && <tr><td colSpan={6} className="px-3 py-6 text-center text-ink/60">No enquiries found.</td></tr>}
                            {data.items.map((q) => (
                                <tr key={q.id} className="hover:bg-brand-soft/50">
                                    <td className="px-3 py-2 font-medium"><Link to={`/quotes/${q.id}`} className="text-brand-dark hover:underline">{q.reference}</Link></td>
                                    <td className="px-3 py-2">{q.name}<div className="text-xs text-ink/60">{q.phone}</div></td>
                                    <td className="px-3 py-2">{q.move_type_label}<div className="text-xs text-ink/60">{q.category?.name ? `${q.category.name} · ` : ''}{q.from_location && q.to_location ? `${q.from_location} → ${q.to_location}` : q.location ?? ''}{q.attachments_count ? ` · ${q.attachments_count} file(s)` : ''}</div></td>
                                    <td className="px-3 py-2">{formatDate(q.preferred_date)}</td>
                                    <td className="px-3 py-2"><Badge value={q.status} label={q.status_label} /></td>
                                    <td className="px-3 py-2">{formatDate(q.created_at, true)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            {data && data.meta.last_page > 1 && (
                <div className="mt-4 flex items-center gap-3 text-sm">
                    <Button variant="ghost" disabled={page <= 1} onClick={() => setPage(page - 1)}>Previous</Button>
                    <span>Page {data.meta.current_page} of {data.meta.last_page} ({data.meta.total} total)</span>
                    <Button variant="ghost" disabled={page >= data.meta.last_page} onClick={() => setPage(page + 1)}>Next</Button>
                </div>
            )}
        </>
    );
}
