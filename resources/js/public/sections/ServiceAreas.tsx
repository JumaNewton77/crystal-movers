import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import { Container, SectionHeading } from '../components/ui';
import { locationPath } from '../routes';

/** Where enquiries are received. Crystal Movers is based in Nairobi: no branch or office is claimed for any other town. */
export default function ServiceAreas() {
    const { locations } = useSite();

    if (locations.length === 0) return null;

    return (
        <section className="bg-brand-soft/50 py-14 lg:py-20" aria-labelledby="areas-title">
            <Container>
                <div id="areas-title">
                    <SectionHeading eyebrow={t('sections.areasEyebrow')} title={t('sections.areasTitle')} intro={t('sections.areasIntro')} />
                </div>
                <ul className="mt-6 flex flex-wrap gap-2">
                    {locations.map((l) => (
                        <li key={l.slug}>
                            <Link to={locationPath(l.slug)} className="inline-block rounded-full border border-brand/40 bg-white px-4 py-2 text-sm font-semibold text-brand-dark hover:bg-brand-soft">
                                {l.name}
                            </Link>
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}
