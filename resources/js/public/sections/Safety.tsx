import { t } from '@/shared/i18n';
import { useContent, useSetting } from '@/shared/siteData';
import Icon from '../components/Icon';
import Img from '../components/Img';
import { ButtonLink, Container } from '../components/ui';

const points = ['safety.careful', 'safety.protective', 'safety.scope', 'safety.communication', 'safety.installation', 'safety.coverage'];

/**
 * Safety and Care. The optional highlighted line is the admin-editable "insurance_text" setting (empty by default).
 * Do not add an insurance, guarantee or compensation claim unless the business has verified terms.
 */
export default function Safety() {
    const heading = useSetting('insurance_heading', 'Safety and Care');
    const claim = useSetting('insurance_text');
    const text = useContent('safety.text');
    const image = useContent('home.safety.image');
    const imageAlt = useContent('home.safety.image_alt');

    return (
        <section className="bg-brand-soft/60 py-14 lg:py-20" aria-labelledby="safety-title">
            <Container className="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div className="flex flex-col gap-6 sm:flex-row">
                    <span className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-brand text-white shadow-lg">
                        <Icon name="shield" className="h-12 w-12" filled />
                    </span>
                    <div>
                        <p className="text-sm font-semibold text-brand-dark">{t('safety.eyebrow')}</p>
                        <h2 id="safety-title" className="mt-1 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">
                            {heading}
                        </h2>
                        {claim && <p className="mt-3 text-lg font-semibold text-brand-dark">{claim}</p>}
                        {text && <p className="mt-3 text-ink/70">{text}</p>}

                        <ul className="mt-5 grid gap-2 sm:grid-cols-2">
                            {points.map((point) => (
                                <li key={point} className="flex items-center gap-2 text-sm font-medium text-ink">
                                    <Icon name="check" className="h-5 w-5 shrink-0 text-brand-dark" />
                                    {t(point)}
                                </li>
                            ))}
                        </ul>

                        <ButtonLink to="/#quote" arrow className="mt-6">
                            {t('cta.getQuote')}
                        </ButtonLink>
                    </div>
                </div>

                {image && (
                    <div className="overflow-hidden rounded-lg shadow-lg">
                        <Img src={image} alt={imageAlt} sizes="(min-width: 1024px) 50vw, 100vw" className="h-full w-full object-cover" width={800} height={533} />
                    </div>
                )}
            </Container>
        </section>
    );
}
