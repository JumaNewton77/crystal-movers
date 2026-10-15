import { useSetting } from '@/shared/siteData';
import { usePageMeta } from '../usePageMeta';
import AboutSection from '../sections/AboutSection';
import ContactSection from '../sections/ContactSection';
import CtaSection from '../sections/CtaSection';
import FaqSection from '../sections/FaqSection';
import FutureServices from '../sections/FutureServices';
import GallerySection from '../sections/GallerySection';
import Hero from '../sections/Hero';
import HowItWorks from '../sections/HowItWorks';
import Safety from '../sections/Safety';
import ServiceAreas from '../sections/ServiceAreas';
import ServicesSection from '../sections/ServicesSection';
import Testimonials from '../sections/Testimonials';
import TrustStrip from '../sections/TrustStrip';
import WhyChoose from '../sections/WhyChoose';

/** Offsets in-page anchors (/#about, /#faq ...) below the sticky header. */
function Anchor({ id, children }: { id: string; children: React.ReactNode }) {
    return (
        <div id={id} className="scroll-mt-20">
            {children}
        </div>
    );
}

/** Section order follows the Crystal Movers homepage specification (About sits next to Why Choose). Every section reads its data from PostgreSQL via the site payload. */
export default function HomePage() {
    usePageMeta(useSetting('seo_default_title', 'Movers in Nairobi & Across Kenya'), useSetting('seo_default_description'));

    return (
        <>
            <Hero />
            <TrustStrip />
            <Anchor id="services"><ServicesSection /></Anchor>
            <WhyChoose />
            <Anchor id="about"><AboutSection /></Anchor>
            <Anchor id="how-it-works"><HowItWorks /></Anchor>
            <Anchor id="gallery"><GallerySection /></Anchor>
            <Safety />
            <Anchor id="reviews"><Testimonials /></Anchor>
            <Anchor id="faq"><FaqSection /></Anchor>
            <Anchor id="areas"><ServiceAreas /></Anchor>
            <FutureServices />
            <CtaSection />
            <Anchor id="contact"><ContactSection /></Anchor>
        </>
    );
}
