import { Link, useParams } from 'react-router-dom';
import { t } from '@/shared/i18n';
import { useSite } from '@/shared/siteData';
import ArticleBody from '../components/ArticleBody';
import Img from '../components/Img';
import { ButtonLink, Container } from '../components/ui';
import { usePageMeta } from '../usePageMeta';
import CtaSection from '../sections/CtaSection';
import NotFoundPage from './NotFoundPage';

export default function BlogPostPage() {
    const { slug } = useParams();
    const post = (useSite().blog_posts ?? []).find((p) => p.slug === slug);

    usePageMeta(post?.meta_title || post?.title || 'Blog', post?.meta_description || post?.excerpt);

    if (!post) return <NotFoundPage />;

    return (
        <>
            <article>
                <Container className="max-w-3xl py-10 lg:py-14">
                    <nav aria-label="Breadcrumb" className="text-sm text-ink/60">
                        <Link to="/" className="hover:text-brand-dark">{t('nav.home')}</Link> / <Link to="/blog" className="hover:text-brand-dark">{t('nav.blog')}</Link>
                    </nav>
                    <h1 className="mt-3 text-4xl font-extrabold tracking-tight text-ink sm:text-5xl">{post.title}</h1>
                    {post.published_at && <p className="mt-3 text-sm text-ink/60">{post.published_at}</p>}
                    {post.image && (
                        <div className="mt-6 overflow-hidden rounded-lg">
                            <Img src={post.image} alt={post.image_alt} priority sizes="(min-width: 768px) 768px, 100vw" className="h-auto w-full object-cover" />
                        </div>
                    )}
                    <div className="mt-8">
                        <ArticleBody text={post.body} />
                    </div>
                    <div className="mt-10">
                        <ButtonLink to="/#quote" arrow>{t('cta.getQuote')}</ButtonLink>
                    </div>
                </Container>
            </article>
            <CtaSection />
        </>
    );
}
