<?php

namespace App\Support;

/**
 * Images kept in the code base (public/images/site) instead of being uploaded in the admin.
 *
 * Just drop files in with the right name; no code change or database edit is needed:
 *   public/images/site/logo.png|svg|webp|jpg     header logo
 *   public/images/site/favicon.png|svg|ico       browser tab icon
 *   public/images/site/og.jpg                    social-sharing image (falls back to the logo)
 *   public/images/site/hero.jpg                  homepage hero
 *   public/images/site/why-choose.jpg            homepage "why choose us"
 *   public/images/site/safety.jpg                homepage safety section
 *   public/images/site/cta.jpg                   call-to-action banner
 *   public/images/site/about.jpg                 about page
 *   public/images/site/home-about.jpg            homepage About block (falls back to about.jpg)
 *   public/images/site/logo-header.png           compact logo for the header (optional; falls back to logo)
 *   public/images/site/categories/<category-slug>.jpg   e.g. cctv-security.jpg
 *   public/images/site/services/<service-slug>.jpg      e.g. cctv-installation.jpg
 *
 * An image uploaded in the admin always wins; these files are used when the admin has none.
 */
class SiteImages
{
    private const EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png', 'svg', 'ico'];

    /** Content keys (website_content) and the file name that fills them. */
    public const CONTENT = [
        'home.hero.image' => 'hero',
        'home.why.image' => 'why-choose',
        'home.safety.image' => 'safety',
        'home.cta.image' => 'cta',
        'about.image' => 'about',
        'home.about.image' => 'home-about',
    ];

    /** Site-relative URL of public/images/site/{name}.{ext}, or null when no such file exists. */
    public static function find(string $name): ?string
    {
        if (! preg_match('#^[a-z0-9][a-z0-9\-/]*$#', $name) || str_contains($name, '..')) {
            return null;
        }

        foreach (self::EXTENSIONS as $ext) {
            $file = public_path("images/site/{$name}.{$ext}");
            if (is_file($file)) {
                return "/images/site/{$name}.{$ext}?v=".filemtime($file);
            }
        }

        return null;
    }

    /**
     * Returns the url only if the file really exists (site-relative files in public/, uploads on the public disk).
     * A stale database value pointing at a deleted file (e.g. an old logo) is treated as "no image", so the
     * file in public/images/site is used instead of a broken image.
     */
    public static function existing(?string $url): ?string
    {
        if ($url === null || $url === '' || str_starts_with($url, 'http')) {
            return $url ?: null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/storage/')) {
            return \Illuminate\Support\Facades\Storage::disk('public')->exists(substr($path, 9)) ? $url : null;
        }

        return str_starts_with($path, '/') && is_file(public_path(ltrim($path, '/'))) ? $url : null;
    }

    /** Changes whenever a file is added, replaced or removed, so cached site data never goes stale. */
    public static function signature(): string
    {
        $parts = [];
        foreach ([public_path('images/site'), public_path('images/site/categories'), public_path('images/site/services')] as $dir) {
            foreach (glob($dir.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    $parts[] = basename($file).filemtime($file);
                }
            }
        }

        return substr(sha1(implode('|', $parts)), 0, 10);
    }
}
