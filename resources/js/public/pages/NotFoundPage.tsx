import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { usePageMeta } from '../usePageMeta';

export default function NotFoundPage() {
    usePageMeta(t('notFound.title'));

    return (
        <div className="mx-auto max-w-3xl px-4 py-20 text-center">
            <p className="text-6xl font-extrabold text-brand">404</p>
            <h1 className="mt-3 text-3xl font-bold text-ink">{t('notFound.title')}</h1>
            <p className="mt-3 text-ink/70">{t('notFound.body')}</p>
            <Link to="/" className="mt-6 inline-block rounded-md bg-brand px-6 py-3 font-semibold text-white hover:bg-brand-dark">
                {t('nav.home')}
            </Link>
        </div>
    );
}
