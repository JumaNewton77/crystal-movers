import { useEffect } from 'react';
import { useSetting } from '@/shared/siteData';

/**
 * Keeps <title> and the meta description correct during client-side navigation.
 * On the first load Laravel has already injected the real, page-specific values into the HTML
 * (see SeoService), so this only matters once the visitor moves between pages.
 */
export function usePageMeta(title: string, description?: string | null): void {
    const company = useSetting('company_name', 'Crystal Movers');
    const full = title.toLowerCase().includes(company.toLowerCase()) ? title : `${title} | ${company}`;

    useEffect(() => {
        document.title = full;
        if (description) {
            document.querySelector('meta[name="description"]')?.setAttribute('content', description);
        }
    }, [full, description]);
}
