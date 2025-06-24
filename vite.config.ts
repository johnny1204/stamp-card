import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            hotFile: 'public/hot',
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        host: process.env.VITE_HOST || '0.0.0.0',
        port: parseInt(process.env.VITE_PORT || '5173'),
        strictPort: true,
        cors: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            port: parseInt(process.env.VITE_HMR_PORT || '15173')
        }
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
