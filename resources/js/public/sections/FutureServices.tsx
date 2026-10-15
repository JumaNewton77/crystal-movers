import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import { serviceIcon } from '../components/ServiceCard';
import { ComingSoonBadge, Container, SectionHeading } from '../components/ui';
import { servicePath } from '../routes';

/** International Moving: display only in Phase 1. No booking, no forms. */
export default function FutureServices() {
    const upcoming = useSite().services.filter((s) => s.status === 'coming_soon');

    if (upcoming.length === 0) return null;

    return (
        <section className="bg-white py-14 lg:py-20" aria-labelledby="future-title">
            <Container>
                <div id="future-title">
                    <SectionHeading eyebrow={t('sections.futureEyebrow')} title={t('sections.futureTitle')} intro={t('sections.futureIntro')} />
                </div>
                <div className="mt-8 grid gap-4 md:grid-cols-2">
                    {upcoming.map((service) => (
                        <article key={service.slug} className="flex gap-4 rounded-lg border border-dashed border-brand/50 bg-brand-soft/30 p-5">
                            <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white text-brand-dark">
                                <Icon name={serviceIcon(service.icon, service.category)} className="h-6 w-6" />
                            </span>
                            <div>
                                <h3 className="flex flex-wrap items-center gap-2 text-lg font-bold text-ink">
                                    {service.title}
                                    <ComingSoonBadge label={t('cta.comingSoon')} />
                                </h3>
                                <p className="mt-1 text-sm text-ink/70">{service.short_description}</p>
                                <Link to={servicePath(service.slug)} className="mt-3 inline-block text-sm font-semibold text-brand-dark hover:underline">
                                    {t('cta.registerInterest')} →
                                </Link>
                            </div>
                        </article>
                    ))}
                </div>
            </Container>
        </section>
    );
}
