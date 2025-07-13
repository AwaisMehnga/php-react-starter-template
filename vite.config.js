// vite.config.js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';
import fs from 'fs';

const spaDirs = fs.readdirSync(resolve(__dirname, 'modules')).filter(name =>
  fs.existsSync(resolve(__dirname, `modules/${name}/app.jsx`))
);

const input = Object.fromEntries(
  spaDirs.map(name => [`modules/${name}/app.jsx`, resolve(__dirname, `modules/${name}/app.jsx`)])
);

const isDev = true;


export default defineConfig({
  plugins: [react()],
  base: isDev ? '/' : '/build/',
  build: {
    outDir: 'build',
    manifest: true,
    cssCodeSplit: true,
    rollupOptions: { input } 
  },
  server: {
    port: 3000,
    proxy: {
      '/api': 'http://localhost:8000',
    }
  }
});
