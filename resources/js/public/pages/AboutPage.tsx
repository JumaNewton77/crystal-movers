import { t } from '@/shared/i18n';
import { useContent, useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import Img from '../components/Img';
import { Container } from '../components/ui';
import { locationPath } from '../routes';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';
import { Link } from 'react-router-dom';

function Block({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <section className="border-t border-ink/10 py-8 first:border-t-0">
            <h2 className="text-2xl font-extrabold text-ink">{title}</h2>
            <div className="mt-3 text-ink/80">{children}</div>
        </section>
    );
}

/** All text is editable in Admin > Website Content (keys about.*). Nothing here is invented company history. */
export default function AboutPage() {
    const { locations } = useSite();
    const summary = useContent('about.summary');
    const who = useContent('about.who_we_are');
    const mission = useContent('about.mission');
    const vision = useContent('about.vision');
    const values = (useContent('about.commitment') || useContent('about.values')).split(/\r?\n/).map((v) => v.trim()).filter(Boolean);
    const why = useContent('about.why_we_move_people');
    const coverage = useContent('about.service_coverage');
    const image = useContent('about.image');
    const imageAlt = useContent('about.image_alt');

    usePageMeta('About Us', summary || undefined);

    return (
        <>
            <section className="bg-white py-12 lg:py-16">
                <Container>
                    <h1 className="text-4xl font-extrabold tracking-tight text-ink sm:text-5xl">{t('about.heading')}</h1>
                    {summary && <p className="mt-4 max-w-2xl text-lg text-ink/70">{summary}</p>}
                </Container>
            </section>

            <Container className="max-w-3xl py-10">
                {image && (
                    <div className="mb-6 overflow-hidden rounded-lg">
                        <Img src={image} alt={imageAlt} sizes="(min-width: 768px) 768px, 100vw" className="h-auto w-full object-cover" />
                    </div>
                )}
                {who && <Block title={t('about.who')}><p className="whitespace-pre-line">{who}</p></Block>}
                {mission && <Block title={t('about.mission')}><p>{mission}</p></Block>}
                {vision && <Block title={t('about.vision')}><p>{vision}</p></Block>}
                {values.length > 0 && (
                    <Block title={t('about.commitment')}>
                        <ul className="grid gap-3">
                            {values.map((value) => (
                                <li key={value} className="flex items-center gap-3 rounded-lg bg-brand-soft/60 px-4 py-3 font-medium text-ink">
                                    <Icon name="check" className="h-5 w-5 shrink-0 text-brand-dark" />
                                    {value}
                                </li>
                            ))}
                        </ul>
                    </Block>
                )}
                {why && <Block title={t('about.why')}><p className="whitespace-pre-line">{why}</p></Block>}
                <Block title={t('about.coverage')}>
                    {coverage && <p>{coverage}</p>}
                    <ul className="mt-3 flex flex-wrap gap-2">
                        {locations.map((l) => (
                            <li key={l.slug}>
                                <Link to={locationPath(l.slug)} className="inline-block rounded-full border border-brand/40 px-3 py-1 text-sm font-medium text-brand-dark hover:bg-brand-soft">
                                    {l.name}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </Block>
            </Container>

            <CtaSection />
        </>
    );
}
