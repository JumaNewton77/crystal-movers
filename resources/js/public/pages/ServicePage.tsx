import { Link, useParams } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import Img from '../components/Img';
import QuoteForm from '../components/QuoteForm';
import { serviceIcon } from '../components/ServiceCard';
import { ButtonLink, ComingSoonBadge, Container } from '../components/ui';
import { categoryPath, servicePath } from '../routes';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';
import NotFoundPage from './NotFoundPage';

export default function ServicePage() {
    const { slug } = useParams();
    const { services, service_categories } = useSite();
    const service = services.find((s) => s.slug === slug);

    usePageMeta(service?.seo_title || service?.title || 'Service', service?.seo_description || service?.short_description);

    if (!service) return <NotFoundPage />;

    const comingSoon = service.status === 'coming_soon';
    const category = service_categories.find((c) => c.slug === service.category);
    const related = services.filter((s) => s.category === service.category && s.slug !== service.slug && s.status === 'active');

    return (
        <>
            <section className="relative isolate overflow-hidden bg-ink text-white">
                <Img src={service.image} alt={service.image_alt} priority className="absolute inset-0 -z-20 h-full w-full object-cover opacity-40" />
                <div className="absolute inset-0 -z-10 bg-gradient-to-r from-ink via-ink/85 to-ink/40" aria-hidden="true" />
                <Container className="py-14 lg:py-20">
                    <nav aria-label="Breadcrumb" className="text-sm text-white/70">
                        <Link to="/" className="hover:text-brand">{t('nav.home')}</Link> / <Link to="/services" className="hover:text-brand">{t('nav.services')}</Link>
                        {category && (
                            <>
                                {' '}/ <Link to={categoryPath(category.slug)} className="hover:text-brand">{category.name}</Link>
                            </>
                        )}
                    </nav>
                    <h1 className="mt-3 flex max-w-2xl items-center gap-3 text-4xl font-extrabold tracking-tight sm:text-5xl">
                        {!service.image && (
                            <span className="hidden h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand text-ink sm:flex">
                                <Icon name={serviceIcon(service.icon, service.category)} />
                            </span>
                        )}
                        {service.title}
                    </h1>
                    {comingSoon && (
                        <p className="mt-4">
                            <ComingSoonBadge label={t('cta.comingSoon')} />
                        </p>
                    )}
                    <p className="mt-4 max-w-2xl text-lg text-white/90">{comingSoon ? t('servicePage.comingSoonNotice') : service.short_description}</p>
                    {service.price_text && !comingSoon && <p className="mt-3 text-lg font-semibold text-brand">{service.price_text}</p>}
                    <div className="mt-7 flex flex-wrap gap-3">
                        <ButtonLink to={comingSoon ? '/contact' : '#request'} arrow>
                            {comingSoon ? t('cta.registerInterest') : t('cta.getQuote')}
                        </ButtonLink>
                    </div>
                </Container>
            </section>

            <section className="py-12 lg:py-16">
                <Container className="grid gap-10 lg:grid-cols-[1.3fr_1fr]">
                    <div>
                        {!comingSoon && service.description && <p className="whitespace-pre-line text-lg text-ink/80">{service.description}</p>}

                        {service.benefits.length > 0 && (
                            <>
                                <h2 className="mt-8 text-2xl font-extrabold text-ink">{t('servicePage.benefits')}</h2>
                                <ul className="mt-4 space-y-3">
                                    {service.benefits.map((benefit) => (
                                        <li key={benefit} className="flex gap-3">
                                            <span className="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand text-ink">
                                                <Icon name="check" className="h-4 w-4" />
                                            </span>
                                            <span className="text-ink/80">{benefit}</span>
                                        </li>
                                    ))}
                                </ul>
                            </>
                        )}

                        {related.length > 0 && (
                            <aside className="mt-10 rounded-lg border border-ink/10 p-5">
                                <h2 className="text-lg font-bold text-ink">{t('servicePage.other')}</h2>
                                <ul className="mt-3 space-y-2">
                                    {related.map((s) => (
                                        <li key={s.slug}>
                                            <Link to={servicePath(s.slug)} className="font-medium text-brand-dark hover:underline">
                                                {s.title}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </aside>
                        )}
                    </div>

                    {!comingSoon && (
                        <div id="request" className="scroll-mt-24 lg:sticky lg:top-24 lg:self-start">
                            <div className="rounded-xl ring-1 ring-ink/10">
                                <QuoteForm compact idPrefix="service" defaultServiceSlug={service.slug} />
                            </div>
                        </div>
                    )}
                </Container>
            </section>

            <CtaSection />
        </>
    );
}
