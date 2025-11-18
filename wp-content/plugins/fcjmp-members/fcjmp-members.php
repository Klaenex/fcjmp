<?php

/**
 * Plugin Name: FCJMP Members
 * Description: Intégration des membres via Monday.com (API + endpoint REST).
 * Version: 1.0.0
 * Author: Vincent C.
 */

if (!defined('ABSPATH')) {
    exit; // Sécurité
}

/**
 * Récupération de la clé API Monday
 * - soit depuis une constante MONDAY_API_KEY définie dans wp-config.php
 * - soit depuis une variable d’environnement
 */
function fcjmp_get_monday_api_key()
{
    if (defined('MONDAY_API_KEY') && MONDAY_API_KEY) {
        return MONDAY_API_KEY;
    }

    if (!empty($_ENV['API_MONDAY'])) {
        return $_ENV['API_MONDAY'];
    }

    return null;
}

/**
 * Enregistrement de la route REST
 * URL : /wp-json/fcjmp/v1/members
 */
add_action('rest_api_init', function () {
    register_rest_route('fcjmp/v1', '/members', [
        'methods'             => 'GET',
        'callback'            => 'fcjmp_rest_get_members',
        'permission_callback' => '__return_true', // à durcir si besoin
    ]);
});

/**
 * Callback de la route REST
 */
function fcjmp_rest_get_members(WP_REST_Request $request)
{
    $api_key = fcjmp_get_monday_api_key();

    if (!$api_key) {
        return new WP_Error(
            'no_api_key',
            'Clé API Monday manquante.',
            ['status' => 500]
        );
    }

    $board_id    = 3180263669; // à adapter si besoin
    $limit_query = 100;

    // Requête GraphQL Monday
    $query = '
        query {
            boards(ids: [' . $board_id . ']) {
                items_page(limit: ' . $limit_query . ') {
                    items {
                        name
                        column_values(
                            ids: ["texte", "email", "location", "phone", "link_mknht68g", "link_mknhjhpb", "numeric_mknpawtj","file_mknpca6"]
                        ) {
                            id
                            text
                        }
                    }
                }
            }
        }
    ';

    $response = wp_remote_post('https://api.monday.com/v2', [
        'headers' => [
            'Authorization' => $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode(['query' => $query]),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return new WP_Error(
            'monday_http_error',
            'Erreur de communication avec Monday.',
            ['status' => 500]
        );
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($body['data']['boards'][0]['items_page']['items'])) {
        return []; // renvoie un tableau vide
    }

    $items = $body['data']['boards'][0]['items_page']['items'];

    // (Optionnel) On peut normaliser les données ici pour éviter au JS
    // de manipuler directement la structure Monday.
    $normalized = array_map('fcjmp_normalize_member', $items);

    return $normalized;
}

/**
 * Normalisation des données d’un membre
 * On convertit column_values en tableau associatif par id
 */
function fcjmp_normalize_member(array $item)
{
    $columns = [];

    if (!empty($item['column_values']) && is_array($item['column_values'])) {
        foreach ($item['column_values'] as $col) {
            if (!empty($col['id'])) {
                $columns[$col['id']] = $col['text'] ?? '';
            }
        }
    }

    return [
        'name'    => $item['name'] ?? '',
        'texte'   => $columns['texte'] ?? '',
        'email'   => $columns['email'] ?? '',
        'location' => $columns['location'] ?? '',
        'phone'   => $columns['phone'] ?? '',
        'site1'   => $columns['link_mknht68g'] ?? '',
        'site2'   => $columns['link_mknhjhpb'] ?? '',
        'region'  => $columns['numeric_mknpawtj'] ?? '',
        'avatar'  => $columns['file_mknpca6'] ?? '',
    ];
}
