<?php

/**
 * Plugin Name: Interface Membres (React)
 * Description: Espace membre React permettant de soumettre et modérer plusieurs types de contenus (Articles, Offres d’emplois, Activités).
 * Version:     1.0.0
 * Author:      Vincent C.
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
    const STATUS_EXPIRED  = 'expired';
    const SHORTCODE       = 'interface_membres';
    const HANDLE          = 'interface-membres-app';
    const REST_NS         = 'im/v1';

    private $content_types = [
        self::CPT_OFFRES => [
            'label'           => 'Offres d’emplois',
            'rest_base'       => self::CPT_OFFRES,
            'cap_publish'     => 'publish_offres',
            'cap_edit_others' => 'edit_others_offres',
            'supports'        => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin'      => false,
        ],
        self::CPT_ACTIVITES => [
            'label'           => 'Activités',
            'rest_base'       => self::CPT_ACTIVITES,
            'cap_publish'     => 'publish_activites',
            'cap_edit_others' => 'edit_others_activites',
            'supports'        => ['title', 'editor', 'excerpt', 'thumbnail'],
            'is_builtin'      => false,
        ],
    ];


    public function __construct()
    {
        register_activation_hook(__FILE__,  [$this, 'on_activate']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivate']);

        add_action('init',                    [$this, 'register_cpts']);
        add_action('init',                    [$this, 'register_rejected_status']);
        add_action('init',                    [$this, 'register_expired_status']);
        add_action('init',                    [$this, 'register_meta']);
        add_action('init',                    [$this, 'ensure_role_caps']);
        add_filter('display_post_states',     [$this, 'show_custom_status_label'], 10, 2);

        add_shortcode(self::SHORTCODE,        [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts',      [$this, 'maybe_enqueue_assets']);

        add_action('rest_api_init',           [$this, 'register_rest_routes']);

        // Cron pour expiration
        add_action('im_expire_offres_daily',  [$this, 'process_expired_offres']);

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
        $this->register_expired_status();
        $this->register_meta();
        flush_rewrite_rules();

        // schedule daily event for expiration
        if (! wp_next_scheduled('im_expire_offres_daily')) {
            wp_schedule_event(time(), 'daily', 'im_expire_offres_daily');
        }
    }

    public function on_deactivate()
    {
        // unschedule event
        $timestamp = wp_next_scheduled('im_expire_offres_daily');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'im_expire_offres_daily');
        }
        flush_rewrite_rules();
    }

    /* ---------------- Rôle & Capacités ---------------- */

    public function ensure_role_caps()
    {
        $base_caps = [
            'read'                   => true,
            'upload_files'           => true,
            'edit_posts'             => false,
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

        // Capacities for 'offres' CPT - members can create/edit but not publish by default
        $offres_caps = [
            'read_offre'                => true,
            'edit_offre'                => true,
            'delete_offre'              => false,
            'edit_offres'               => true,
            'edit_others_offres'        => false,
            'publish_offres'            => false,
            'create_offres'             => true,
            'edit_published_offres'     => true,
            'delete_offres'             => false,
            'delete_others_offres'      => false,
            'delete_published_offres'   => false,
            'read_private_offres'       => false,
        ];
        if ($role) {
            foreach ($offres_caps as $k => $v) {
                $v ? $role->add_cap($k) : $role->remove_cap($k);
            }
        }

        // Capacities for 'activites' CPT
        $activites_caps = [
            'read_activite'                => true,
            'edit_activite'                => true,
            'delete_activite'              => false,
            'edit_activites'               => true,
            'edit_others_activites'        => false,
            'publish_activites'            => false,
            'create_activites'             => true,
            'edit_published_activites'     => true,
            'delete_activites'             => false,
            'delete_others_activites'      => false,
            'delete_published_activites'   => false,
            'read_private_activites'       => false,
        ];
        if ($role) {
            foreach ($activites_caps as $k => $v) {
                $v ? $role->add_cap($k) : $role->remove_cap($k);
            }
        }

        // Ensure admins & editors have all needed caps (including publish)
        $roles_to_grant = ['administrator', 'editor'];
        $admin_caps = [
            // Offres caps (including publish)
            'read_offre',
            'edit_offre',
            'delete_offre',
            'edit_offres',
            'edit_others_offres',
            'publish_offres',
            'create_offres',
            'edit_published_offres',
            'delete_offres',
            'delete_others_offres',
            'delete_published_offres',
            'read_private_offres',
            // Activites caps
            'read_activite',
            'edit_activite',
            'delete_activite',
            'edit_activites',
            'edit_others_activites',
            'publish_activites',
            'create_activites',
            'edit_published_activites',
            'delete_activites',
            'delete_others_activites',
            'delete_published_activites',
            'read_private_activites',
        ];
        foreach ($roles_to_grant as $rname) {
            $r = get_role($rname);
            if (!$r) continue;
            foreach ($admin_caps as $cap) {
                if (!$r->has_cap($cap)) $r->add_cap($cap);
            }
        }
    }

    /* ---------------- CPT & Statut ---------------- */

    public function register_cpts()
    {
        // CPT Offres d’emploi
        register_post_type(self::CPT_OFFRES, [
            'labels' => [
                'name'          => __('Offres d’emplois', 'interface-membres'),
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
            'capabilities' => [
                'edit_post'           => 'edit_offre',
                'read_post'           => 'read_offre',
                'delete_post'         => 'delete_offre',
                'edit_posts'          => 'edit_offres',
                'edit_others_posts'   => 'edit_others_offres',
                'publish_posts'       => 'publish_offres',
                'read_private_posts'  => 'read_private_offres',
                'create_posts'        => 'create_offres',
                'delete_posts'        => 'delete_offres',
            ],
        ]);

        // CPT Activités
        register_post_type(self::CPT_ACTIVITES, [
            'labels' => [
                'name'          => __('Activités', 'interface-membres'),
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
            'capabilities' => [
                'edit_post'           => 'edit_activite',
                'read_post'           => 'read_activite',
                'delete_post'         => 'delete_activite',
                'edit_posts'          => 'edit_activites',
                'edit_others_posts'   => 'edit_others_activites',
                'publish_posts'       => 'publish_activites',
                'read_private_posts'  => 'read_private_activites',
                'create_posts'        => 'create_activites',
                'delete_posts'        => 'delete_activites',
            ],
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

    public function register_expired_status()
    {
        register_post_status(self::STATUS_EXPIRED, [
            'label'                     => _x('Expiré', 'post status', 'interface-membres'),
            'public'                    => false,
            'show_in_admin_status_list' => true,
            'show_in_rest'              => true,
            'label_count'               => _n_noop('Expiré <span class="count">(%s)</span>', 'Expirés <span class="count">(%s)</span>', 'interface-membres'),
        ]);
    }

    public function show_custom_status_label($states, $post)
    {
        if ($post->post_status === self::STATUS_REJECTED) {
            $states['rejected'] = __('Rejeté', 'interface-membres');
        }
        if ($post->post_status === self::STATUS_EXPIRED) {
            $states['expired'] = __('Expiré', 'interface-membres');
        }
        return $states;
    }

    /* ---------------- Register meta ---------------- */

    public function register_meta()
    {
        $meta_keys = [
            'im_off_type',
            'im_off_type_prec',
            'im_off_regime',
            'im_off_regime_prec',
            'im_off_zone',
            'im_off_lieu_prec',
            'im_off_desc_asbl',
            'im_off_desc_poste',
            'im_off_missions',
            'im_off_qualifs',
            'im_off_competences',
            'im_off_conditions',
            'im_off_infos',
            'im_off_candidature_url',
            'im_off_candidature_email',
            'im_off_candidature_tel',
            'im_off_date_limite'
        ];

        foreach ($meta_keys as $key) {
            register_post_meta(self::CPT_OFFRES, $key, [
                'single' => true,
                'show_in_rest' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ]);
        }
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
            'restUrl'      => esc_url_raw(rest_url()),
            'nonce'        => wp_create_nonce('wp_rest'),
            'currentUser'  => [
                'id'    => $current_user->ID,
                'name'  => $current_user->display_name,
                'roles' => $current_user->roles,
            ],
            'status'       => [
                'draft'    => 'draft',
                'pending'  => 'pending',
                'publish'  => 'publish',
                'rejected' => self::STATUS_REJECTED,
                'expired'  => self::STATUS_EXPIRED,
            ],
            'types'        => $types,
            'siteUrl'      => home_url('/'),
            'restNamespace' => self::REST_NS,
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

        // Endpoint pour la soumission (utilisé par les membres)
        register_rest_route(self::REST_NS, '/submit', [
            'methods' => 'POST',
            'permission_callback' => function () {
                return is_user_logged_in() && current_user_can('create_offres');
            },
            'callback' => [$this, 'rest_submit_offre'],
        ]);
    }

    public function rest_submit_offre(\WP_REST_Request $req)
    {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return new \WP_Error('not_logged', 'Utilisateur non connecté', ['status' => 401]);
        }

        $params = $req->get_json_params();
        if (!is_array($params)) $params = [];

        // sanitize
        $title = sanitize_text_field($params['title'] ?? '');
        $content = isset($params['content']) ? wp_kses_post($params['content']) : '';
        $meta = is_array($params['meta'] ?? null) ? $params['meta'] : [];

        // Force author and status
        $postarr = [
            'post_title'   => $title ?: '(Sans titre)',
            'post_content' => $content,
            'post_type'    => self::CPT_OFFRES,
            'post_status'  => 'pending',
            'post_author'  => $user->ID,
        ];

        $post_id = wp_insert_post($postarr, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // allowed meta keys
        $allowed_meta = [
            'im_off_type',
            'im_off_type_prec',
            'im_off_regime',
            'im_off_regime_prec',
            'im_off_zone',
            'im_off_lieu_prec',
            'im_off_desc_asbl',
            'im_off_desc_poste',
            'im_off_missions',
            'im_off_qualifs',
            'im_off_competences',
            'im_off_conditions',
            'im_off_infos',
            'im_off_candidature_url',
            'im_off_candidature_email',
            'im_off_candidature_tel',
            'im_off_date_limite'
        ];

        foreach ($allowed_meta as $key) {
            if (isset($meta[$key])) {
                $value = $meta[$key];
                if ($key === 'im_off_candidature_email') {
                    update_post_meta($post_id, $key, sanitize_email($value));
                } elseif ($key === 'im_off_candidature_url') {
                    update_post_meta($post_id, $key, esc_url_raw($value));
                } else {
                    update_post_meta($post_id, $key, sanitize_text_field($value));
                }
            }
        }

        return rest_ensure_response(['ok' => true, 'id' => $post_id]);
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
            self::CPT_OFFRES    => 'publish_offres',
            self::CPT_ACTIVITES => 'publish_activites',
            default             => null,
        };
    }

    /* ---------------- Process expired offres ---------------- */

    public function process_expired_offres()
    {
        $today = current_time('Y-m-d'); // WP timezone-aware

        $args = [
            'post_type'      => self::CPT_OFFRES,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => 'im_off_date_limite',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ],
            ],
        ];

        $q = new WP_Query($args);
        if (empty($q->posts)) {
            return;
        }

        foreach ($q->posts as $post_id) {
            $date = get_post_meta($post_id, 'im_off_date_limite', true);
            if (! $date) continue;
            $d = date_create_from_format('Y-m-d', $date);
            if (! $d) continue;
            $d_str = $d->format('Y-m-d');
            if ($d_str <= $today) {
                wp_update_post(['ID' => $post_id, 'post_status' => self::STATUS_EXPIRED]);
                error_log("[Interface Membres] Offre #{$post_id} expirée ({$date}).");
            }
        }
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

    /**
     * Assure le chargement de l'éditeur et du plugin wplink pour le CPT "offres"
     * (à placer DANS la classe IM_Interface_Membres)
     */
    public function admin_enqueue_editor_scripts($hook)
    {
        if (! function_exists('get_current_screen')) return;

        $screen = get_current_screen();
        if (! $screen) return;

        // Ne charger que pour l'écran d'édition du CPT "offres"
        if ($screen->post_type !== self::CPT_OFFRES) return;

        // Charge l'éditeur Wordpress (TinyMCE + dépendances)
        if (function_exists('wp_enqueue_editor')) {
            wp_enqueue_editor();
        }

        // Assure le plugin wplink + color picker
        wp_enqueue_script('wplink');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
}




new IM_Interface_Membres();
