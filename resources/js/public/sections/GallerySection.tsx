import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Img from '../components/Img';
import { ButtonLink, Container, SectionHeading } from '../components/ui';

export default function GallerySection() {
    const items = useSite().gallery.slice(0, 6);

    if (items.length === 0) return null;

    return (
        <section className="bg-white py-14 lg:py-20" aria-labelledby="gallery-title">
            <Container>
                <div className="flex flex-wrap items-end justify-between gap-6">
                    <div id="gallery-title">
                        <SectionHeading eyebrow={t('sections.galleryEyebrow')} title={t('sections.galleryTitle')} />
                    </div>
                    <ButtonLink to="/gallery" variant="outlineDark" arrow>
                        {t('cta.viewGallery')}
                    </ButtonLink>
                </div>
                <ul className="mt-10 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4">
                    {items.map((item) => (
                        <li key={item.id} className="group relative aspect-[4/3] overflow-hidden rounded-lg bg-ink/5">
                            <Img src={item.image} alt={item.alt} sizes="(min-width: 768px) 33vw, 50vw" className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" width={640} height={480} />
                            <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/80 to-transparent px-3 pb-2 pt-8 text-sm font-semibold text-white">{item.title}</span>
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}
