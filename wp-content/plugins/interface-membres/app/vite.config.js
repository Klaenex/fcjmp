import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "path";

export default defineConfig({
  root: ".",
  plugins: [react()],
  build: {
    // on place le dist à la racine du plugin : wp-content/plugins/interface-membres/dist
    outDir: path.resolve(__dirname, "../dist"),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      // point d'entrée relatif correctement résolu
      input: path.resolve(__dirname, "src/main.jsx"),
    },
  },

  // évite l'erreur "use client" lors du pre-bundle
  optimizeDeps: {
    exclude: ["react-router", "react-router-dom"],
  },

  // s'assure que react-router est bundlé correctement en build SSR / prod
  ssr: {
    noExternal: ["react-router", "react-router-dom"],
  },

  // optionnel : config dev (pratique si tu veux lancer vite dev server)
  server: {
    port: 5173,
    host: true, // permet d'accéder depuis l'IP locale
  },
});
