import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    
    return {
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/app.tsx"],
                refresh: true,
                hotFile: "public/hot",
            }),
            react(),
            tailwindcss(),
        ],
        server: {
            host: env.VITE_HOST || "0.0.0.0",
            port: parseInt(env.VITE_PORT || "5173"),
            strictPort: true,
            cors: true,
            origin: `http://${env.VITE_HMR_HOST || 'localhost'}:${env.VITE_HMR_PORT || '15173'}`,
            hmr: {
                host: env.VITE_HMR_HOST || 'localhost',
                port: parseInt(env.VITE_HMR_PORT || '15173')
            }
        },
        build: {
            rollupOptions: {
                output: {
                    manualChunks: undefined,
                },
            },
        },
    };
});
