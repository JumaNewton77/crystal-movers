import { t } from '@/shared/i18n';
import { telLink } from '@/shared/site';
import { useContent, useSetting } from '@/shared/siteData';
import Icon from '../components/Icon';
import Img from '../components/Img';
import QuoteForm from '../components/QuoteForm';
import { ButtonLink, Container } from '../components/ui';

/** Highlights the company name inside the (admin-editable) headline. */
function Headline({ text }: { text: string }) {
    const parts = text.split(/(Crystal Movers)/);
    return (
        <>
            {parts.map((part, i) =>
                part === 'Crystal Movers' ? (
                    <span key={i} className="text-brand-dark">
                        {part}
                    </span>
                ) : (
                    part
                ),
            )}
        </>
    );
}

export default function Hero() {
    const eyebrow = useContent('home.hero.eyebrow');
    const title = useContent('home.hero.title', 'Professional Moving and Installation Services You Can Rely On');
    const text = useContent('home.hero.text');
    const imageAlt = useContent('home.hero.image_alt');
    const image = useContent('home.hero.image');
    const phone = useSetting('phone');
    const tel = telLink(phone);

    return (
        <section className="relative isolate overflow-hidden bg-white text-ink">
            <Img src={image} alt={imageAlt} priority className="absolute inset-0 -z-20 h-full w-full object-cover" />
            <div className="absolute inset-0 -z-10 bg-gradient-to-r from-white/75 via-white/50 to-transparent" aria-hidden="true" />

            <Container className="grid gap-8 py-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-12 lg:py-16">
                <div>
                    {eyebrow && (
                        <p className="flex items-center gap-2 text-sm font-semibold text-brand-dark">
                            <span className="h-0.5 w-8 bg-brand" aria-hidden="true" />
                            {eyebrow}
                        </p>
                    )}
                    <h1 className="mt-4 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        <Headline text={title} />
                    </h1>
                    {text && <p className="mt-4 max-w-xl text-lg text-ink/80">{text}</p>}

                    <div className="mt-7 flex flex-wrap gap-3">
                        <ButtonLink to="/#quote" arrow>
                            {t('cta.getQuote')}
                        </ButtonLink>
                        <ButtonLink to="/services" variant="outlineDark">
                            {t('cta.explore')}
                        </ButtonLink>
                    </div>

                    {tel && (
                        <a href={tel} className="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-ink hover:text-brand-dark lg:hidden">
                            <Icon name="phone" className="h-5 w-5 text-brand" />
                            {t('cta.callUsShort')}: {phone}
                        </a>
                    )}
                </div>

                <div id="quote" className="scroll-mt-24">
                    <div className="rounded-xl ring-1 ring-ink/10"><QuoteForm compact idPrefix="hero" /></div>
                </div>
            </Container>
        </section>
    );
}
