import { useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { telLink, whatsappLink } from '@/shared/site';
import { useSetting, useSite } from '@/shared/siteData';
import Icon from './Icon';

const allLinks: { to: string; label: string; end?: boolean; blog?: boolean }[] = [
    { to: '/', label: 'nav.home', end: true },
    { to: '/services', label: 'nav.services' },
    { to: '/about-us', label: 'nav.about' },
    { to: '/how-it-works', label: 'nav.howItWorks' },
    { to: '/gallery', label: 'nav.gallery' },
    { to: '/faq', label: 'nav.faq' },
    { to: '/blog', label: 'nav.blog', blog: true },
    { to: '/contact', label: 'nav.contact' },
];

export function Logo({ onClick, onBrand = false }: { onClick?: () => void; onBrand?: boolean }) {
    const { settings } = useSite();
    const name = settings.company_name || t('brand.name');
    const [failed, setFailed] = useState(false);
    // Admin/site-data logo first; otherwise the file you keep in the project at public/images/site/logo-header.png.
    const logoSrc = settings.logo_header || settings.logo || '/images/site/logo-header.png';

    return (
        <Link to="/" className="flex items-center gap-2 leading-tight" onClick={onClick} aria-label={name}>
            {!failed ? (
                <span className="rounded-md bg-white px-2 py-1 shadow-sm">
                    <img src={logoSrc} alt={name} className="h-10 w-auto max-w-[220px] object-contain sm:h-11" height={44} onError={() => setFailed(true)} />
                </span>
            ) : (
                <>
                    <span className="flex flex-col">
                        <span className={`text-xl font-extrabold uppercase tracking-wide ${onBrand ? 'text-ink' : 'text-white'}`}>
                            {name.split(' ')[0]} <span className={onBrand ? 'text-white' : 'text-brand'}>{name.split(' ').slice(1).join(' ')}</span>
                        </span>
                        {settings.tagline && <span className={`text-[11px] font-medium ${onBrand ? 'text-ink/80' : 'text-white/70'}`}>{settings.tagline}</span>}
                    </span>
                </>
            )}
        </Link>
    );
}

export default function Header() {
    const [open, setOpen] = useState(false);
    const { blog_posts } = useSite();
    // The Blog link appears only once at least one article is published.
    const links = allLinks.filter((l) => !l.blog || (blog_posts?.length ?? 0) > 0);
    const phone = useSetting('phone');
    const whatsapp = whatsappLink(useSetting('whatsapp_number'), useSetting('whatsapp_default_message', t('whatsapp.defaultMessage')));
    const tel = telLink(phone);

    const linkClass = ({ isActive }: { isActive: boolean }) =>
        `text-sm font-semibold transition-colors hover:text-white ${isActive ? 'text-white underline decoration-2 underline-offset-8' : 'text-ink'}`;

    return (
        <header className="sticky top-0 z-40 bg-brand text-ink shadow-sm">
            <div className="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <Logo onBrand onClick={() => setOpen(false)} />

                <nav className="hidden items-center gap-6 lg:flex" aria-label="Main">
                    {links.map((l) => (
                        <NavLink key={l.to} to={l.to} end={l.end} className={linkClass}>
                            {t(l.label)}
                        </NavLink>
                    ))}
                </nav>

                <div className="flex items-center gap-3">
                    {phone && (
                        <div className="hidden items-center gap-2 text-right text-xs leading-tight xl:flex">
                            <Icon name="phone" className="h-5 w-5 text-ink" />
                            <div>
                                <span className="block text-ink/80">{t('cta.callUs')}</span>
                                {tel ? (
                                    <a href={tel} className="font-semibold hover:text-white">{phone}</a>
                                ) : (
                                    <span className="font-semibold">{phone}</span>
                                )}
                            </div>
                        </div>
                    )}
                    <Link to="/#quote" className="rounded-md bg-ink px-3 py-2 text-sm font-semibold text-white hover:bg-black sm:px-4" onClick={() => setOpen(false)}>
                        {t('cta.quoteShort')}
                    </Link>
                    <button
                        type="button"
                        className="rounded-md border border-ink/40 p-2 text-ink lg:hidden"
                        aria-expanded={open}
                        aria-controls="mobile-nav"
                        aria-label={open ? t('nav.close') : t('nav.menu')}
                        onClick={() => setOpen((v) => !v)}
                    >
                        <Icon name={open ? 'x' : 'menu'} />
                    </button>
                </div>
            </div>

            {open && (
                <nav id="mobile-nav" className="border-t border-ink/15 px-4 pb-4 lg:hidden" aria-label="Mobile">
                    <ul className="flex flex-col pt-2">
                        {links.map((l) => (
                            <li key={l.to}>
                                <NavLink to={l.to} end={l.end} className={(s) => `block py-3 ${linkClass(s)}`} onClick={() => setOpen(false)}>
                                    {t(l.label)}
                                </NavLink>
                            </li>
                        ))}
                    </ul>
                    <div className="mt-2 grid grid-cols-2 gap-2">
                        {tel ? (
                            <a href={tel} className="flex items-center justify-center gap-2 rounded-md border border-ink/40 py-3 text-sm font-semibold">
                                <Icon name="phone" className="h-4 w-4" /> {t('cta.callUsShort')}
                            </a>
                        ) : (
                            <Link to="/contact" className="flex items-center justify-center gap-2 rounded-md border border-ink/40 py-3 text-sm font-semibold" onClick={() => setOpen(false)}>
                                <Icon name="phone" className="h-4 w-4" /> {t('nav.contact')}
                            </Link>
                        )}
                        {whatsapp ? (
                            <a href={whatsapp} target="_blank" rel="noopener noreferrer" className="flex items-center justify-center gap-2 rounded-md bg-[#25D366] py-3 text-sm font-semibold text-ink">
                                <Icon name="chat" className="h-4 w-4" /> WhatsApp
                            </a>
                        ) : (
                            <Link to="/#quote" className="flex items-center justify-center gap-2 rounded-md bg-ink py-3 text-sm font-semibold text-white" onClick={() => setOpen(false)}>
                                {t('cta.quoteShort')}
                            </Link>
                        )}
                    </div>
                </nav>
            )}
        </header>
    );
}
