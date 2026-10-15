import { t } from '@/shared/i18n';
import { useContent, useSite } from '@/shared/siteData';
import CategoryCard from '../components/CategoryCard';
import { ButtonLink, Container, SectionHeading } from '../components/ui';

/**
 * Service categories with a few services each. Moving is introduced separately from the installation and repair
 * services so that not everything reads as "moving". Active services only; "coming soon" has its own section.
 */
export default function ServicesSection() {
    const { service_categories, services } = useSite();
    const movingTitle = useContent('home.moving.title');
    const movingText = useContent('home.moving.text');
    const installTitle = useContent('home.install.title');
    const installText = useContent('home.install.text');
    const active = services.filter((s) => s.status === 'active');
    const categories = service_categories.filter((c) => active.some((s) => s.category === c.slug));
    const moving = categories.filter((c) => c.slug === 'moving');
    const technical = categories.filter((c) => c.slug !== 'moving');

    if (categories.length === 0) return null;

    const grid = (list: typeof categories) => (
        <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {list.map((category) => (
                <CategoryCard key={category.slug} category={category} services={active.filter((s) => s.category === category.slug)} />
            ))}
        </div>
    );

    // The installation and repair categories glide right to left in a single line (paused on hover/focus).
    // The repeated copy is hidden from screen readers and not focusable, so each card is reachable once.
    const row = (list: typeof categories) => (
        <div className="cm-marquee mt-6" style={{ ['--cm-marquee-duration' as string]: `${list.length * 7}s` }}>
            <ul className="cm-marquee-track">
                {[false, true].flatMap((copy) =>
                    list.map((category) => (
                        <li key={`${copy ? 'copy-' : ''}${category.slug}`} className="mr-4 w-72 shrink-0 sm:w-80" aria-hidden={copy || undefined} inert={copy || undefined}>
                            <CategoryCard category={category} services={active.filter((s) => s.category === category.slug)} />
                        </li>
                    )),
                )}
            </ul>
        </div>
    );

    return (
        <section className="bg-white py-14 lg:py-20" aria-labelledby="services-title">
            <Container>
                <div className="flex flex-wrap items-end justify-between gap-6">
                    <div id="services-title">
                        <SectionHeading eyebrow={t('sections.servicesEyebrow')} title={t('sections.servicesTitle')} intro={t('sections.servicesIntro')} />
                    </div>
                    <ButtonLink to="/services" arrow>
                        {t('cta.viewAll')}
                    </ButtonLink>
                </div>

                {moving.length > 0 && (
                    <div className="mt-10">
                        <h3 className="text-xl font-extrabold text-ink">{movingTitle || 'Moving Services for Homes, Offices and Businesses'}</h3>
                        {movingText && <p className="mt-2 max-w-3xl text-ink/75">{movingText}</p>}
                        {grid(moving)}
                    </div>
                )}

                {technical.length > 0 && (
                    <div className="mt-12">
                        <h3 className="text-xl font-extrabold text-ink">{installTitle || 'Installation, Repairs and Home Services'}</h3>
                        {installText && <p className="mt-2 max-w-3xl text-ink/75">{installText}</p>}
                        {technical.length >= 3 ? row(technical) : grid(technical)}
                    </div>
                )}
            </Container>
        </section>
    );
}
