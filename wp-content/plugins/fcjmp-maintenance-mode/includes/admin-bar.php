<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (!current_user_can('manage_options')) return;

    $enabled = fcjmp_mm_is_effectively_enabled();
    $title   = $enabled ? '⚠️ Maintenance ON' : '✅ Maintenance OFF';
    $action  = $enabled ? 'off' : 'on';
    $url     = wp_nonce_url(admin_url('admin-post.php?action=fcjmp_mm_toggle&set=' . $action), 'fcjmp_mm_toggle');

    // Bouton bascule
    $wp_admin_bar->add_node([
        'id'    => 'fcjmp-mm',
        'title' => $title,
        'href'  => $url,
        'meta'  => ['title' => 'Basculer le mode maintenance']
    ]);

    // Lien vers réglages
    $wp_admin_bar->add_node([
        'id'    => 'fcjmp-mm-settings',
        'parent' => 'fcjmp-mm',
        'title' => 'Réglages',
        'href'  => admin_url('options-general.php?page=fcjmp-mm'),
        'meta'  => ['title' => 'Ouvrir les réglages du mode maintenance']
    ]);

    // ❌ On supprime la prévisualisation front.
}, 1000);

add_action('admin_post_fcjmp_mm_toggle', function () {
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    check_admin_referer('fcjmp_mm_toggle');
    $set  = sanitize_text_field($_GET['set'] ?? '');
    $opts = fcjmp_mm_get_all_options();
    if ($set === 'on')  $opts['enabled'] = true;
    if ($set === 'off') $opts['enabled'] = false;
    update_option('fcjmp_mm_options', $opts);
    wp_safe_redirect(wp_get_referer() ?: admin_url('options-general.php?page=fcjmp-mm'));
    exit;
});
