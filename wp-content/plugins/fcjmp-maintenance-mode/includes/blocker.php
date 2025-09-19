<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Détecte une requête d’aperçu de la page maintenance.
 */
function fcjmp_mm_is_preview_request(): bool
{
    if (!fcjmp_mm_get_option('allow_preview')) return false;
    if (is_admin()) return false;
    if (empty($_GET['preview_maintenance'])) return false;
    return current_user_can('manage_options');
}

/**
 * Désactive les redirections “canoniques” et autres redirs
 * qui pourraient court-circuiter l’aperçu.
 */
add_action('init', function () {
    if (!fcjmp_mm_is_preview_request()) return;

    // Empêche les redirections canoniques (home, trailing slashes, etc.)
    add_filter('redirect_canonical', '__return_false', 99);

    // Bloque toute tentative de wp_redirect pendant l’aperçu
    add_filter('wp_redirect', function ($location, $status) {
        return false;
    }, 99, 2);
});

/**
 * Forcer des en-têtes no-cache pour l’aperçu (utile si un cache est en place).
 */
add_action('send_headers', function () {
    if (!fcjmp_mm_is_preview_request()) return;

    // 200 OK et pas de cache
    status_header(200);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Robots-Tag: noindex, nofollow', true);
}, 0);

/**
 * Blocage / Aperçu.
 */
add_action('template_redirect', 'fcjmp_mm_maybe_block', 0);
function fcjmp_mm_maybe_block()
{
    // 1) APERÇU EN PRIORITÉ (force l’affichage, même si maintenance OFF)
    if (fcjmp_mm_is_preview_request()) {
        fcjmp_mm_serve_maintenance(true); // true = preview (200 OK)
        exit;
    }

    // 2) Logique standard uniquement si maintenance effectivement active
    if (!fcjmp_mm_is_effectively_enabled()) return;

    $is_login = fcjmp_mm_is_login();
    $is_admin = is_admin();
    $is_rest  = fcjmp_mm_is_rest();
    $is_cron  = defined('DOING_CRON') && DOING_CRON;
    $is_feed  = is_feed();

    if ($is_cron || $is_feed) return;
    if (fcjmp_mm_get_option('allow_login_admin') && ($is_login || $is_admin)) return;
    if (fcjmp_mm_get_option('allow_rest') && $is_rest) return;

    $ip = fcjmp_mm_get_ip();
    if ($ip && in_array($ip, (array) fcjmp_mm_get_option('whitelist_ips'), true)) return;

    if (is_user_logged_in()) {
        if (fcjmp_mm_get_option('allow_admins_front') && current_user_can('manage_options')) return;
        $wl_users = array_map('intval', (array) fcjmp_mm_get_option('whitelist_users'));
        if (!empty($wl_users) && in_array(get_current_user_id(), $wl_users, true)) return;
    }

    // Si on arrive ici → on bloque
    fcjmp_mm_serve_maintenance(false); // false = 503 réel
    exit;
}

/**
 * Sert la page de maintenance.
 * @param bool $is_preview  true => 200 OK (aperçu admin) | false => 503 (réel)
 */
function fcjmp_mm_serve_maintenance($is_preview = false)
{
    if ($is_preview) {
        status_header(200);
        header('X-Robots-Tag: noindex, nofollow', true);
    } else {
        status_header(503);
        header('Retry-After: ' . max(1, (int) fcjmp_mm_get_option('retry_after_min')) * 60);
    }
    header('Content-Type: text/html; charset=utf-8');

    $form_url      = esc_url(fcjmp_mm_get_option('form_url'));
    $contact_email = sanitize_email(fcjmp_mm_get_option('contact_email'));
    $message       = esc_html(fcjmp_mm_get_option('custom_message'));
    $logo_html     = get_custom_logo();
    $site_name     = get_bloginfo('name');
    $css_href      = esc_url(fcjmp_mm_asset_url('assets/css/maintenance.css'));

    echo '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Site en maintenance' . ($is_preview ? ' — aperçu' : '') . '</title>
<link rel="preload" as="style" href="' . $css_href . '" />
<link rel="stylesheet" href="' . $css_href . '" />
</head>
<body>
  <div class="fcjmp-wrap">
    <div class="fcjmp-card">';
    echo $logo_html ? '<div class="fcjmp-logo">' . $logo_html . '</div>' : '<h2>' . esc_html($site_name) . '</h2>';
    echo '
      <div class="fcjmp-tag">Site en travaux' . ($is_preview ? ' (aperçu admin)' : '') . '</div>
      <h1>Nous revenons très vite.</h1>
      <p class="fcjmp-text">' . $message . '</p>
      <p class="fcjmp-text">Pour les inscriptions à nos formations vous pouvez passer par ici :</p>
      <p><a class="fcjmp-btn" href="' . $form_url . '" target="_blank" rel="noopener">S\'inscrire aux formations</a></p>
      <p class="fcjmp-text">Et si vous avez besoin de nous contacter : <a class="fcjmp-link" href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . '</a></p>
      <div class="fcjmp-footer">' . ($is_preview ? 'Aperçu — 200 OK' : 'HTTP 503') . '</div>
    </div>
  </div>
</body>
</html>';
}
