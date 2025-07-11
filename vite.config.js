import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
    plugins: [react()],
    
    build: {
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'modules/App/app.jsx'),
                // Add more SPAs here:
                // dashboard: resolve(__dirname, 'modules/Dashboard/app.jsx'),
            },
            output: {
                entryFileNames: '[name]/main.js',
                chunkFileNames: '[name]/chunks/[name].js',
                assetFileNames: '[name]/[name].[ext]'
            }
        },
        outDir: 'build',
    },
    
    server: {
        port: 3000,
        proxy: {
            '/api': 'http://localhost:8000'
        }
    }
});
