import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite, type FaqItem } from '@/shared/siteData';
import Icon from '../components/Icon';
import { Container, SectionHeading } from '../components/ui';

/** Accordion built on native <details>: accessible, no JavaScript state, safe to prerender. */
export function Accordion({ items }: { items: FaqItem[] }) {
    return (
        <div className="divide-y divide-ink/10 rounded-lg border border-ink/10 bg-white">
            {items.map((faq) => (
                <details key={faq.id} className="group">
                    <summary className="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 text-left font-semibold text-ink [&::-webkit-details-marker]:hidden">
                        {faq.question}
                        <span className="shrink-0 text-brand-dark">
                            <Icon name="plus" className="h-5 w-5 group-open:hidden" />
                            <Icon name="minus" className="hidden h-5 w-5 group-open:block" />
                        </span>
                    </summary>
                    <p className="whitespace-pre-line px-4 pb-4 text-sm text-ink/70">{faq.answer}</p>
                </details>
            ))}
        </div>
    );
}

/** Home page FAQ: the first few published questions (managed in Admin > FAQs). */
export default function FaqSection() {
    const faqs = useSite().faqs;

    if (faqs.length === 0) return null;

    return (
        <section className="bg-brand-soft/50 py-14 lg:py-20" aria-labelledby="faq-title">
            <Container className="max-w-4xl">
                <div id="faq-title">
                    <SectionHeading eyebrow={t('sections.faqEyebrow')} title={t('sections.faqTitle')} center />
                </div>
                <div className="mt-8">
                    <Accordion items={faqs.slice(0, 6)} />
                </div>
                {faqs.length > 6 && (
                    <p className="mt-6 text-center">
                        <Link to="/faq" className="font-semibold text-brand-dark hover:underline">
                            {t('nav.faq')} →
                        </Link>
                    </p>
                )}
            </Container>
        </section>
    );
}
