import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { whatsappLink } from '@/shared/site';
import { useSetting } from '@/shared/siteData';

const cls =
    'fixed bottom-4 right-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform hover:scale-105';

const glyph = (
    <svg viewBox="0 0 24 24" className="h-7 w-7" fill="currentColor" aria-hidden="true">
        <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.2 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .5l-.4.6c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.4.1.6-.1l.8-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.2.1.8-.1 1.4Z" />
    </svg>
);

/**
 * Floating bottom-right WhatsApp button. The number and the default message come from the
 * admin settings; until a real number is set the button opens the contact page instead.
 */
export default function WhatsAppButton() {
    const href = whatsappLink(useSetting('whatsapp_number'), useSetting('whatsapp_default_message', t('whatsapp.defaultMessage')));

    if (!href) {
        return (
            <Link to="/contact" aria-label={t('whatsapp.label')} className={cls}>
                {glyph}
            </Link>
        );
    }

    return (
        <a href={href} target="_blank" rel="noopener noreferrer" aria-label={t('whatsapp.label')} className={cls}>
            {glyph}
        </a>
    );
}
