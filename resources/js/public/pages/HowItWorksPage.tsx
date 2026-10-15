import { t } from '@/shared/i18n';
import { Container, SectionHeading } from '../components/ui';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';
import HowItWorks from '../sections/HowItWorks';

export default function HowItWorksPage() {
    usePageMeta('How It Works: Simple Steps to Get Your Job Done');

    return (
        <>
            <section className="pt-12 lg:pt-16">
                <Container>
                    <h1 className="sr-only">{t('nav.howItWorks')}</h1>
                    <SectionHeading eyebrow={t('sections.howEyebrow')} title={t('sections.howTitle')} intro={t('sections.howIntro')} />
                </Container>
            </section>
            <HowItWorks withHeading={false} />
            <CtaSection />
        </>
    );
}
