import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Two browser entries: the public website and the admin app.
            input: [
                'resources/js/public/main.tsx',
                'resources/js/admin/main.tsx',
            ],
            // Used by `vite build --ssr` to prerender public pages at build time.
            ssr: 'resources/js/public/entry-server.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
