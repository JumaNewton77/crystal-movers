<?php

namespace App\Services;

/**
 * Reads the HTML fragments produced by scripts/prerender.mjs (storage/app/prerender/*.html).
 *
 * The prerender is a snapshot of the database taken at build time. `_meta.json` records the
 * content hash it was built from; when the live content hash differs (an admin edited
 * something), the stale snapshot is skipped and React renders the page in the browser from
 * fresh data instead. Run `php artisan site:prerender` to refresh the snapshots.
 */
class PrerenderRepository
{
    public function get(string $path, string $liveHash): string
    {
        $slug = trim($path, '/');
        $slug = $slug === '' ? 'index' : $slug;

        // Defence in depth: only lowercase slugs and "/" can ever reach the filesystem.
        if (! preg_match('#^[a-z0-9][a-z0-9\-/]*$#', $slug) || str_contains($slug, '..')) {
            return '';
        }

        if ($this->snapshotHash() !== $liveHash) {
            return '';
        }

        $file = storage_path("app/prerender/{$slug}.html");

        return is_file($file) ? (string) file_get_contents($file) : '';
    }

    private function snapshotHash(): ?string
    {
        $meta = storage_path('app/prerender/_meta.json');

        if (! is_file($meta)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($meta), true);

        return is_array($decoded) ? ($decoded['hash'] ?? null) : null;
    }
}
