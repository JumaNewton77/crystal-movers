import { Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider, useAuth } from './auth';
import Shell from './components/Shell';
import { Spinner } from './components/ui';
import DashboardPage from './pages/DashboardPage';
import KeyValuePage from './pages/KeyValuePage';
import LoginPage from './pages/LoginPage';
import MessagesPage from './pages/MessagesPage';
import QuoteDetailPage from './pages/QuoteDetailPage';
import QuotesPage from './pages/QuotesPage';
import ResourcePage from './pages/ResourcePage';
import { blogConfig, faqsConfig, galleryConfig, serviceCategoriesConfig, servicesConfig, testimonialsConfig } from './resources';

function Protected() {
    const { user, loading } = useAuth();
    if (loading) return <Spinner />;
    if (!user) return <Navigate to="/login" replace />;
    return <Shell />;
}

export default function App() {
    return (
        <AuthProvider>
            <Routes>
                <Route path="login" element={<LoginPage />} />
                <Route element={<Protected />}>
                    <Route index element={<DashboardPage />} />
                    <Route path="quotes" element={<QuotesPage />} />
                    <Route path="quotes/:id" element={<QuoteDetailPage />} />
                    <Route path="messages" element={<MessagesPage />} />
                    <Route path="services" element={<ResourcePage config={servicesConfig} />} />
                    <Route path="service-categories" element={<ResourcePage config={serviceCategoriesConfig} />} />
                    <Route path="testimonials" element={<ResourcePage config={testimonialsConfig} />} />
                    <Route path="gallery" element={<ResourcePage config={galleryConfig} />} />
                    <Route path="blog" element={<ResourcePage config={blogConfig} />} />
                    <Route path="faqs" element={<ResourcePage config={faqsConfig} />} />
                    <Route
                        path="content"
                        element={
                            <KeyValuePage
                                title="Website content & images"
                                help="Edit the main website text and replace the photos used on the homepage. Image changes go live immediately; text goes live when you press Save."
                                path="/admin/content"
                                bodyKey="content"
                                isImage={(k) => k.endsWith('.image')}
                            />
                        }
                    />
                    <Route
                        path="settings"
                        element={
                            <KeyValuePage
                                title="Settings"
                                help="Company details, contact information, social links, Google rating, insurance wording and default SEO. Placeholder values (XXX) are never shown as links on the website."
                                path="/admin/settings"
                                bodyKey="settings"
                                isImage={(k) => k === 'logo_path' || k === 'favicon_path' || k === 'seo_og_image'}
                            />
                        }
                    />
                </Route>
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </AuthProvider>
    );
}
