import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import { ButtonLink, Container } from '../components/ui';
import { locationPath, servicePath } from '../routes';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';

/**
 * /movers-in-{city}. Useful for people searching in that town, and truthful: Crystal Movers is based in
 * Nairobi and serves customers across Kenya. No page claims an office or branch in the town.
 */
export default function LocationPage({ name }: { name: string }) {
    const { services, locations } = useSite();
    const active = services.filter((s) => s.status === 'active' && s.category === 'moving');
    const isNairobi = name === 'Nairobi';
    const others = locations.filter((l) => l.name !== name);

    usePageMeta(
        `Movers in ${name}`,
        `Looking for movers in ${name}? Crystal Movers handles home and office moves. Tell us about your move and request a quote.`,
    );

    return (
        <>
            <section className="bg-ink py-12 text-white lg:py-16">
                <Container>
                    <p className="flex items-center gap-2 text-sm font-semibold text-brand">
                        <Icon name="pin" className="h-4 w-4" /> {t('sections.coverageTitle')}
                    </p>
                    <h1 className="mt-3 text-4xl font-extrabold tracking-tight sm:text-5xl">Movers in {name}</h1>
                    <p className="mt-4 max-w-2xl text-lg text-white/85">
                        {isNairobi
                            ? 'Crystal Movers is based in Nairobi and provides home moving and office relocation services for homes and businesses in the city.'
                            : `Moving to, from or within ${name}? Tell us about your move and we will confirm whether we can help and prepare a quotation.`}
                    </p>
                    <div className="mt-7">
                        <ButtonLink to="/#quote" arrow>
                            {t('cta.getQuote')}
                        </ButtonLink>
                    </div>
                </Container>
            </section>

            <Container className="max-w-4xl py-10">
                <h2 className="text-2xl font-extrabold text-ink">Moving services for {name}</h2>
                <ul className="mt-4 grid gap-3 sm:grid-cols-2">
                    {active.map((service) => (
                        <li key={service.slug} className="rounded-lg border border-ink/10 p-4">
                            <Link to={servicePath(service.slug)} className="font-bold text-brand-dark hover:underline">
                                {service.title}
                            </Link>
                            <p className="mt-1 text-sm text-ink/70">{service.short_description}</p>
                        </li>
                    ))}
                </ul>

                <h2 className="mt-10 text-2xl font-extrabold text-ink">How a move in {name} works</h2>
                <p className="mt-3 text-ink/80">
                    Send us your quote request with the pickup and delivery locations and your preferred date. You can also add photos of your belongings to help us understand
                    the move. Our team reviews your requirements and contacts you with a quotation. There is no obligation to proceed.
                </p>

                <h2 className="mt-10 text-2xl font-extrabold text-ink">Other areas we serve</h2>
                <p className="mt-3 text-ink/80">{t('footer.coverage')} {t('sections.coverageText')}</p>
                <ul className="mt-3 flex flex-wrap gap-2">
                    {others.map((l) => (
                        <li key={l.slug}>
                            <Link to={locationPath(l.slug)} className="inline-block rounded-full border border-brand/40 px-3 py-1 text-sm font-medium text-brand-dark hover:bg-brand-soft">
                                {l.name}
                            </Link>
                        </li>
                    ))}
                    <li className="px-1 py-1 text-sm text-ink/60">{t('sections.coverageOther')}</li>
                </ul>
            </Container>

            <CtaSection />
        </>
    );
}
