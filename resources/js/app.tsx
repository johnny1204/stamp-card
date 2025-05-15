import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Viteのglob importを使用して、全てのページを事前に収集
        const pages = import.meta.glob('./Pages/**/*.tsx', { eager: true }) as Record<string, { default: React.ComponentType }>;
        
        // ページ名を正規化（./Pages/Auth/Login.tsx -> Auth/Login）
        const normalizedPages: Record<string, React.ComponentType> = {};
        
        Object.keys(pages).forEach(path => {
            const pageName = path
                .replace('./Pages/', '')
                .replace(/\.(tsx|jsx)$/, '');
            normalizedPages[pageName] = pages[path].default;
        });
        
        console.log('🔍 Resolving page:', name);
        console.log('📄 Available pages:', Object.keys(normalizedPages));
        
        if (normalizedPages[name]) {
            console.log('✅ Successfully resolved page:', name);
            return normalizedPages[name];
        }
        
        console.error('❌ Page not found:', name);
        console.error('Available pages:', Object.keys(normalizedPages));
        throw new Error(`Page ${name} not found`);
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});