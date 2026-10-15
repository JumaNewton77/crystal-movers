import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import type { ServiceItem } from '@/shared/siteData';
import { servicePath } from '../routes';
import Icon, { type IconName } from './Icon';
import Img from './Img';
import MediaPlaceholder from './MediaPlaceholder';
import { ComingSoonBadge } from './ui';

const FALLBACK_BY_CATEGORY: Record<string, IconName> = {
    moving: 'truck',
    'cctv-security': 'camera',
    'internet-networking': 'wifi',
    'tv-satellite': 'tv',
    'washing-machine': 'wrench',
    lighting: 'bulb',
    shower: 'droplet',
    'furniture-assembly': 'bed',
};

const KNOWN = new Set<string>(['truck', 'home', 'building', 'box', 'camera', 'wifi', 'tv', 'wrench', 'bulb', 'droplet', 'bed', 'shield']);

/** Admin-chosen icon first, then a sensible one for the category, then a generic tool icon. */
export function serviceIcon(icon: string | null | undefined, category?: string | null): IconName {
    if (icon && KNOWN.has(icon)) return icon as IconName;
    return (category && FALLBACK_BY_CATEGORY[category]) || 'wrench';
}

export default function ServiceCard({ service }: { service: ServiceItem }) {
    const comingSoon = service.status === 'coming_soon';
    const icon = serviceIcon(service.icon, service.category);

    return (
        <article className="flex flex-col overflow-hidden rounded-lg border border-ink/10 bg-white shadow-sm transition-shadow hover:shadow-md">
            <div className="relative aspect-[16/7] bg-ink/5">
                {service.image ? (
                    <Img src={service.image} alt={service.image_alt} sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" className="h-full w-full object-cover" width={640} height={400} />
                ) : (
                    <MediaPlaceholder icon={icon} />
                )}
                <span className="absolute -bottom-5 left-4 flex h-11 w-11 items-center justify-center rounded-full bg-brand text-ink shadow-md">
                    <Icon name={icon} className="h-5 w-5" />
                </span>
            </div>
            <div className="flex flex-1 flex-col p-4 pt-7">
                <h3 className="flex flex-wrap items-center gap-2 text-lg font-bold text-ink">
                    {service.title}
                    {comingSoon && <ComingSoonBadge label={t('cta.comingSoon')} />}
                </h3>
                <p className="mt-1 flex-1 text-sm leading-snug text-ink/70">{service.short_description}</p>
                {service.price_text && <p className="mt-2 text-sm font-semibold text-ink">{service.price_text}</p>}
                <Link to={servicePath(service.slug)} className="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-dark hover:underline">
                    {comingSoon ? t('cta.comingSoon') : t('cta.learnMore')}
                    <Icon name="arrow" className="h-4 w-4" />
                </Link>
            </div>
        </article>
    );
}
