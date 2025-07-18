// member-admin/vite.config.js
import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "path";

export default defineConfig({
  // En prod on sert depuis le dossier du thème
  base:
    process.env.NODE_ENV === "production"
      ? "/wp-content/themes/fcjmp_custom/espace-membre/"
      : "/",
  plugins: [react()],
  build: {
    emptyOutDir: true,
    outDir: path.resolve(
      __dirname,
      "../wp-content/themes/fcjmp_custom/espace-membre"
    ),
    assetsDir: "",
    rollupOptions: {
      input: path.resolve(__dirname, "src/main.jsx"),
      output: {
        // JS → index.js
        entryFileNames: "index.js",

        // CSS → index.css, tous les autres assets conservent [name][extname]
        assetFileNames: (assetInfo) => {
          // assetInfo.name correspond au nom du chunk d’où vient l’asset
          // la CSS extraite du JS entrée s’appelle 'main' par défaut,
          // on la remappe donc vers index.css :
          if (assetInfo.name === "main" && assetInfo.extname === ".css") {
            return "index.css";
          }
          return "[name][extname]";
        },
      },
    },
  },
});
