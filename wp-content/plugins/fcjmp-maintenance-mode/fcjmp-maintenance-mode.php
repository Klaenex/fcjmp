<?php

/**
 * Plugin Name: FCJMP Maintenance Mode
 * Description: Mode maintenance avec whitelist par utilisateurs (triés par rôle), planification, barre admin, et page publique stylée. Réglages réservés aux administrateurs.
 * Version:     3.0.0
 * Author:      FCJMP
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FCJMP_MM_FILE', __FILE__);
define('FCJMP_MM_DIR', plugin_dir_path(__FILE__));
define('FCJMP_MM_URL', plugin_dir_url(__FILE__));
define('FCJMP_MM_VER', '3.0.0');

// Chargement des modules
require_once FCJMP_MM_DIR . 'includes/helpers.php';
require_once FCJMP_MM_DIR . 'includes/blocker.php';
require_once FCJMP_MM_DIR . 'includes/admin-bar.php';
require_once FCJMP_MM_DIR . 'includes/settings-page.php';

// Activer les defaults à l’activation
register_activation_hook(FCJMP_MM_FILE, function () {
    $current = get_option('fcjmp_mm_options');
    if (!$current) {
        add_option('fcjmp_mm_options', fcjmp_mm_defaults());
    } else {
        update_option('fcjmp_mm_options', wp_parse_args($current, fcjmp_mm_defaults()));
    }
});
