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

    private $content_types = [
        'post' => [
            'label' => 'Articles',
            'rest_base' => 'posts',
            'cap_publish' => 'publish_posts',
            'cap_edit_others' => 'edit_others_posts',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin' => true,
        ],
        self::CPT_OFFRES => [
            'label' => 'Offres d’emplois',
            'rest_base' => self::CPT_OFFRES,
            'cap_publish' => 'publish_offres',
            'cap_edit_others' => 'edit_others_offres',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin' => false,
        ],
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

        // 🔒 Verrouillage wp-admin pour les membres
        add_action('admin_init',              [$this, 'block_admin_for_members'], 1);
        add_filter('show_admin_bar',          [$this, 'hide_admin_bar_for_members'], 10, 1);
        add_filter('login_redirect',          [$this, 'redirect_members_after_login'], 10, 3);
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
        flush_rewrite_rules();
    }

    /* ---------------- Rôle & Capacités ---------------- */

    public function ensure_role_caps()
    {
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

        $offres_caps = [
            'edit_offre'              => true,
            'read_offre'              => true,
            'delete_offre'            => false,
            'edit_offres'             => true,
            'edit_others_offres'      => false,
            'publish_offres'          => false,
        ];
        if ($role) {
            foreach ($offres_caps as $k => $v) {
                $v ? $role->add_cap($k) : $role->remove_cap($k);
            }
        }

        $activites_caps = [
            'edit_activite'              => true,
            'read_activite'              => true,
            'delete_activite'            => false,
            'edit_activites'             => true,
            'edit_others_activites'      => false,
            'publish_activites'          => false,
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
        register_post_type(self::CPT_OFFRES, [
            'labels' => [
                'name' => __('Offres d’emplois', 'interface-membres'),
                'singular_name' => __('Offre d’emploi', 'interface-membres'),
            ],
            'public'       => true,
            'show_ui'      => true,
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'offres'],
            'supports'     => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest' => true,
            'rest_base'    => self::CPT_OFFRES,
            'map_meta_cap' => true,
            'capability_type' => ['offre', 'offres'],
        ]);

        register_post_type(self::CPT_ACTIVITES, [
            'labels' => [
                'name' => __('Activités', 'interface-membres'),
                'singular_name' => __('Activité', 'interface-membres'),
            ],
            'public'       => true,
            'show_ui'      => true,
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
            'show_in_admin_status_list' => true,
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
        if (!is_singular()) return;
        global $post;
        if (!$post) return;

        $has_sc = has_shortcode($post->post_content, self::SHORTCODE);
        $uses_template = (
            is_page_template('page-espacemembre.php')
            || is_page_template('page-espacemembre-react.php')
        );

        if ($has_sc || $uses_template) {
            $this->enqueue_vite_assets_and_context();
        }
    }

    private function enqueue_vite_assets_and_context()
    {
        $manifest_path = plugin_dir_path(__FILE__) . 'dist/manifest.json';
        if (!file_exists($manifest_path)) {
            $manifest_path = plugin_dir_path(__FILE__) . 'dist/.vite/manifest.json';
        }

        if (!file_exists($manifest_path)) {
            error_log('[Interface Membres] manifest.json introuvable.');
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);
        if (!$manifest || !is_array($manifest)) return;

        $entry = null;
        foreach ($manifest as $file => $meta) {
            if (!empty($meta['isEntry'])) {
                $entry = $meta;
                break;
            }
        }
        if (!$entry) return;

        if (!empty($entry['css'])) {
            foreach ($entry['css'] as $css) {
                wp_enqueue_style(self::HANDLE . '-css-' . md5($css), plugins_url('dist/' . $css, __FILE__));
            }
        }

        wp_enqueue_script(self::HANDLE, plugins_url('dist/' . $entry['file'], __FILE__), [], null, true);

        $current_user = wp_get_current_user();
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

        wp_localize_script(self::HANDLE, 'IMAppConfig', [
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
        ]);
    }

    /* ---------------- Endpoints REST custom ---------------- */

    public function register_rest_routes()
    {
        register_rest_route(self::REST_NS, '/moderation/(?P<type>[a-z0-9_\/-]+)/(?P<id>\d+)/accept', [
            'methods'             => 'POST',
            'permission_callback' => fn($req) => current_user_can($this->get_publish_cap_for_type($req['type'])),
            'callback'            => fn($req) => $this->moderate($req['type'], (int)$req['id'], 'publish'),
        ]);
        register_rest_route(self::REST_NS, '/moderation/(?P<type>[a-z0-9_\/-]+)/(?P<id>\d+)/reject', [
            'methods'             => 'POST',
            'permission_callback' => fn($req) => current_user_can($this->get_publish_cap_for_type($req['type'])),
            'callback'            => fn($req) => $this->moderate($req['type'], (int)$req['id'], self::STATUS_REJECTED),
        ]);
    }

    private function moderate($type, $id, $status)
    {
        $post = get_post($id);
        if (!$post || $post->post_type !== $type) {
            return new \WP_Error('not_found', 'Contenu introuvable', ['status' => 404]);
        }
        $updated = wp_update_post(['ID' => $id, 'post_status' => $status], true);
        if (is_wp_error($updated)) return $updated;
        return new \WP_REST_Response(['ok' => true, 'id' => $id, 'status' => $status], 200);
    }

    private function get_publish_cap_for_type($type)
    {
        return match ($type) {
            'post' => 'publish_posts',
            self::CPT_OFFRES => 'publish_offres',
            self::CPT_ACTIVITES => 'publish_activites',
            default => null,
        };
    }

    /* ---------------- Restrictions wp-admin ---------------- */

    private function current_user_is_member()
    {
        if (!is_user_logged_in()) return false;
        $u = wp_get_current_user();
        return in_array(self::ROLE, (array)$u->roles, true);
    }

    public function block_admin_for_members()
    {
        if (!$this->current_user_is_member()) return;

        $is_ajax = defined('DOING_AJAX') && DOING_AJAX;
        $is_rest = defined('REST_REQUEST') && REST_REQUEST;
        if ($is_ajax || $is_rest) return;

        wp_safe_redirect(home_url('/espace-membre/'));
        exit;
    }

    public function hide_admin_bar_for_members($show)
    {
        return $this->current_user_is_member() ? false : $show;
    }

    public function redirect_members_after_login($redirect_to, $requested, $user)
    {
        if ($user instanceof \WP_User && in_array(self::ROLE, $user->roles, true)) {
            return home_url('/espace-membre/');
        }
        return $redirect_to;
    }
}

new IM_Interface_Membres();
