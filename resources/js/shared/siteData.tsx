import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import { api } from './api';

/** Public website data. It comes from PostgreSQL via Laravel (window.__SITE__ or GET /api/site). */
export interface ServiceCategoryItem {
    slug: string;
    name: string;
    description: string | null;
    icon: string | null;
    image: string | null;
    image_alt: string;
}

export interface ServiceItem {
    id: number;
    slug: string;
    title: string;
    short_description: string;
    description: string | null;
    benefits: string[];
    status: 'active' | 'coming_soon';
    image: string | null;
    image_alt: string;
    price_text: string | null;
    icon: string | null;
    category: string | null;
    seo_title: string | null;
    seo_description: string | null;
}

export interface FaqItem {
    id: number;
    question: string;
    answer: string;
}

export interface TestimonialItem {
    id: number;
    customer_name: string;
    location: string | null;
    rating: number;
    review: string;
    photo: string | null;
}

export interface GalleryEntry {
    id: number;
    title: string;
    description: string | null;
    alt: string;
    image: string | null;
    category: string | null;
}

export interface BlogPostItem {
    slug: string;
    title: string;
    excerpt: string | null;
    body: string;
    image: string | null;
    image_alt: string;
    meta_title: string | null;
    meta_description: string | null;
    published_at: string | null;
}

export interface SiteData {
    settings: Record<string, string | null>;
    content: Record<string, string | null>;
    service_categories: ServiceCategoryItem[];
    services: ServiceItem[];
    faqs: FaqItem[];
    testimonials: TestimonialItem[];
    gallery: GalleryEntry[];
    gallery_categories: { slug: string; name: string }[];
    blog_posts?: BlogPostItem[];
    locations: { slug: string; name: string }[];
}

declare global {
    interface Window {
        __SITE__?: SiteData;
    }
}

export const emptySite: SiteData = {
    settings: {},
    content: {},
    service_categories: [],
    services: [],
    faqs: [],
    testimonials: [],
    gallery: [],
    gallery_categories: [],
    blog_posts: [],
    locations: [],
};

const SiteContext = createContext<SiteData>(emptySite);

export function SiteProvider({ data, children }: { data?: SiteData; children: ReactNode }) {
    const [site, setSite] = useState<SiteData>(data ?? emptySite);

    // Normally the page shell already contains the data. Fetch only if it is missing (e.g. dev tooling).
    useEffect(() => {
        if (data) return;
        api<SiteData>('/site')
            .then((response) => setSite(response.data))
            .catch(() => undefined);
    }, [data]);

    return <SiteContext.Provider value={site}>{children}</SiteContext.Provider>;
}

export function useSite(): SiteData {
    return useContext(SiteContext);
}

/** A company setting from the admin area, with a fallback. */
export function useSetting(key: string, fallback = ''): string {
    return useSite().settings[key] || fallback;
}

/** Editable website text from the admin area, with a fallback. */
export function useContent(key: string, fallback = ''): string {
    return useSite().content[key] || fallback;
}

/** Placeholder values contain "XXX" and must never be turned into links. */
export function isPlaceholder(value: string | null | undefined): boolean {
    return !value || /x{3}/i.test(value);
}
