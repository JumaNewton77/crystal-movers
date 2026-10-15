import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import { Container, SectionHeading } from '../components/ui';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';
import { Accordion } from '../sections/FaqSection';

export default function FaqPage() {
    const { faqs } = useSite();
    usePageMeta('FAQs: Moving, Installations & Booking');

    return (
        <>
            <section className="py-12 lg:py-16">
                <Container className="max-w-4xl">
                    <h1 className="sr-only">{t('nav.faq')}</h1>
                    <SectionHeading eyebrow={t('sections.faqEyebrow')} title={t('sections.faqTitle')} center />
                    <div className="mt-8">{faqs.length > 0 && <Accordion items={faqs} />}</div>
                </Container>
            </section>
            <CtaSection />
        </>
    );
}
