import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { telLink } from '@/shared/site';
import { useSite } from '@/shared/siteData';
import { Logo } from './Header';
import { locationPath } from '../routes';

const socials: [string, string][] = [
    ['social_facebook', 'Facebook'],
    ['social_instagram', 'Instagram'],
    ['social_x', 'X'],
    ['social_linkedin', 'LinkedIn'],
    ['social_youtube', 'YouTube'],
    ['social_tiktok', 'TikTok'],
];

export default function Footer() {
    const { settings, locations, blog_posts } = useSite();
    const tel = telLink(settings.phone);
    const activeSocials = socials.filter(([key]) => settings[key]);

    return (
        <footer className="bg-ink text-white">
            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                <div>
                    <Logo />
                    <p className="mt-3 text-sm font-semibold text-white">{settings.tagline || t('footer.tagline')}</p>
                    <p className="text-sm text-white/70">{t('footer.supporting')}</p>
                    <p className="mt-2 text-sm text-white/70">{t('footer.coverage')}</p>
                </div>

                <nav aria-label="Footer">
                    <p className="text-sm font-semibold">{t('footer.quickLinks')}</p>
                    <ul className="mt-3 grid grid-cols-2 gap-2 text-sm text-white/70">
                        <li><Link className="hover:text-brand" to="/">{t('nav.home')}</Link></li>
                        <li><Link className="hover:text-brand" to="/services">{t('nav.services')}</Link></li>
                        <li><Link className="hover:text-brand" to="/about-us">{t('nav.about')}</Link></li>
                        <li><Link className="hover:text-brand" to="/how-it-works">{t('nav.howItWorks')}</Link></li>
                        <li><Link className="hover:text-brand" to="/gallery">{t('nav.gallery')}</Link></li>
                        <li><Link className="hover:text-brand" to="/faq">{t('nav.faq')}</Link></li>
                        {(blog_posts?.length ?? 0) > 0 && <li><Link className="hover:text-brand" to="/blog">{t('nav.blog')}</Link></li>}
                        <li><Link className="hover:text-brand" to="/contact">{t('nav.contact')}</Link></li>
                    </ul>
                </nav>

                <nav aria-label="Service areas">
                    <p className="text-sm font-semibold">{t('sections.coverageTitle')}</p>
                    <ul className="mt-3 space-y-2 text-sm text-white/70">
                        {locations.map((l) => (
                            <li key={l.slug}>
                                <Link className="hover:text-brand" to={locationPath(l.slug)}>Movers in {l.name}</Link>
                            </li>
                        ))}
                    </ul>
                </nav>

                <div>
                    <p className="text-sm font-semibold">{t('footer.contact')}</p>
                    <ul className="mt-3 space-y-2 text-sm text-white/70">
                        {settings.phone && <li>{tel ? <a className="hover:text-brand" href={tel}>{settings.phone}</a> : settings.phone}</li>}
                        {settings.email && <li><a className="hover:text-brand" href={`mailto:${settings.email}`}>{settings.email}</a></li>}
                        {settings.address && <li>{settings.address}</li>}
                        {settings.working_hours && <li>{settings.working_hours}</li>}
                    </ul>
                    {activeSocials.length > 0 && (
                        <>
                            <p className="mt-5 text-sm font-semibold">{t('footer.followUs')}</p>
                            <ul className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-white/70">
                                {activeSocials.map(([key, label]) => (
                                    <li key={key}>
                                        <a className="hover:text-brand" href={settings[key] as string} target="_blank" rel="noopener noreferrer">{label}</a>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </div>
            </div>
            <div className="border-t border-white/10 py-4 text-center text-xs text-white/60">
                © {new Date().getFullYear()} {settings.company_name || t('brand.name')}. {t('footer.rights')}
            </div>
        </footer>
    );
}
