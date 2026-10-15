import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Icon from '../components/Icon';
import Img from '../components/Img';
import { Container, SectionHeading } from '../components/ui';

/** Six reasons, edited in Admin > Website Content (keys why.1.title ... why.6.text). No statistics. */
export default function WhyChoose() {
    const { content } = useSite();
    const items = [1, 2, 3, 4, 5, 6]
        .map((n) => ({ title: content[`why.${n}.title`], text: content[`why.${n}.text`] }))
        .filter((item) => item.title);
    const image = content['home.why.image'];

    if (items.length === 0) return null;

    return (
        <section className="bg-white py-14 lg:py-20" aria-labelledby="why-title">
            <Container className={`grid gap-10 ${image ? 'lg:grid-cols-2 lg:items-center' : ''}`}>
                {image && (
                    <div className="overflow-hidden rounded-lg shadow-lg">
                        <Img src={image} alt={content['home.why.image_alt'] || ''} sizes="(min-width: 1024px) 50vw, 100vw" className="h-full w-full object-cover" width={800} height={500} />
                    </div>
                )}
                <div>
                    <div id="why-title">
                        <SectionHeading title={t('sections.whyTitle')} />
                    </div>
                    <ul className="mt-8 space-y-5">
                        {items.map((item) => (
                            <li key={item.title} className="flex gap-4">
                                <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand text-white">
                                    <Icon name="check" className="h-5 w-5" />
                                </span>
                                <div>
                                    <h3 className="font-bold text-ink">{item.title}</h3>
                                    {item.text && <p className="text-sm text-ink/70">{item.text}</p>}
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            </Container>
        </section>
    );
}
