import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import type { ServiceCategoryItem, ServiceItem } from '@/shared/siteData';
import { categoryPath, servicePath } from '../routes';
import Icon from './Icon';
import Img from './Img';
import MediaPlaceholder from './MediaPlaceholder';
import { serviceIcon } from './ServiceCard';

/** Homepage card for one service category, listing a few of its services. Links to the grouped Services page. */
export default function CategoryCard({ category, services }: { category: ServiceCategoryItem; services: ServiceItem[] }) {
    const icon = serviceIcon(category.icon, category.slug);
    const shown = services.slice(0, 4);

    return (
        <article className="flex flex-col overflow-hidden rounded-lg border border-ink/10 bg-white shadow-sm transition-shadow hover:shadow-md">
            <div className="relative aspect-[16/7] bg-ink/5">
                {category.image ? (
                    <Img src={category.image} alt={category.image_alt} sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" className="h-full w-full object-cover" width={640} height={400} />
                ) : (
                    <MediaPlaceholder icon={icon} />
                )}
                <span className="absolute -bottom-5 left-4 flex h-11 w-11 items-center justify-center rounded-full bg-brand text-ink shadow-md">
                    <Icon name={icon} className="h-5 w-5" />
                </span>
            </div>
            <div className="flex flex-1 flex-col p-4 pt-7">
                <h3 className="text-lg font-bold text-ink">{category.name}</h3>
                {category.description && <p className="mt-2 text-sm text-ink/70">{category.description}</p>}
                <ul className="mt-2 flex-1 space-y-0.5 text-sm">
                    {shown.map((s) => (
                        <li key={s.slug}>
                            <Link to={servicePath(s.slug)} className="text-ink/80 hover:text-brand-dark hover:underline">
                                {s.title}
                            </Link>
                        </li>
                    ))}
                </ul>
                <Link to={categoryPath(category.slug)} className="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-dark hover:underline">
                    {t('cta.viewCategory')}
                    <Icon name="arrow" className="h-4 w-4" />
                </Link>
            </div>
        </article>
    );
}
