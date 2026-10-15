import { Link, useParams } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import Img from '../components/Img';
import { serviceIcon } from '../components/ServiceCard';
import ServiceCard from '../components/ServiceCard';
import { Container } from '../components/ui';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';
import NotFoundPage from './NotFoundPage';

/** Every service in one category, e.g. /services/category/cctv-security. */
export default function CategoryPage() {
    const { slug } = useParams();
    const { services, service_categories } = useSite();
    const category = service_categories.find((c) => c.slug === slug);
    const items = services.filter((s) => s.category === slug && s.status === 'active');

    usePageMeta(category ? `${category.name} in Kenya` : 'Services', category?.description ?? undefined);

    if (!category) return <NotFoundPage />;

    return (
        <>
            <section className="relative isolate overflow-hidden bg-ink text-white">
                <Img src={category.image} alt={category.image_alt} priority className="absolute inset-0 -z-20 h-full w-full object-cover opacity-40" />
                <div className="absolute inset-0 -z-10 bg-gradient-to-r from-ink via-ink/85 to-ink/40" aria-hidden="true" />
                <Container className="py-12 lg:py-16">
                    <nav aria-label="Breadcrumb" className="text-sm text-white/70">
                        <Link to="/" className="hover:text-brand">{t('nav.home')}</Link> / <Link to="/services" className="hover:text-brand">{t('nav.services')}</Link>
                    </nav>
                    <h1 className="mt-3 flex items-center gap-3 text-4xl font-extrabold tracking-tight sm:text-5xl">
                        <span className="hidden h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand text-ink sm:flex">
                            <Icon name={serviceIcon(category.icon, category.slug)} />
                        </span>
                        {category.name}
                    </h1>
                    {category.description && <p className="mt-4 max-w-2xl text-lg text-white/85">{category.description}</p>}
                </Container>
            </section>

            <section className="py-10 lg:py-12">
                <Container>
                    {items.length === 0 ? (
                        <p className="text-ink/70">Services in this category will be listed here soon.</p>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {items.map((service) => (
                                <ServiceCard key={service.slug} service={service} />
                            ))}
                        </div>
                    )}
                </Container>
            </section>

            <CtaSection />
        </>
    );
}
