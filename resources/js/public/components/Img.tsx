import type { ImgHTMLAttributes } from 'react';

/**
 * Image with lazy loading and (for admin uploads) a responsive srcset.
 * Uploaded images are stored as "<name>.webp" plus "<name>-640.webp", so the small version
 * can be offered to phones. Default/placeholder images have no small variant and use a plain src.
 */
export default function Img({
    src,
    alt,
    priority = false,
    sizes = '100vw',
    ...rest
}: { src: string | null | undefined; alt: string; priority?: boolean } & Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'>) {
    if (!src) return null;

    const uploaded = /^\/storage\/.+\.(webp|jpg)$/.test(src);
    const srcSet = uploaded ? `${src.replace(/(\.\w+)$/, '-640$1')} 640w, ${src} 1600w` : undefined;

    return (
        <img
            src={src}
            srcSet={srcSet}
            sizes={srcSet ? sizes : undefined}
            alt={alt}
            loading={priority ? 'eager' : 'lazy'}
            decoding="async"
            {...(priority ? { fetchPriority: 'high' as const } : {})}
            {...rest}
        />
    );
}
