// vite.config.js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig(({ mode }) => {
    return {
        plugins: [react()],
        build: {
            outDir: 'build',
            rollupOptions: {
                input: {
                    Home: path.resolve(__dirname, 'modules/Home/app.jsx'),
                    Afaq: path.resolve(__dirname, 'modules/Afaq/app.jsx'),
                    // Add more SPA entries here
                },
                output: {
                    entryFileNames: `[name]/main.js`,
                    assetFileNames: `[name]/style.css`,
                    chunkFileNames: `[name]/chunks/[name].js`,
                },
            },
        },
        server: {
            port: 3000,
            hmr: true,
            origin: 'http://localhost:3000',
        },
    };
});
