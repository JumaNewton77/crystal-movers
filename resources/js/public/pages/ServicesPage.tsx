import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import { serviceIcon } from '../components/ServiceCard';
import ServiceCard from '../components/ServiceCard';
import { Container, SectionHeading } from '../components/ui';
import { categoryPath, servicePath } from '../routes';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';

/** One compact card per category, listing its services. "Learn more" opens the category page with every service. */
export default function ServicesPage() {
    const { services, service_categories } = useSite();
    const active = services.filter((s) => s.status === 'active');
    const upcoming = services.filter((s) => s.status === 'coming_soon');

    usePageMeta('Our Services: Moving, Installations & Repairs', t('sections.servicesIntro'));

    return (
        <>
            <section className="pt-12 lg:pt-16">
                <Container>
                    <h1 className="sr-only">{t('nav.services')}</h1>
                    <SectionHeading eyebrow={t('sections.servicesEyebrow')} title={t('sections.servicesTitle')} intro={t('sections.servicesIntro')} />
                </Container>
            </section>

            <section className="py-10 lg:py-12">
                <Container>
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {service_categories.map((category) => {
                            const items = active.filter((s) => s.category === category.slug);
                            if (items.length === 0) return null;
                            const shown = items.slice(0, 3);
                            return (
                                <article key={category.slug} id={category.slug} className="flex flex-col rounded-lg border border-ink/10 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                                    <h2 className="flex items-center gap-3 text-xl font-extrabold text-ink">
                                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand text-ink">
                                            <Icon name={serviceIcon(category.icon, category.slug)} />
                                        </span>
                                        {category.name}
                                    </h2>
                                    <ul className="mt-4 flex-1 space-y-3">
                                        {shown.map((s) => (
                                            <li key={s.slug}>
                                                <Link to={servicePath(s.slug)} className="font-semibold text-ink hover:text-brand-dark">{s.title}</Link>
                                                <p className="text-sm leading-snug text-ink/70">{s.short_description}</p>
                                            </li>
                                        ))}
                                    </ul>
                                    <Link to={categoryPath(category.slug)} className="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-dark hover:underline">
                                        {t('cta.learnMore')}
                                        <Icon name="arrow" className="h-4 w-4" />
                                    </Link>
                                </article>
                            );
                        })}
                    </div>
                </Container>
            </section>

            {upcoming.length > 0 && (
                <section className="pb-10 lg:pb-12" aria-labelledby="coming-soon-title">
                    <Container>
                        <h2 id="coming-soon-title" className="text-2xl font-extrabold text-ink">
                            {t('sections.futureTitle')}
                        </h2>
                        <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {upcoming.map((service) => (
                                <ServiceCard key={service.slug} service={service} />
                            ))}
                        </div>
                    </Container>
                </section>
            )}

            <CtaSection />
        </>
    );
}
