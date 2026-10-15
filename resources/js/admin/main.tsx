import '../../css/app.css';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';

const container = document.getElementById('root');
if (!container) throw new Error('Missing #root element');

// The admin is a plain client-side React app (no prerendering, noindex).
createRoot(container).render(
    <StrictMode>
        <BrowserRouter basename="/admin">
            <App />
        </BrowserRouter>
    </StrictMode>,
);
