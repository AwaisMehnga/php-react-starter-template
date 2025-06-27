import { defineConfig } from "vite";
import react from '@vitejs/plugin-react'
import path from "path";

export default defineConfig({
  plugins: [react()],

  // Base URL for serving assets in production (relative to PHP public path)
  base: "/",

  build: {
    outDir: path.resolve(__dirname, "./build"),
    emptyOutDir: true,
    rollupOptions: {
      input: path.resolve(__dirname, "./src/index.html"),
      output: {
        entryFileNames: `main.js`,
        chunkFileNames: `chunks/[name].js`,
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return 'style.css';
          }
          return 'assets/[name][extname]';
        }
      }
    },
  },


  server: {
    port: 3000,
    hmr: true,
    origin: 'http://localhost:3000',
    // Proxy PHP API requests to your backend server if needed
    proxy: {
      "/api": "http://localhost:8002",
    },
  },
});
