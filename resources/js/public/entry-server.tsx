import { renderToString } from 'react-dom/server';
import { StaticRouter } from 'react-router-dom/server';
import { SiteProvider, type SiteData } from '@/shared/siteData';
import App from './App';
import { prerenderPaths } from './routes';

/** Render one public URL to an HTML string (called by scripts/prerender.mjs with the exported database content). */
export function render(url: string, data: SiteData): string {
    return renderToString(
        <SiteProvider data={data}>
            <StaticRouter location={url}>
                <App />
            </StaticRouter>
        </SiteProvider>,
    );
}

/** The list of URLs to prerender. */
export function routes(data: SiteData): string[] {
    return prerenderPaths(data);
}
