import { useState } from 'react';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Img from '../components/Img';
import { Container, SectionHeading } from '../components/ui';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';

export default function GalleryPage() {
    const { gallery, gallery_categories } = useSite();
    const [category, setCategory] = useState<string>('all');

    usePageMeta('Gallery: Our Work');

    // Only show filter tabs for categories that actually contain published photos.
    const categories = gallery_categories.filter((c) => gallery.some((g) => g.category === c.slug));
    const items = category === 'all' ? gallery : gallery.filter((g) => g.category === category);

    const tab = (active: boolean) =>
        `rounded-full px-4 py-2 text-sm font-semibold transition-colors ${active ? 'bg-brand text-white' : 'border border-ink/20 text-ink hover:border-brand hover:text-brand-dark'}`;

    return (
        <>
            <section className="py-12 lg:py-16">
                <Container>
                    <h1 className="sr-only">{t('nav.gallery')}</h1>
                    <SectionHeading eyebrow={t('sections.galleryEyebrow')} title={t('sections.galleryTitle')} />

                    {gallery.length === 0 ? (
                        <p className="mt-8 text-ink/70">{t('sections.galleryEmpty')}</p>
                    ) : (
                        <>
                            {categories.length > 1 && (
                                <div className="mt-6 flex flex-wrap gap-2" role="tablist" aria-label="Gallery categories">
                                    <button type="button" role="tab" aria-selected={category === 'all'} className={tab(category === 'all')} onClick={() => setCategory('all')}>
                                        {t('gallery.all')}
                                    </button>
                                    {categories.map((c) => (
                                        <button key={c.slug} type="button" role="tab" aria-selected={category === c.slug} className={tab(category === c.slug)} onClick={() => setCategory(c.slug)}>
                                            {c.name}
                                        </button>
                                    ))}
                                </div>
                            )}

                            <ul className="mt-8 grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4">
                                {items.map((item) => (
                                    <li key={item.id} className="group relative aspect-[4/3] overflow-hidden rounded-lg bg-ink/5">
                                        <Img src={item.image} alt={item.alt} sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw" className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" width={640} height={480} />
                                        <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/80 to-transparent px-3 pb-2 pt-8 text-sm font-semibold text-white">{item.title}</span>
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}
                </Container>
            </section>
            <CtaSection />
        </>
    );
}
