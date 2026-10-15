import { Outlet } from 'react-router-dom';
import Header from './Header';
import Footer from './Footer';
import ScrollManager from './ScrollManager';
import WhatsAppButton from './WhatsAppButton';

export default function Layout() {
    return (
        <div className="flex min-h-screen flex-col">
            <ScrollManager />
            <Header />
            <main className="flex-1">
                <Outlet />
            </main>
            <Footer />
            <WhatsAppButton />
        </div>
    );
}
