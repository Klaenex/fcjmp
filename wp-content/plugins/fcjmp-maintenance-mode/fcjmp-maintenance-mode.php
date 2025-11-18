<?php

/**
 * Plugin Name: FCJMP Maintenance Mode
 * Description: Mode maintenance avec whitelist utilisateurs, planification, barre admin, prévisualisation dans l’admin. Réglages réservés aux administrateurs.
 * Version:     3.2.0
 * Author:      Vincent C.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FCJMP_MM_FILE', __FILE__);
define('FCJMP_MM_DIR', plugin_dir_path(__FILE__));
define('FCJMP_MM_URL', plugin_dir_url(__FILE__));
define('FCJMP_MM_VER', '3.2.0');

// Modules
require_once FCJMP_MM_DIR . 'includes/helpers.php';
require_once FCJMP_MM_DIR . 'includes/template.php';
require_once FCJMP_MM_DIR . 'includes/blocker.php';
require_once FCJMP_MM_DIR . 'includes/admin-bar.php';
require_once FCJMP_MM_DIR . 'includes/settings-page.php';

// Defaults à l’activation
register_activation_hook(FCJMP_MM_FILE, function () {
    $current = get_option('fcjmp_mm_options');
    if (!$current) {
        add_option('fcjmp_mm_options', fcjmp_mm_defaults());
    } else {
        update_option('fcjmp_mm_options', wp_parse_args($current, fcjmp_mm_defaults()));
    }
});
