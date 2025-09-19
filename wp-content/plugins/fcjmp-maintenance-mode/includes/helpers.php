<?php
if (!defined('ABSPATH')) {
    exit;
}

/** Valeurs par défaut */
function fcjmp_mm_defaults()
{
    return [
        // Général
        'enabled'             => true,
        'retry_after_min'     => 60,

        // Accès
        'allow_rest'          => true,
        'allow_login_admin'   => true,
        'allow_admins_front'  => true,
        'whitelist_ips'       => [],
        'whitelist_users'     => [],

        // Page publique
        'form_url'            => 'https://docs.google.com/forms/d/15j4R4hkCztqf5OJD8tbsPnuBgOdTl8lzfpg7HTTI4GE/viewform',
        'contact_email'       => 'infos@fcjmp.be',
        'custom_message'      => "Nous effectuons actuellement des opérations de maintenance et de tests. Le site sera de retour sous peu.",

        // Planification
        'schedule_mode'       => 'off', // off | window
        'schedule_start'      => '',    // "Y-m-d H:i" (timezone WP) — on accepte aussi "Y-m-d\TH:i"
        'schedule_end'        => '',

        // Divers
        'allow_preview'       => true,
    ];
}

/** Accès options */
function fcjmp_mm_get_all_options()
{
    $opts = get_option('fcjmp_mm_options', []);
    return wp_parse_args($opts, fcjmp_mm_defaults());
}
function fcjmp_mm_get_option($key)
{
    $o = fcjmp_mm_get_all_options();
    $d = fcjmp_mm_defaults();
    return $o[$key] ?? $d[$key] ?? null;
}

/** URLs d’assets */
function fcjmp_mm_asset_url($path)
{
    return FCJMP_MM_URL . ltrim($path, '/');
}

/** Date/heure */
function fcjmp_mm_timezone()
{
    return wp_timezone(); // WP >= 5.3
}
function fcjmp_mm_now_ts()
{
    $tz = fcjmp_mm_timezone();
    $now = new DateTime('now', $tz);
    return $now->getTimestamp();
}

/**
 * Parse une date/heure locale en tenant compte du fuseau WP.
 * Accepte "Y-m-d H:i" OU "Y-m-d\TH:i" (format <input type="datetime-local">).
 */
function fcjmp_mm_parse_dt_local($str)
{
    if (!$str) return null;
    $tz = fcjmp_mm_timezone();
    $try = [];

    // 1) tel quel (au cas où un plugin aurait déjà normalisé)
    $try[] = $str;
    // 2) remplacer T par espace
    $try[] = str_replace('T', ' ', $str);

    foreach ($try as $candidate) {
        // "Y-m-d H:i"
        $dt = DateTime::createFromFormat('Y-m-d H:i', $candidate, $tz);
        if ($dt instanceof DateTime) return $dt;
        // "Y-m-d\TH:i"
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $candidate, $tz);
        if ($dt instanceof DateTime) return $dt;
        // fallback permissif (strtotime local)
        $ts = strtotime($candidate);
        if ($ts !== false) {
            $dt = new DateTime('@' . $ts);
            $dt->setTimezone($tz);
            return $dt;
        }
    }
    return null;
}

function fcjmp_mm_is_scheduled_active()
{
    if (fcjmp_mm_get_option('schedule_mode') !== 'window') return false;
    $start = fcjmp_mm_parse_dt_local(fcjmp_mm_get_option('schedule_start'));
    $end   = fcjmp_mm_parse_dt_local(fcjmp_mm_get_option('schedule_end'));
    if (!$start || !$end) return false;
    $now_ts = fcjmp_mm_now_ts();
    // Inclusif sur les bornes
    return ($now_ts >= $start->getTimestamp()) && ($now_ts <= $end->getTimestamp());
}

function fcjmp_mm_is_effectively_enabled()
{
    return (bool) fcjmp_mm_get_option('enabled') || fcjmp_mm_is_scheduled_active();
}

/** Détection contexte requêtes */
function fcjmp_mm_is_rest()
{
    if (defined('REST_REQUEST') && REST_REQUEST) return true;
    $rest_prefix = trailingslashit(rest_get_url_prefix());
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return (strpos($uri, '/' . $rest_prefix) !== false);
}
function fcjmp_mm_is_login()
{
    $pagenow = $GLOBALS['pagenow'] ?? '';
    if ($pagenow === 'wp-login.php') return true;
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return (strpos($uri, 'wp-login.php') !== false);
}
function fcjmp_mm_get_ip()
{
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '';
}
