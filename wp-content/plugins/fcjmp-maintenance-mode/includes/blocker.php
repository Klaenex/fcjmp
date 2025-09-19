<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {
    if (fcjmp_mm_is_effectively_enabled() && !headers_sent()) {
        nocache_headers();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Robots-Tag: noindex, nofollow', true);
    }
}, 0);

add_action('template_redirect', 'fcjmp_mm_maybe_block', 0);
function fcjmp_mm_maybe_block()
{

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


    status_header(503);
    header('Retry-After: ' . max(1, (int) fcjmp_mm_get_option('retry_after_min')) * 60);
    header('Content-Type: text/html; charset=utf-8');
    echo fcjmp_mm_get_maintenance_html(false);
    exit;
}
