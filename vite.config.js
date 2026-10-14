import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import UnpluginFonts from 'unplugin-fonts/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        UnpluginFonts({
            bunny: {
                families: [
                    {
                        name: 'Instrument Sans',
                        weights: ['400', '500', '600'],
                    },
                ],
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});