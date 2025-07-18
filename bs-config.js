// bs-config.js (à la racine)
module.exports = {
  proxy: "http://localhost/fcjmp/",

  // On watche uniquement :
  //  - Le CSS global généré par Sass
  //  - Le build React (JS + CSS)
  //  - Tes fichiers PHP de thème
  files: [
    "wp-content/themes/fcjmp_custom/assets/css/**/*.css", // SCSS -> CSS
    "wp-content/themes/fcjmp_custom/espace-membre/index.js", // React JS
    "wp-content/themes/fcjmp_custom/espace-membre/index.css", // React CSS
    "wp-content/themes/fcjmp_custom/**/*.php", // Templates PHP
  ],

  notify: false,
  open: true,
  port: 3000,
};
