import { useState } from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../auth';

const links = [
    ['/', 'Dashboard'],
    ['/quotes', 'Enquiries'],
    ['/messages', 'Messages'],
    ['/services', 'Services'],
    ['/service-categories', 'Service categories'],
    ['/testimonials', 'Testimonials'],
    ['/gallery', 'Gallery'],
    ['/faqs', 'FAQs'],
    ['/blog', 'Blog'],
    ['/content', 'Website content & images'],
    ['/settings', 'Settings'],
] as const;

export default function Shell() {
    const { user, logout } = useAuth();
    const [open, setOpen] = useState(false);

    return (
        <div className="min-h-screen bg-ink/5 lg:flex">
            <header className="flex items-center justify-between bg-ink px-4 py-3 text-white lg:hidden">
                <span className="font-bold">Crystal Movers Admin</span>
                <button type="button" onClick={() => setOpen(!open)} aria-expanded={open} className="rounded border border-white/30 px-3 py-1 text-sm">Menu</button>
            </header>
            <aside className={`${open ? 'block' : 'hidden'} bg-ink text-white lg:block lg:min-h-screen lg:w-64 lg:shrink-0`}>
                <div className="hidden px-5 py-5 text-lg font-bold lg:block">Crystal <span className="text-brand">Movers</span></div>
                <nav className="flex flex-col px-3 pb-4" aria-label="Admin">
                    {links.map(([to, label]) => (
                        <NavLink key={to} to={to} end={to === '/'} onClick={() => setOpen(false)}
                            className={({ isActive }) => `rounded-md px-3 py-2 text-sm font-medium ${isActive ? 'bg-brand text-white' : 'text-white/80 hover:bg-white/10'}`}>
                            {label}
                        </NavLink>
                    ))}
                    <a href="/" target="_blank" rel="noreferrer" className="mt-3 px-3 py-2 text-sm text-white/70 hover:text-white">View website ↗</a>
                    <div className="mt-3 border-t border-white/15 px-3 pt-3 text-xs text-white/60">{user?.email}</div>
                    <button type="button" onClick={() => void logout()} className="mt-2 rounded-md px-3 py-2 text-left text-sm font-medium text-white/80 hover:bg-white/10">Sign out</button>
                </nav>
            </aside>
            <main className="min-w-0 flex-1 p-4 lg:p-8">
                <Outlet />
            </main>
        </div>
    );
}
