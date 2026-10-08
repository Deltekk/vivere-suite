import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/*
 * Unico build frontend della suite: i moduli NON hanno un proprio vite.config.js.
 * - resources/css/filament/theme.css -> tema di tutti i panel Filament
 * - resources/css/app.css, resources/js/app.js -> eventuali pagine fuori dai panel
 */
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/theme.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
