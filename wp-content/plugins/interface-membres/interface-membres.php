<?php

/**
 * Plugin Name: Interface Membres (React)
 * Description: Espace membre React permettant de soumettre et modérer plusieurs types de contenus (Articles, Offres d’emplois, Activités).
 * Version:     1.0.0
 * Author:      Votre Nom
 * Text Domain: interface-membres
 */

if (!defined('ABSPATH')) {
    exit;
}

final class IM_Interface_Membres
{
    const ROLE            = 'membre';
    const CPT_OFFRES      = 'offres';
    const CPT_ACTIVITES   = 'activites';
    const STATUS_REJECTED = 'rejected';
    const SHORTCODE       = 'interface_membres';
    const HANDLE          = 'interface-membres-app';
    const REST_NS         = 'im/v1';

    // Types de contenus gérés dans l’app (slug => config)
    private $content_types = [
        // post natif
        'post' => [
            'label' => 'Articles',
            'rest_base' => 'posts',
            'cap_publish' => 'publish_posts',
            'cap_edit_others' => 'edit_others_posts',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin' => true,
        ],
        // CPT offres
        self::CPT_OFFRES => [
            'label' => 'Offres d’emplois',
            'rest_base' => self::CPT_OFFRES,
            'cap_publish' => 'publish_offres',
            'cap_edit_others' => 'edit_others_offres',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin' => false,
        ],
        // CPT activités
        self::CPT_ACTIVITES => [
            'label' => 'Activités',
            'rest_base' => self::CPT_ACTIVITES,
            'cap_publish' => 'publish_activites',
            'cap_edit_others' => 'edit_others_activites',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin' => false,
        ],
    ];

    public function __construct()
    {
        register_activation_hook(__FILE__,  [$this, 'on_activate']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivate']);

        add_action('init',                    [$this, 'register_cpts']);
        add_action('init',                    [$this, 'register_rejected_status']);
        add_action('init',                    [$this, 'ensure_role_caps']);
        add_filter('display_post_states',     [$this, 'show_custom_status_label'], 10, 2);

        add_shortcode(self::SHORTCODE,        [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts',      [$this, 'maybe_enqueue_assets']);

        add_action('rest_api_init',           [$this, 'register_rest_routes']);
    }

    /* ---------------- Activation / Désactivation ---------------- */

    public function on_activate()
    {
        $this->ensure_role_caps();
        $this->register_cpts();
        $this->register_rejected_status();
        flush_rewrite_rules();
    }

    public function on_deactivate()
    {
        // On garde rôle/statuts/CPT (données).
        flush_rewrite_rules();
    }

    /* ---------------- Rôle & Capacités ---------------- */

    public function ensure_role_caps()
    {
        // Rôle "membre" : créer/éditer ses contenus, uploader, mais pas publier.
        $base_caps = [
            'read'                   => true,
            'upload_files'           => true,
            'edit_posts'             => true,
            'delete_posts'           => false,
            'publish_posts'          => false,
            'edit_published_posts'   => false,
            'delete_published_posts' => false,
            'edit_others_posts'      => false,
            'delete_others_posts'    => false,
        ];

        $role = get_role(self::ROLE);
        if (!$role) {
            $role = add_role(self::ROLE, 'Membre', $base_caps);
        }
        if ($role) {
            foreach ($base_caps as $k => $v) {
                $v ? $role->add_cap($k) : $role->remove_cap($k);
            }
        }

        // Caps CPT OFFRES (capability_type => ['offre','offres'])
        $offres_caps = [
            'edit_offre'              => true,
            'read_offre'              => true,
            'delete_offre'            => false,
            'edit_offres'             => true,
            'edit_others_offres'      => false,
            'publish_offres'          => false,
            'read_private_offres'     => false,
            'delete_offres'           => false,
            'delete_private_offres'   => false,
            'delete_published_offres' => false,
            'delete_others_offres'    => false,
            'edit_private_offres'     => false,
            'edit_published_offres'   => false,
        ];
        if ($role) {
            foreach ($offres_caps as $k => $v) {
                $v ? $role->add_cap($k) : $role->remove_cap($k);
            }
        }

        // Caps CPT ACTIVITES (capability_type => ['activite','activites'])
        $activites_caps = [
            'edit_activite'              => true,
            'read_activite'              => true,
            'delete_activite'            => false,
            'edit_activites'             => true,
            'edit_others_activites'      => false,
            'publish_activites'          => false,
            'read_private_activites'     => false,
            'delete_activites'           => false,
            'delete_private_activites'   => false,
            'delete_published_activites' => false,
            'delete_others_activites'    => false,
            'edit_private_activites'     => false,
            'edit_published_activites'   => false,
        ];
        if ($role) {
            foreach ($activites_caps as $k => $v) {
                $v ? $role->add_cap($k) : $role->remove_cap($k);
            }
        }
    }

    /* ---------------- CPT & Statut ---------------- */

    public function register_cpts()
    {
        // OFFRES
        register_post_type(self::CPT_OFFRES, [
            'labels' => [
                'name' => __('Offres d’emplois', 'interface-membres'),
                'singular_name' => __('Offre d’emploi', 'interface-membres'),
            ],
            'public'       => true,
            'show_ui'      => true,
            'show_in_menu' => true,
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'offres'],
            'supports'     => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest' => true,
            'rest_base'    => self::CPT_OFFRES,
            'map_meta_cap' => true,
            'capability_type' => ['offre', 'offres'],
        ]);

        // ACTIVITES
        register_post_type(self::CPT_ACTIVITES, [
            'labels' => [
                'name' => __('Activités', 'interface-membres'),
                'singular_name' => __('Activité', 'interface-membres'),
            ],
            'public'       => true,
            'show_ui'      => true,
            'show_in_menu' => true,
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'activites'],
            'supports'     => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest' => true,
            'rest_base'    => self::CPT_ACTIVITES,
            'map_meta_cap' => true,
            'capability_type' => ['activite', 'activites'],
        ]);
    }

    public function register_rejected_status()
    {
        register_post_status(self::STATUS_REJECTED, [
            'label'                     => _x('Rejeté', 'post status', 'interface-membres'),
            'public'                    => false,
            'internal'                  => false,
            'exclude_from_search'       => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'show_in_rest'              => true,
            'label_count'               => _n_noop('Rejeté <span class="count">(%s)</span>', 'Rejetés <span class="count">(%s)</span>', 'interface-membres'),
        ]);
    }

    public function show_custom_status_label($states, $post)
    {
        if ($post->post_status === self::STATUS_REJECTED) {
            $states['rejected'] = __('Rejeté', 'interface-membres');
        }
        return $states;
    }

    /* ---------------- Shortcode & Assets React ---------------- */

    public function render_shortcode()
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Vous devez être connecté pour accéder à cet espace.', 'interface-membres') . '</p>';
        }
        return '<div id="im-app-root"></div>';
    }

    public function maybe_enqueue_assets()
    {
        if (! is_singular()) return;
        global $post;
        if (! $post) return;

        $has_sc = has_shortcode($post->post_content, self::SHORTCODE);


        $uses_template = is_page_template('page-espace-membre.php') || is_page_template('page-espace-membre-react.php');

        if ($has_sc || $uses_template) {
            $this->enqueue_vite_assets_and_context();
        }
    }


    private function enqueue_vite_assets_and_context()
    {
        $manifest_path = plugin_dir_path(__FILE__) . 'dist/manifest.json';
        $manifest = null;
        if (file_exists($manifest_path)) {
            $json = file_get_contents($manifest_path);
            $manifest = $json ? json_decode($json, true) : null;
        }

        if ($manifest && is_array($manifest)) {
            $entry = null;
            foreach ($manifest as $file => $meta) {
                if (!empty($meta['isEntry'])) {
                    $entry = $meta;
                    break;
                }
            }
            if ($entry) {
                if (!empty($entry['css']) && is_array($entry['css'])) {
                    foreach ($entry['css'] as $css) {
                        wp_enqueue_style(self::HANDLE . '-css-' . md5($css), plugins_url('dist/' . $css, __FILE__), [], null);
                    }
                }
                $entry_url = plugins_url('dist/' . $entry['file'], __FILE__);
                wp_enqueue_script(self::HANDLE, $entry_url, [], null, true);
            }
        }

        $current_user = wp_get_current_user();

        // Filtrer les types autorisés si nécessaire (ici on expose les 3)
        $types = [];
        foreach ($this->content_types as $slug => $def) {
            $types[$slug] = [
                'label' => $def['label'],
                'rest_base' => $def['rest_base'],
                'is_builtin' => $def['is_builtin'],
                'supports' => $def['supports'],
                'caps' => [
                    'can_publish'   => current_user_can($def['cap_publish']),
                    'can_edit_others' => current_user_can($def['cap_edit_others']),
                ]
            ];
        }

        $context = [
            'restUrl'     => esc_url_raw(rest_url()),
            'nonce'       => wp_create_nonce('wp_rest'),
            'currentUser' => [
                'id'    => $current_user->ID,
                'name'  => $current_user->display_name,
                'roles' => $current_user->roles,
            ],
            'status'      => [
                'draft'    => 'draft',
                'pending'  => 'pending',
                'publish'  => 'publish',
                'rejected' => self::STATUS_REJECTED,
            ],
            'types'       => $types,
            'siteUrl'     => home_url('/'),
        ];
        wp_localize_script(self::HANDLE, 'IMAppConfig', $context);
    }

    /* ---------------- 
    Endpoints REST custom (modération générique) 
    ---------------- */

    public function register_rest_routes()
    {
        register_rest_route(self::REST_NS, '/moderation/(?P<type>[a-z0-9_\/-]+)/(?P<id>\d+)/accept', [
            'methods'             => 'POST',
            'permission_callback' => function (\WP_REST_Request $req) {
                $type = sanitize_key($req['type']);
                $cap  = $this->get_publish_cap_for_type($type);
                return $cap ? current_user_can($cap) : false;
            },
            'callback'            => function (\WP_REST_Request $req) {
                $type = sanitize_key($req['type']);
                $id   = (int)$req['id'];
                $post = get_post($id);
                if (!$post || $post->post_type !== $type) {
                    return new \WP_Error('not_found', 'Contenu introuvable', ['status' => 404]);
                }
                $updated = wp_update_post(['ID' => $id, 'post_status' => 'publish'], true);
                if (is_wp_error($updated)) return $updated;
                return new \WP_REST_Response(['ok' => true, 'id' => $id, 'status' => 'publish'], 200);
            },
            'args' => [
                'type' => ['type' => 'string', 'required' => true],
                'id'   => ['type' => 'integer', 'required' => true],
            ],
        ]);

        register_rest_route(self::REST_NS, '/moderation/(?P<type>[a-z0-9_\/-]+)/(?P<id>\d+)/reject', [
            'methods'             => 'POST',
            'permission_callback' => function (\WP_REST_Request $req) {
                $type = sanitize_key($req['type']);
                $cap  = $this->get_publish_cap_for_type($type);
                return $cap ? current_user_can($cap) : false;
            },
            'callback'            => function (\WP_REST_Request $req) {
                $type = sanitize_key($req['type']);
                $id   = (int)$req['id'];
                $post = get_post($id);
                if (!$post || $post->post_type !== $type) {
                    return new \WP_Error('not_found', 'Contenu introuvable', ['status' => 404]);
                }
                $updated = wp_update_post(['ID' => $id, 'post_status' => self::STATUS_REJECTED], true);
                if (is_wp_error($updated)) return $updated;
                return new \WP_REST_Response(['ok' => true, 'id' => $id, 'status' => self::STATUS_REJECTED], 200);
            },
            'args' => [
                'type' => ['type' => 'string', 'required' => true],
                'id'   => ['type' => 'integer', 'required' => true],
            ],
        ]);
    }

    private function get_publish_cap_for_type($type)
    {
        if ($type === 'post') return 'publish_posts';
        if ($type === self::CPT_OFFRES) return 'publish_offres';
        if ($type === self::CPT_ACTIVITES) return 'publish_activites';
        return null;
    }
}

new IM_Interface_Membres();
