Put your images here (no admin upload needed). Use these exact names; extension can be jpg, png, webp (svg for logo).

logo.png            header logo (transparent PNG/SVG, at least 400px wide)
favicon.png         browser tab icon (or favicon.svg / favicon.ico)
og.jpg              image shown when the site is shared (1200x630)
hero.jpg            homepage hero photo (1600px wide or more)
why-choose.jpg      homepage "Why choose us" photo
safety.jpg          homepage safety photo
cta.jpg             call-to-action banner photo
about.jpg           About page photo
home-about.jpg      photo beside the homepage About text (optional; uses about.jpg if missing)
logo-header.png     compact logo (no tagline) for the header (optional; uses logo.png if missing)

categories/<category-slug>.jpg   moving, cctv-security, internet-networking, tv-satellite,
                                 washing-machine, lighting, shower, furniture-assembly
services/<service-slug>.jpg      e.g. cctv-installation.jpg (slug = the page URL name)

After adding files run:  npm run build   and   php artisan site:prerender
(An image uploaded in the admin overrides the file here.)
