import { t } from '@/shared/i18n';
import { telLink, whatsappLink } from '@/shared/site';
import { useContent, useSetting } from '@/shared/siteData';
import Img from '../components/Img';
import { ButtonLink, Container } from '../components/ui';

export default function CtaSection() {
    const title = useContent('cta.title');
    const text = useContent('cta.text');
    const image = useContent('home.cta.image');
    const imageAlt = useContent('home.cta.image_alt');
    const whatsapp = whatsappLink(useSetting('whatsapp_number'), useSetting('whatsapp_default_message', t('whatsapp.defaultMessage')));
    const phone = useSetting('phone');
    const tel = telLink(phone);

    if (!title) return null;

    return (
        <section className="relative isolate overflow-hidden bg-ink py-14 text-white lg:py-20" aria-labelledby="cta-title">
            <Img src={image} alt={imageAlt} className="absolute inset-0 -z-20 h-full w-full object-cover opacity-30" />
            <div className="absolute inset-0 -z-10 bg-gradient-to-r from-ink via-ink/85 to-ink/50" aria-hidden="true" />
            <Container>
                <h2 id="cta-title" className="text-3xl font-extrabold tracking-tight sm:text-4xl">
                    {title}
                </h2>
                {text && <p className="mt-3 max-w-xl text-lg text-white/85">{text}</p>}
                <div className="mt-7 flex flex-wrap items-center gap-3">
                    <ButtonLink to="/#quote" arrow>
                        {t('cta.requestQuote')}
                    </ButtonLink>
                    {tel && (
                        <ButtonLink href={tel} variant="outline">
                            {t('cta.callCrystal')}
                        </ButtonLink>
                    )}
                    {whatsapp && (
                        <ButtonLink href={whatsapp} external variant="outline">
                            {t('cta.whatsapp')}
                        </ButtonLink>
                    )}
                    {!tel && !whatsapp && (
                        <ButtonLink to="/contact" variant="outline">
                            {t('nav.contact')}
                        </ButtonLink>
                    )}
                </div>
            </Container>
        </section>
    );
}
