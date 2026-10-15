import '../../css/app.css';
import { StrictMode } from 'react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { SiteProvider } from '@/shared/siteData';
import App from './App';

const container = document.getElementById('root');
if (!container) throw new Error('Missing #root element');

const app = (
    <StrictMode>
        <SiteProvider data={window.__SITE__}>
            <BrowserRouter>
                <App />
            </BrowserRouter>
        </SiteProvider>
    </StrictMode>
);

// Laravel only includes prerendered markup when it matches the current database content,
// so a non-empty #root can be hydrated safely. Otherwise React renders from window.__SITE__.
if (container.hasChildNodes()) {
    hydrateRoot(container, app);
} else {
    createRoot(container).render(app);
}
