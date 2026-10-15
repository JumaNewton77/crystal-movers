import { t } from '@/shared/i18n';
import ContactForm from '../components/ContactForm';
import ContactInfo from '../components/ContactInfo';
import QuoteForm from '../components/QuoteForm';
import { Container, SectionHeading } from '../components/ui';
import { usePageMeta } from '../usePageMeta';

export default function ContactPage() {
    usePageMeta('Contact Crystal Movers', 'Contact Crystal Movers by phone, WhatsApp or email, or send a message. Tell us the service you need and where the work is required.');

    return (
        <>
            <section className="bg-white py-10 lg:py-14">
                <Container>
                    <h1 className="text-4xl font-extrabold tracking-tight text-ink sm:text-5xl">{t('contact.title')}</h1>
                    <p className="mt-3 max-w-2xl text-lg text-ink/70">{t('contact.intro')}</p>
                </Container>
            </section>

            <Container className="grid gap-8 pb-12 lg:grid-cols-[1fr_1.1fr]">
                <div>
                    <SectionHeading title={t('contact.title')} />
                    <div className="mt-6">
                        <ContactInfo />
                    </div>
                </div>

                <div className="space-y-8">
                    <div id="quote" className="scroll-mt-24 rounded-lg bg-ink/5 p-1">
                        <QuoteForm idPrefix="contact-quote" />
                    </div>
                    <ContactForm />
                </div>
            </Container>
        </>
    );
}
