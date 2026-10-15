import Icon, { type IconName } from './Icon';

/** Neutral stand-in shown until an image is uploaded in the admin area. Keeps card layout and spacing intact. */
export default function MediaPlaceholder({ icon = 'image', className = '' }: { icon?: IconName; className?: string }) {
    return (
        <div className={`flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-soft to-ink/5 text-brand-dark/60 ${className}`} aria-hidden="true">
            <Icon name={icon} className="h-12 w-12" />
        </div>
    );
}
