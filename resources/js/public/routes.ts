import locations from '../../content/locations.json';
import type { SiteData } from '@/shared/siteData';

export const staticPaths = ['/', '/services', '/about-us', '/how-it-works', '/gallery', '/faq', '/contact'] as const;

export const blogPath = (slug: string): string => `/blog/${slug}`;

export const servicePath = (slug: string): string => `/services/${slug}`;
export const categoryPath = (slug: string): string => `/services/category/${slug}`;
export const locationPath = (slug: string): string => `/movers-in-${slug}`;

/** Locations are static SEO configuration (resources/content/locations.json), shared with Laravel. */
export const locationList: { slug: string; name: string }[] = locations;

/** Every public URL that is prerendered to static HTML. Services come from the database export. */
export function prerenderPaths(data: SiteData): string[] {
    return [
        ...staticPaths,
        ...(data.service_categories ?? []).map((c) => categoryPath(c.slug)),
        ...data.services.map((s) => servicePath(s.slug)),
        ...locationList.map((l) => locationPath(l.slug)),
        // The blog index is prerendered only when there is something to show.
        ...((data.blog_posts ?? []).length > 0 ? ['/blog', ...(data.blog_posts ?? []).map((p) => blogPath(p.slug))] : []),
    ];
}
