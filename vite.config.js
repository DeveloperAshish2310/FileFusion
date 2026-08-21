import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
     server: {
        host: true,           // or use '0.0.0.0'
        port: 5173,           // or any open port
        hmr: {
            host: '192.168.1.100', // e.g. '192.168.1.100'
        },
    },
});
