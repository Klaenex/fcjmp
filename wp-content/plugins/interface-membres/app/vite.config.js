import { defineConfig } from "vite";

export default defineConfig({
  root: ".",
  build: {
    outDir: "../dist",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: { input: "/src/main.jsx" },
  },
});
