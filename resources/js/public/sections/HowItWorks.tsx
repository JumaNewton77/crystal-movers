import { t } from '@/shared/i18n';
import Icon, { type IconName } from '../components/Icon';
import { ButtonLink, Container, SectionHeading } from '../components/ui';

export const stepIcons: IconName[] = ['clipboard', 'chat', 'calendar', 'truck', 'home'];

/** Five simple steps. Text lives in the translation file; nothing here is an automated workflow. */
export default function HowItWorks({ withHeading = true }: { withHeading?: boolean }) {
    return (
        <section className="bg-brand-soft/50 py-14 lg:py-20" aria-labelledby="how-title">
            <Container>
                {withHeading && (
                    <div className="flex flex-wrap items-end justify-between gap-6">
                        <div id="how-title">
                            <SectionHeading eyebrow={t('sections.howEyebrow')} title={t('sections.howTitle')} intro={t('sections.howIntro')} />
                        </div>
                        <ButtonLink to="/#quote" arrow>
                            {t('cta.getStarted')}
                        </ButtonLink>
                    </div>
                )}

                <ol className="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                    {stepIcons.map((icon, index) => {
                        const n = index + 1;
                        return (
                            <li key={n} className="relative flex gap-4 lg:block lg:text-center">
                                <div className="relative mx-0 flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white text-brand-dark shadow-sm ring-1 ring-brand/20 lg:mx-auto lg:h-20 lg:w-20">
                                    <Icon name={icon} className="h-8 w-8 lg:h-9 lg:w-9" />
                                    <span className="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-brand text-sm font-bold text-white">{n}</span>
                                </div>
                                <div className="lg:mt-4">
                                    <h3 className="text-base font-bold text-ink">{t(`steps.${n}.title`)}</h3>
                                    <p className="mt-1 text-sm text-ink/70">{t(`steps.${n}.text`)}</p>
                                </div>
                            </li>
                        );
                    })}
                </ol>
            </Container>
        </section>
    );
}
