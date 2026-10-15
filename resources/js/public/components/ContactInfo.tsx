import { t } from '@/shared/i18n';
import { telLink, whatsappLink } from '@/shared/site';
import { isPlaceholder, useSetting } from '@/shared/siteData';
import Icon, { type IconName } from './Icon';

function InfoRow({ icon, label, children }: { icon: IconName; label: string; children: React.ReactNode }) {
    return (
        <li className="flex gap-3">
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand-dark">
                <Icon name={icon} />
            </span>
            <div className="min-w-0">
                <p className="text-xs font-semibold uppercase tracking-wide text-ink/60">{label}</p>
                <p className="break-words font-medium text-ink">{children}</p>
            </div>
        </li>
    );
}

/** Contact details and map, all from Admin > Settings. Used by the Contact page and the homepage contact section. */
export default function ContactInfo({ showMap = true }: { showMap?: boolean }) {
    const phone = useSetting('phone');
    const whatsappNumber = useSetting('whatsapp_number');
    const email = useSetting('email');
    const address = useSetting('address');
    const hours = useSetting('working_hours');
    const mapUrl = useSetting('google_maps_embed_url');
    const tel = telLink(phone);
    const whatsapp = whatsappLink(whatsappNumber, useSetting('whatsapp_default_message', t('whatsapp.defaultMessage')));

    // A map is shown only when the owner has pasted a verified Google Maps embed URL in Admin > Settings.
    // Nothing is guessed from the address, so no unverified pin can appear.
    const mapSrc = mapUrl || '';

    return (
        <div>
            <ul className="space-y-5">
                <InfoRow icon="phone" label={t('contact.phone')}>
                    {tel ? <a href={tel} className="hover:text-brand-dark">{phone}</a> : isPlaceholder(phone) ? t('contact.notConfigured') : phone}
                </InfoRow>
                <InfoRow icon="chat" label={t('contact.whatsapp')}>
                    {whatsapp ? (
                        <a href={whatsapp} target="_blank" rel="noopener noreferrer" className="hover:text-brand-dark">
                            {t('cta.whatsapp')}
                        </a>
                    ) : (
                        t('contact.notConfigured')
                    )}
                </InfoRow>
                {email && !isPlaceholder(email) && (
                    <InfoRow icon="mail" label={t('contact.email')}>
                        <a href={`mailto:${email}`} className="hover:text-brand-dark">{email}</a>
                    </InfoRow>
                )}
                <InfoRow icon="pin" label={t('contact.location')}>{address || t('contact.notConfigured')}</InfoRow>
                <InfoRow icon="clock" label={t('contact.hours')}>{hours || t('contact.notConfigured')}</InfoRow>
            </ul>

            {showMap && mapSrc && (
                <div className="mt-8">
                    <h3 className="mb-3 text-lg font-bold text-ink">{t('contact.mapTitle')}</h3>
                    <iframe title="Map" src={mapSrc} loading="lazy" referrerPolicy="no-referrer-when-downgrade" className="h-72 w-full rounded-lg border-0" allowFullScreen />
                </div>
            )}
        </div>
    );
}
