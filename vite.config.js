import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
            '/sanctum': {
                target: 'http://localhost:8080',
                changeOrigin: true,
            },
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
