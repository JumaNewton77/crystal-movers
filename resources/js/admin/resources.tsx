import { Badge, mediaUrl } from './components/ui';
import type { ResourceConfig } from './pages/ResourcePage';

const thumb = (path: string | null | undefined) => {
    const src = mediaUrl(path);
    return src ? <img src={src} alt="" className="h-12 w-16 shrink-0 rounded object-cover" /> : <div className="h-12 w-16 shrink-0 rounded bg-ink/10" />;
};

const published = (row: { is_published: boolean }) => (row.is_published ? null : <Badge value="hidden" label="Hidden" />);

export const servicesConfig: ResourceConfig = {
    path: '/admin/services',
    title: 'Services',
    singular: 'service',
    help: 'Add, edit, hide, reorder or remove services. Only published, active services appear on the website and in the enquiry form. "Coming soon" services are shown as display-only cards. Price text is shown only if you enter it; no prices are ever made up.',
    summary: (r) => (
        <div className="flex items-center gap-3">
            {thumb(r.image_path)}
            <div className="min-w-0">
                <div className="font-semibold">{r.title} <Badge value={r.status} label={r.status === 'active' ? 'Active' : 'Coming soon'} /> {published(r as never)}</div>
                <div className="truncate text-ink/60">/services/{r.slug}</div>
            </div>
        </div>
    ),
    fields: [
        { name: 'title', label: 'Title', type: 'text', required: true },
        { name: 'slug', label: 'URL slug', type: 'text', required: true, hint: 'Lowercase letters, numbers and hyphens, e.g. office-relocation. Changing it changes the page URL.' },
        { name: 'service_category_id', label: 'Category', type: 'select', lookup: '/admin/lookups/service-categories' },
        { name: 'status', label: 'Status', type: 'select', required: true, defaultValue: 'active', options: [{ value: 'active', label: 'Active' }, { value: 'coming_soon', label: 'Coming soon (display only)' }] },
        { name: 'short_description', label: 'Short description', type: 'textarea', required: true, hint: 'Shown on service cards.' },
        { name: 'description', label: 'Full description', type: 'textarea' },
        { name: 'benefits', label: 'Benefits', type: 'textarea', hint: 'One benefit per line.' },
        { name: 'price_text', label: 'Price text (optional)', type: 'text', hint: 'e.g. "From KES 2,500" or "Quote on request". Leave empty to show no price.' },
        { name: 'icon', label: 'Icon', type: 'select', options: [{ value: '', label: 'Automatic' }, ...['truck', 'home', 'building', 'box', 'camera', 'shield', 'wifi', 'tv', 'wrench', 'bulb', 'droplet', 'bed', 'lock', 'clipboard'].map((v) => ({ value: v, label: v }))], hint: 'Shown when the service has no photo.' },
        { name: 'image', label: 'Service photo', type: 'image', column: 'image_path' },
        { name: 'image_alt', label: 'Photo description (alt text)', type: 'text', hint: 'Describe the photo for screen readers and search engines.' },
        { name: 'seo_title', label: 'SEO title', type: 'text', hint: 'Optional. Shown in Google results.' },
        { name: 'seo_description', label: 'SEO description', type: 'textarea', hint: 'Optional. About 150 characters.' },
        { name: 'is_published', label: 'Published on the website', type: 'checkbox' },
    ],
};

export const serviceCategoriesConfig: ResourceConfig = {
    path: '/admin/service-categories',
    title: 'Service categories',
    singular: 'category',
    help: 'Categories group your services on the website (for example Moving & Relocation, CCTV & Security). Use the arrows to change the order. A category appears only when published and when it has at least one published service.',
    summary: (r) => (
        <div className="flex items-center gap-3">
            {thumb(r.image_path)}
            <div className="min-w-0">
                <div className="font-semibold">{r.name} {published(r as never)}</div>
                <div className="truncate text-ink/60">{r.slug}</div>
            </div>
        </div>
    ),
    fields: [
        { name: 'name', label: 'Name', type: 'text', required: true },
        { name: 'slug', label: 'URL slug', type: 'text', required: true, hint: 'Lowercase letters, numbers and hyphens.' },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'icon', label: 'Icon', type: 'select', options: [{ value: '', label: 'Automatic' }, ...['truck', 'camera', 'wifi', 'tv', 'droplet', 'bulb', 'wrench', 'bed', 'shield', 'box'].map((v) => ({ value: v, label: v }))] },
        { name: 'image', label: 'Category photo', type: 'image', column: 'image_path' },
        { name: 'image_alt', label: 'Photo description (alt text)', type: 'text' },
        { name: 'is_published', label: 'Published on the website', type: 'checkbox' },
    ],
};

export const faqsConfig: ResourceConfig = {
    path: '/admin/faqs',
    title: 'FAQs',
    singular: 'FAQ',
    help: 'Use the arrows to change the order shown on the website.',
    summary: (r) => (
        <div>
            <div className="font-semibold">{r.question} {published(r as never)}</div>
            <div className="truncate text-ink/60">{r.answer}</div>
        </div>
    ),
    fields: [
        { name: 'question', label: 'Question', type: 'text', required: true },
        { name: 'answer', label: 'Answer', type: 'textarea', required: true },
        { name: 'is_published', label: 'Published on the website', type: 'checkbox' },
    ],
};

export const testimonialsConfig: ResourceConfig = {
    path: '/admin/testimonials',
    title: 'Testimonials',
    singular: 'testimonial',
    help: 'Only add genuine customer reviews. A testimonial appears on the website only when its status is Approved.',
    summary: (r) => (
        <div className="flex items-center gap-3">
            {thumb(r.photo_path)}
            <div className="min-w-0">
                <div className="font-semibold">{r.customer_name} · {'★'.repeat(r.rating)} <Badge value={r.status} /></div>
                <div className="truncate text-ink/60">{r.review}</div>
            </div>
        </div>
    ),
    quickActions: (r) =>
        r.status === 'approved'
            ? [{ label: 'Hide', body: { status: 'hidden' } }]
            : [{ label: 'Approve', body: { status: 'approved' } }],
    fields: [
        { name: 'customer_name', label: 'Customer name', type: 'text', required: true },
        { name: 'location', label: 'Location', type: 'text' },
        { name: 'rating', label: 'Rating (1–5)', type: 'number', required: true, defaultValue: '5' },
        { name: 'review', label: 'Review', type: 'textarea', required: true },
        { name: 'photo', label: 'Customer photo (optional)', type: 'image', column: 'photo_path' },
        { name: 'status', label: 'Status', type: 'select', required: true, defaultValue: 'pending', options: [{ value: 'pending', label: 'Pending' }, { value: 'approved', label: 'Approved (visible)' }, { value: 'hidden', label: 'Hidden' }] },
    ],
};

export const galleryConfig: ResourceConfig = {
    path: '/admin/gallery',
    title: 'Gallery',
    singular: 'gallery image',
    help: 'Upload your own project photos. Images are optimised automatically. Use the arrows to change the order.',
    summary: (r) => (
        <div className="flex items-center gap-3">
            {thumb(r.image_path)}
            <div className="min-w-0">
                <div className="font-semibold">{r.title} {published(r as never)}</div>
                <div className="truncate text-ink/60">{r.category?.name ?? 'No category'}</div>
            </div>
        </div>
    ),
    fields: [
        { name: 'title', label: 'Title', type: 'text', required: true },
        { name: 'image', label: 'Photo', type: 'image', column: 'image_path', hint: 'Required for new images. JPG, PNG or WebP, up to 8 MB.' },
        { name: 'alt_text', label: 'Description for screen readers / SEO', type: 'text', hint: 'e.g. "Movers loading a truck in Nairobi"' },
        { name: 'description', label: 'Caption', type: 'textarea' },
        { name: 'gallery_category_id', label: 'Category', type: 'select', lookup: '/admin/lookups/gallery-categories' },
        { name: 'is_published', label: 'Published on the website', type: 'checkbox' },
    ],
};

export const blogConfig: ResourceConfig = {
    path: '/admin/blog-posts',
    title: 'Blog',
    singular: 'article',
    reorderable: false,
    help: 'Write helpful, original articles. Only published articles appear on the website (the Blog link shows once one is published). Body tips: separate paragraphs with a blank line, start a line with "## " for a heading and with "- " for a bullet point. Do not paste text from other websites.',
    summary: (r) => (
        <div className="flex items-center gap-3">
            {thumb(r.image_path)}
            <div className="min-w-0">
                <div className="font-semibold">{r.title} <Badge value={r.is_published ? 'active' : 'hidden'} label={r.is_published ? 'Published' : 'Draft'} /></div>
                <div className="truncate text-ink/60">/blog/{r.slug}{r.published_at ? ` · ${String(r.published_at).slice(0, 10)}` : ''}</div>
            </div>
        </div>
    ),
    fields: [
        { name: 'title', label: 'Title', type: 'text', required: true },
        { name: 'slug', label: 'URL slug', type: 'text', required: true, hint: 'Lowercase letters, numbers and hyphens, e.g. how-to-prepare-for-a-house-move. Must be unique.' },
        { name: 'excerpt', label: 'Short summary', type: 'textarea', hint: 'Shown on the blog list and in search results if no meta description is set.' },
        { name: 'body', label: 'Article', type: 'textarea', required: true },
        { name: 'image', label: 'Featured image', type: 'image', column: 'image_path' },
        { name: 'image_alt', label: 'Image description (alt text)', type: 'text' },
        { name: 'meta_title', label: 'Meta title (SEO)', type: 'text', hint: 'Optional. About 60 characters.' },
        { name: 'meta_description', label: 'Meta description (SEO)', type: 'textarea', hint: 'Optional. About 150 characters.' },
        { name: 'published_at', label: 'Publication date', type: 'text', hint: 'Format YYYY-MM-DD. Left empty, today is used when you publish.' },
        { name: 'is_published', label: 'Published on the website', type: 'checkbox', defaultValue: false },
    ],
};
