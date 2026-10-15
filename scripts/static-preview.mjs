/**
 * Builds a STATIC, look-only copy of the public website in ./dist-preview for sharing a work in progress
 * (Netlify, Cloudflare Pages, GitHub Pages...). There is no PHP/database there, so forms and the admin do not work.
 *
 *   php artisan site:prerender      (exports the database + renders the pages)
 *   npm run preview:static          (this script)
 *   -> deploy the dist-preview folder
 */
import { cp, mkdir, readdir, readFile, rm, writeFile, stat } from 'node:fs/promises';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const out = path.join(root, 'dist-preview');
const prerenderDir = path.join(root, 'storage', 'app', 'prerender');
const dataFile = path.join(root, 'storage', 'app', 'site-data.json');
const manifestFile = path.join(root, 'public', 'build', 'manifest.json');

const exists = async (p) => stat(p).then(() => true, () => false);

for (const [file, hint] of [[dataFile, 'php artisan site:prerender'], [manifestFile, 'npm run build'], [prerenderDir, 'php artisan site:prerender']]) {
    if (!(await exists(file))) {
        console.error(`Missing ${path.relative(root, file)}. Run: ${hint}`);
        process.exit(1);
    }
}

const { data } = JSON.parse(await readFile(dataFile, 'utf8'));
const manifest = JSON.parse(await readFile(manifestFile, 'utf8'));
const entry = manifest['resources/js/public/main.tsx'];

// Collect the entry script, its shared chunks and every stylesheet.
const css = new Set();
const preload = new Set();
(function walk(chunk) {
    for (const c of chunk.css ?? []) css.add(c);
    for (const key of chunk.imports ?? []) {
        const imported = manifest[key];
        if (imported && !preload.has(imported.file)) {
            preload.add(imported.file);
            walk(imported);
        }
    }
})(entry);

const siteName = data.settings?.company_name || 'Crystal Movers';
const json = JSON.stringify(data).replace(/</g, '\\u003c').replace(/&/g, '\\u0026');
const favicon = data.settings?.favicon || '/favicon.svg';

const notice = `document.addEventListener('submit',function(e){e.preventDefault();e.stopImmediatePropagation();alert('This is a design preview. Forms are switched off here.');},true);`;

function shell(title, body) {
    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>${title}</title>
<link rel="icon" href="${favicon}">
${[...css].map((c) => `<link rel="stylesheet" href="/build/${c}">`).join('\n')}
${[...preload].map((f) => `<link rel="modulepreload" href="/build/${f}">`).join('\n')}
<script>window.__SITE__ = ${json};</script>
<script>${notice}</script>
<script type="module" src="/build/${entry.file}"></script>
</head>
<body>
<div id="root">${body}</div>
</body>
</html>`;
}

await rm(out, { recursive: true, force: true });
await mkdir(out, { recursive: true });

// Static assets.
await cp(path.join(root, 'public', 'build'), path.join(out, 'build'), { recursive: true });
if (await exists(path.join(root, 'public', 'images'))) await cp(path.join(root, 'public', 'images'), path.join(out, 'images'), { recursive: true });
if (await exists(path.join(root, 'public', 'favicon.svg'))) await cp(path.join(root, 'public', 'favicon.svg'), path.join(out, 'favicon.svg'));
// Images uploaded in the admin live in storage/app/public (served at /storage).
if (await exists(path.join(root, 'storage', 'app', 'public'))) await cp(path.join(root, 'storage', 'app', 'public'), path.join(out, 'storage'), { recursive: true, dereference: true });

// Pages: every prerendered fragment becomes <route>/index.html.
let count = 0;
async function pages(dir, rel = '') {
    for (const name of await readdir(dir)) {
        const full = path.join(dir, name);
        if ((await stat(full)).isDirectory()) {
            await pages(full, path.join(rel, name));
        } else if (name.endsWith('.html')) {
            const route = name === 'index.html' ? rel : path.join(rel, name.replace(/\.html$/, ''));
            const target = path.join(out, route, 'index.html');
            await mkdir(path.dirname(target), { recursive: true });
            const fragment = (await readFile(full, 'utf8')).replace(/\r?\n/g, '');
            await writeFile(target, shell(`${siteName} (preview)`, fragment), 'utf8');
            count += 1;
        }
    }
}
await pages(prerenderDir);

await writeFile(path.join(out, '404.html'), shell(`${siteName} (preview)`, ''), 'utf8'); // React shows its own not-found page
await writeFile(path.join(out, 'robots.txt'), 'User-agent: *\nDisallow: /\n', 'utf8');

console.log(`Static preview written to dist-preview/ (${count} pages). Deploy that folder.`);
