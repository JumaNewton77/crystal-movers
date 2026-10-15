import { t } from '@/shared/i18n';
import { useState } from 'react';
import { useSite } from '@/shared/siteData';
import TestimonialForm from '../components/TestimonialForm';
import Icon, { Stars } from '../components/Icon';
import Img from '../components/Img';
import { Container, SectionHeading } from '../components/ui';

/** Google rating badge. Shows ONLY values the administrator has entered: nothing is invented. */
export function GoogleRating() {
    const { settings } = useSite();
    const rating = Number(settings.google_rating);

    if (!settings.google_rating || !settings.google_review_count || !Number.isFinite(rating) || rating <= 0) return null;

    return (
        <div className="inline-flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-ink/10 bg-white px-5 py-4 shadow-sm">
            <div>
                <p className="text-xs font-semibold uppercase tracking-wide text-ink/60">{t('sections.ratingTitle')}</p>
                <div className="flex items-center gap-2">
                    <span className="text-3xl font-extrabold text-ink">{rating.toFixed(1)}</span>
                    <Stars rating={rating} />
                </div>
                {settings.google_review_count && <p className="text-xs text-ink/60">{t('sections.ratingReviews', { count: settings.google_review_count })}</p>}
            </div>
            {settings.google_business_url && (
                <a href={settings.google_business_url} target="_blank" rel="noopener noreferrer" className="text-sm font-semibold text-brand-dark hover:underline">
                    {t('cta.viewOnGoogle')}
                </a>
            )}
        </div>
    );
}

type Review = ReturnType<typeof useSite>['testimonials'][number];

function ReviewCard({ item, copy = false }: { item: Review; copy?: boolean }) {
    return (
        <li aria-hidden={copy || undefined} className="mr-4 w-72 shrink-0 rounded-lg border border-ink/10 bg-white p-5 shadow-sm sm:w-80">
            <Stars rating={item.rating} className="h-4 w-4" />
            <blockquote className="mt-3 text-sm text-ink/80">“{item.review}”</blockquote>
            <div className="mt-4 flex items-center gap-3">
                {item.photo ? (
                    <Img src={item.photo} alt={item.customer_name} className="h-10 w-10 rounded-full object-cover" width={40} height={40} />
                ) : (
                    <span className="flex h-10 w-10 items-center justify-center rounded-full bg-brand-soft text-brand-dark">
                        <Icon name="user" />
                    </span>
                )}
                <div>
                    <p className="text-sm font-bold text-ink">{item.customer_name}</p>
                    {item.location && <p className="text-xs text-ink/60">{item.location}</p>}
                </div>
            </div>
        </li>
    );
}

/**
 * One single line of review cards. With 3 or more reviews the line glides from right to left forever (the list is
 * repeated once so the loop is seamless; the copy is hidden from screen readers). With fewer it stays still.
 */
function ReviewRow({ items }: { items: Review[] }) {
    const moving = items.length >= 3;

    if (!moving) {
        return (
            <ul className="mt-10 flex flex-wrap gap-4">
                {items.map((item) => <ReviewCard key={item.id} item={item} />)}
            </ul>
        );
    }

    return (
        <div className="cm-marquee mt-10" style={{ ['--cm-marquee-duration' as string]: `${items.length * 9}s` }} tabIndex={0} aria-label={t('sections.testimonialsTitle')}>
            <ul className="cm-marquee-track">
                {items.map((item) => <ReviewCard key={item.id} item={item} />)}
                {items.map((item) => <ReviewCard key={`copy-${item.id}`} item={item} copy />)}
            </ul>
        </div>
    );
}

/** Approved testimonials only (the server never sends any other). Hidden when there are none. */
export default function Testimonials() {
    const { testimonials, settings } = useSite();
    const showRating = Boolean(settings.google_rating && settings.google_review_count);

    const [formOpen, setFormOpen] = useState(false);

    return (
        <section className="bg-white py-14 lg:py-20" aria-labelledby="testimonials-title">
            <Container>
                <div className="flex flex-wrap items-end justify-between gap-6">
                    <div id="testimonials-title">
                        <SectionHeading title={t('sections.testimonialsTitle')} intro={testimonials.length > 0 || showRating ? t('sections.testimonialsIntro') : t('review.emptyIntro')} />
                    </div>
                    <GoogleRating />
                </div>

                {testimonials.length > 0 && <ReviewRow items={testimonials} />}

                <div className="mt-8">
                    {formOpen ? (
                        <div className="max-w-2xl">
                            <TestimonialForm />
                        </div>
                    ) : (
                        <button type="button" onClick={() => setFormOpen(true)} className="rounded-md border border-ink/30 px-5 py-3 font-semibold text-ink hover:border-brand hover:text-brand-dark">
                            {testimonials.length === 0 && !showRating ? t('review.first') : t('review.open')}
                        </button>
                    )}
                </div>
            </Container>
        </section>
    );
}
