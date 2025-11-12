// bs-config.js
module.exports = {
  // Adresse du WordPress local (via XAMPP)
  proxy: "http://localhost/fcjmp",

  // Fichiers à surveiller
  files: [
    "wp-content/themes/fcjmp_custom/assets/css/**/*.css", // CSS compilé
    "wp-content/themes/fcjmp_custom/espace-membre/**/*.{js,css}", // Build React complet
    "wp-content/themes/fcjmp_custom/**/*.php", // Templates PHP
  ],

  // Options BrowserSync
  notify: false, // Pas de popup
  open: false, // Évite d’ouvrir plusieurs onglets
  port: 3000, // Port par défaut
  ui: { port: 3001 },
  ghostMode: false, // Pas de mirroring entre navigateurs
  logConnections: true,
  logPrefix: "FCJMP",
  reloadDelay: 200, // léger délai pour la stabilité PHP
};
