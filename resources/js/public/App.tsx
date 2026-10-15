import { Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import AboutPage from './pages/AboutPage';
import BlogPage from './pages/BlogPage';
import BlogPostPage from './pages/BlogPostPage';
import ContactPage from './pages/ContactPage';
import FaqPage from './pages/FaqPage';
import GalleryPage from './pages/GalleryPage';
import HomePage from './pages/HomePage';
import HowItWorksPage from './pages/HowItWorksPage';
import LocationPage from './pages/LocationPage';
import NotFoundPage from './pages/NotFoundPage';
import CategoryPage from './pages/CategoryPage';
import ServicePage from './pages/ServicePage';
import ServicesPage from './pages/ServicesPage';
import { locationList, locationPath } from './routes';

export default function App() {
    return (
        <Routes>
            <Route element={<Layout />}>
                <Route index element={<HomePage />} />
                <Route path="services" element={<ServicesPage />} />
                <Route path="services/category/:slug" element={<CategoryPage />} />
                <Route path="services/:slug" element={<ServicePage />} />
                <Route path="about-us" element={<AboutPage />} />
                <Route path="how-it-works" element={<HowItWorksPage />} />
                <Route path="gallery" element={<GalleryPage />} />
                <Route path="blog" element={<BlogPage />} />
                <Route path="blog/:slug" element={<BlogPostPage />} />
                <Route path="faq" element={<FaqPage />} />
                <Route path="contact" element={<ContactPage />} />
                {locationList.map((l) => (
                    <Route key={l.slug} path={locationPath(l.slug).slice(1)} element={<LocationPage name={l.name} />} />
                ))}
                <Route path="*" element={<NotFoundPage />} />
            </Route>
        </Routes>
    );
}
