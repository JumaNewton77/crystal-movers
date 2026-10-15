import { t } from '@/shared/i18n';
import ContactForm from '../components/ContactForm';
import ContactInfo from '../components/ContactInfo';
import { ButtonLink, Container, SectionHeading } from '../components/ui';

/** Homepage contact block. The quote form is in the hero, so this has details plus the general enquiry form. */
export default function ContactSection() {
    return (
        <section className="bg-brand-soft/50 py-14 lg:py-20" aria-labelledby="contact-title">
            <Container>
                <div id="contact-title">
                    <SectionHeading eyebrow={t('nav.contact')} title={t('contact.title')} intro={t('contact.intro')} />
                </div>
                <div className="mt-10 grid gap-8 lg:grid-cols-2">
                    <div>
                        <ContactInfo />
                        <div className="mt-6">
                            <ButtonLink to="/contact" variant="outlineDark" arrow>
                                {t('nav.contact')}
                            </ButtonLink>
                        </div>
                    </div>
                    <ContactForm />
                </div>
            </Container>
        </section>
    );
}
