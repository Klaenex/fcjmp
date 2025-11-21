// bs-config.js
module.exports = {
  proxy: "http://localhost/fcjmp",
  files: [
    "wp-content/themes/fcjmp_custom/assets/css/**/*.css",
    "wp-content/themes/fcjmp_custom/**/*.php",
    "wp-content/plugins/interface-membres/dist/**/*.{js,css,html,json}",
    "wp-content/themes/fcjmp_custom/espace-membre/**/*.{php,js,css}",
  ],
  notify: false,
  open: false,
  port: 3000,
  ui: { port: 3001 },
  ghostMode: false,
  logConnections: true,
  logFileChanges: true,
  logPrefix: "FCJMP",
  reloadDelay: 200,
  watchOptions: {
    usePolling: true,
    interval: 1000,
  },
};
