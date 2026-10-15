import { t } from '@/shared/i18n';
import Icon, { type IconName } from '../components/Icon';
import { Container } from '../components/ui';

const items: { icon: IconName; label: string }[] = [
    { icon: 'check', label: 'trust.range' },
    { icon: 'chat', label: 'trust.communication' },
    { icon: 'shield', label: 'trust.care' },
    { icon: 'pin', label: 'trust.coverage' },
];

export default function TrustStrip() {
    return (
        <section className="border-y border-ink/10 bg-white text-ink" aria-label="What to expect from Crystal Movers">
            <Container>
                <ul className="grid grid-cols-2 gap-4 py-5 lg:grid-cols-4">
                    {items.map((item) => (
                        <li key={item.label} className="flex items-center gap-3">
                            <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-brand bg-brand-soft text-brand-dark">
                                <Icon name={item.icon} />
                            </span>
                            <span className="text-sm font-semibold">{t(item.label)}</span>
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}
