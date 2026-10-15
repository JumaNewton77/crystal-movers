import { Link } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import Img from '../components/Img';
import { Container } from '../components/ui';
import { blogPath } from '../routes';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';

export default function BlogPage() {
    const posts = useSite().blog_posts ?? [];
    usePageMeta('Blog: Moving and Installation Guides', 'Practical guides from Crystal Movers on preparing for a move, mounting a TV, Wi-Fi coverage, CCTV planning and furniture assembly.');

    return (
        <>
            <section className="bg-white py-12 lg:py-16">
                <Container>
                    <h1 className="text-4xl font-extrabold tracking-tight text-ink sm:text-5xl">{t('blog.title')}</h1>
                    <p className="mt-3 max-w-2xl text-lg text-ink/70">{t('blog.intro')}</p>
                </Container>
            </section>
            <Container className="pb-14">
                {posts.length === 0 ? (
                    <p className="text-ink/70">{t('blog.empty')}</p>
                ) : (
                    <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {posts.map((post) => (
                            <li key={post.slug} className="overflow-hidden rounded-lg border border-ink/10 bg-white shadow-sm">
                                {post.image && <Img src={post.image} alt={post.image_alt} sizes="(min-width: 1024px) 33vw, 100vw" className="h-44 w-full object-cover" width={640} height={352} />}
                                <div className="p-5">
                                    {post.published_at && <p className="text-xs font-semibold uppercase tracking-wide text-ink/60">{post.published_at}</p>}
                                    <h2 className="mt-1 text-xl font-extrabold text-ink">
                                        <Link to={blogPath(post.slug)} className="hover:text-brand-dark">{post.title}</Link>
                                    </h2>
                                    {post.excerpt && <p className="mt-2 text-sm text-ink/70">{post.excerpt}</p>}
                                    <Link to={blogPath(post.slug)} className="mt-3 inline-block text-sm font-semibold text-brand-dark hover:underline">{t('blog.read')} →</Link>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </Container>
            <CtaSection />
        </>
    );
}
