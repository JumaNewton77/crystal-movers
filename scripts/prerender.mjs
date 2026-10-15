/**
 * Build-time prerender for the public website.
 *
 * Normally run through `php artisan site:prerender`, which first exports the database
 * content to storage/app/site-data.json and then runs this script. The flow:
 *
 *   PostgreSQL --(site:export)--> storage/app/site-data.json --(this script)--> storage/app/prerender/*.html
 *
 * Laravel serves a prerendered fragment only while its recorded content hash (_meta.json) equals
 * the live database hash, so a stale snapshot is never shown after an admin edit.
 *
 * To move to full SSR later, call the same `render(url, data)` from a Node server.
 */
import { mkdir, readdir, readFile, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const root = path.resolve(import.meta.dirname, '..');
const outDir = path.join(root, 'storage', 'app', 'prerender');
const dataFile = path.join(root, 'storage', 'app', 'site-data.json');
const bundle = path.join(root, 'bootstrap', 'ssr', 'entry-server.js');

let exported;
try {
    exported = JSON.parse(await readFile(dataFile, 'utf8'));
} catch {
    console.error('storage/app/site-data.json not found. Run `php artisan site:prerender` (or `php artisan site:export`) first.');
    process.exit(1);
}

const { render, routes } = await import(pathToFileURL(bundle).href);

// Clear old output but keep the directory's .gitignore.
await mkdir(outDir, { recursive: true });
for (const entry of await readdir(outDir)) {
    if (entry !== '.gitignore') {
        await rm(path.join(outDir, entry), { recursive: true, force: true });
    }
}

let count = 0;
for (const url of routes(exported.data)) {
    const file = url === '/' ? 'index.html' : `${url.replace(/^\//, '')}.html`;
    const target = path.join(outDir, file);
    await mkdir(path.dirname(target), { recursive: true });
    await writeFile(target, render(url, exported.data), 'utf8');
    count += 1;
}

await writeFile(path.join(outDir, '_meta.json'), JSON.stringify({ hash: exported.hash }), 'utf8');

console.log(`Prerendered ${count} pages to ${path.relative(root, outDir)}/`);
