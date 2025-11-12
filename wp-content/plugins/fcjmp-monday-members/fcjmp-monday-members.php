<?php

/**
 * Plugin Name: FCJMP – Monday Members API
 * Description: Expose un endpoint REST pour récupérer les membres via l’API Monday, sans exposer la clé côté client.
 * Version: 1.0.0
 * Author: FCJMP
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Où stocker la clé ?
 * - Idéal: dans l'environnement serveur: putenv('API_MONDAY=xxx') / .env chargé par wp-config / hébergeur.
 * - Alternative: define('MONDAY_API_KEY', 'xxx'); dans wp-config.php (évite le dépôt git).
 */
function fcjmp_monday_get_api_key()
{
    $key = getenv('API_MONDAY');
    if (!$key && defined('MONDAY_API_KEY')) {
        $key = MONDAY_API_KEY;
    }
    return $key ?: '';
}

/**
 * Endpoint: GET /wp-json/fcjmp/v1/members
 * Retourne un JSON normalisé: [{ name, email, phone, location, region, avatar }]
 *
 * IMPORTANT: adapte les IDs de colonnes ci-dessous à ton board Monday.
 * - email:            "email"
 * - phone:            "phone"
 * - location/adresse: "location"
 * - region (1..6):    "numeric_mknpawtj"
 * - avatar (url):     "file_mknpca6" ou "texte" selon ton usage réel
 */
add_action('rest_api_init', function () {
    register_rest_route('fcjmp/v1', '/members', [
        'methods'  => 'GET',
        'callback' => 'fcjmp_monday_members_handler',
        'permission_callback' => '__return_true', // lecture publique OK
    ]);
});

function fcjmp_monday_members_handler(WP_REST_Request $req)
{
    $api_key   = fcjmp_monday_get_api_key();
    $board_id  = '3180263669';
    $limit     = 100; // si beaucoup d’items, penser à paginer
    $cache_key = 'fcjmp_members_cache_v1';
    $ttl       = 10 * MINUTE_IN_SECONDS;

    if (empty($api_key)) {
        return new WP_REST_Response(['error' => 'API key missing'], 500);
    }

    // Cache
    if ($cached = get_transient($cache_key)) {
        return new WP_REST_Response($cached, 200);
    }

    // GraphQL – adapte les IDs selon ton board
    $query = <<<GQL
    query {
      boards(ids: [$board_id]) {
        items_page (limit: $limit) {
          items {
            name
            column_values(ids: ["email","phone","location","numeric_mknpawtj","file_mknpca6","texte"]) {
              id
              text
            }
          }
        }
      }
    }
    GQL;

    $resp = wp_remote_post('https://api.monday.com/v2', [
        'headers' => [
            'Authorization' => $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode(['query' => $query]),
        'timeout' => 20,
    ]);

    if (is_wp_error($resp)) {
        return new WP_REST_Response(['error' => $resp->get_error_message()], 500);
    }

    $code = wp_remote_retrieve_response_code($resp);
    $body = json_decode(wp_remote_retrieve_body($resp), true);

    if ($code !== 200 || !empty($body['errors'])) {
        return new WP_REST_Response(['error' => $body['errors'] ?? 'Bad response'], 500);
    }

    $items = $body['data']['boards'][0]['items_page']['items'] ?? [];

    // Mappe column_values -> objet { id => text }
    $members = array_map(function ($item) {
        $name = $item['name'] ?? '';
        $kv   = [];
        foreach (($item['column_values'] ?? []) as $cv) {
            $kv[$cv['id']] = $cv['text'] ?? '';
        }

        // Normalisation: ajuste ici si tes colonnes diffèrent
        $email   = $kv['email'] ?? '';
        $phone   = $kv['phone'] ?? '';
        $loc     = $kv['location'] ?? '';
        $region  = $kv['numeric_mknpawtj'] ?? ''; // attendu "1".."6"
        $avatar  = $kv['file_mknpca6'] ?: ($kv['texte'] ?? ''); // fallback sur "texte" si tu stockes l’URL là

        return [
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'location' => $loc,
            'region'   => $region,
            'avatar'   => $avatar,
        ];
    }, $items);

    // Option: filtrer les membres incomplets
    // $members = array_values(array_filter($members, fn($m) => !empty($m['region'])));

    set_transient($cache_key, $members, $ttl);
    return new WP_REST_Response($members, 200);
}
