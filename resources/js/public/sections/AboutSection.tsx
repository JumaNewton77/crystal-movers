import { t } from '@/shared/i18n';
import { useContent } from '@/shared/siteData';
import Img from '../components/Img';
import Icon from '../components/Icon';
import { ButtonLink, Container, SectionHeading } from '../components/ui';

/** Short About block for the homepage. Text comes from Admin > Website content (about.*). The full page stays at /about-us. */
export default function AboutSection() {
    const summary = useContent('about.summary');
    const who = useContent('about.who_we_are');
    const mission = useContent('about.mission');
    const values = (useContent('about.commitment') || useContent('about.values')).split(/\r?\n/).map((v) => v.trim()).filter(Boolean);

    const image = useContent('home.about.image') || useContent('about.image');
    const imageAlt = useContent('home.about.image_alt') || useContent('about.image_alt');

    if (!summary && !who) return null;

    return (
        <section className="bg-white py-14 lg:py-20" aria-labelledby="about-title">
            <Container className="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <div id="about-title">
                        <SectionHeading eyebrow={t('nav.about')} title={t('about.heading')} />
                    </div>
                    {who && <p className="mt-4 whitespace-pre-line text-ink/80">{who}</p>}
                    {mission && (
                        <p className="mt-4 text-ink/80">
                            <strong className="text-ink">{t('about.mission')}: </strong>
                            {mission}
                        </p>
                    )}
                    {image && values.length > 0 && <h3 className="mt-5 font-bold text-ink">{t('about.commitment')}</h3>}
                    {image && values.length > 0 && (
                        <ul className="mt-5 grid gap-2">
                            {values.map((value) => (
                                <li key={value} className="flex items-center gap-2 text-sm font-medium text-ink">
                                    <Icon name="check" className="h-5 w-5 shrink-0 text-brand-dark" />
                                    {value}
                                </li>
                            ))}
                        </ul>
                    )}
                    <div className="mt-6">
                        <ButtonLink to="/about-us" arrow>
                            {t('cta.learnMore')}
                        </ButtonLink>
                    </div>
                </div>
                {image ? (
                    <div className="mx-auto w-full max-w-md overflow-hidden rounded-lg shadow-lg lg:order-first">
                        <Img src={image} alt={imageAlt} sizes="(min-width: 1024px) 28rem, 100vw" className="h-full w-full object-cover" width={780} height={731} />
                    </div>
                ) : values.length > 0 ? (
                    <ul className="grid gap-3">
                        {values.map((value) => (
                            <li key={value} className="flex items-center gap-3 rounded-lg bg-brand-soft/60 px-4 py-3 font-medium text-ink">
                                <Icon name="check" className="h-5 w-5 shrink-0 text-brand-dark" />
                                {value}
                            </li>
                        ))}
                    </ul>
                ) : null}
            </Container>
        </section>
    );
}

