<?php
// =======================
// ENQUEUE STYLES & SCRIPTS
// =======================

function fcjmp_enqueue_assets()
{
    // Feuilles de style globales
    wp_enqueue_style(
        'fcjmp-style',
        get_stylesheet_uri()
    );
    wp_enqueue_style(
        'fcjmp-index',
        get_template_directory_uri() . '/assets/css/index.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/index.css'),
        'all'
    );

    // Scripts JS globaux
    wp_enqueue_script(
        'fcjmp-custom-js',
        get_template_directory_uri() . '/assets/js/custom.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/custom.js'),
        true
    );

    // Transfert de données PHP vers JS global
    wp_localize_script(
        'fcjmp-custom-js',
        'themeData',
        array(
            'themeUrl' => get_template_directory_uri(),
        )
    );
}
add_action('wp_enqueue_scripts', 'fcjmp_enqueue_assets');


// =======================
// SETUP DU THÈME
// =======================

add_action('after_setup_theme', 'fcjmp_theme_setup');
function fcjmp_theme_setup()
{
    register_nav_menus(array(
        'fcjmp_basic-guest'  => __('Menu Invité', 'fcjmp'),
        'fcjmp_basic-member' => __('Menu Membre', 'fcjmp'),
    ));
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 400,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}


// =======================
// NEWSLETTER
// =======================

function fcjmp_handle_newsletter_subscription()
{
    if (isset($_POST['newsletter_email']) && is_email($_POST['newsletter_email'])) {
        $email = sanitize_email($_POST['newsletter_email']);
        // ... traitement ...
        wp_redirect(home_url('/merci-pour-votre-inscription'));
        exit;
    }
}
add_action('admin_post_nopriv_subscribe_newsletter', 'fcjmp_handle_newsletter_subscription');
add_action('admin_post_subscribe_newsletter',   'fcjmp_handle_newsletter_subscription');


// =======================
// REDIRECTIONS CONNEXION / DÉCONNEXION
// =======================

function fcjmp_custom_login_redirect($redirect_to, $request, $user)
{
    if (isset($user->roles) && is_array($user->roles)) {
        return in_array('administrator', $user->roles)
            ? admin_url()
            : home_url();
    }
    return $redirect_to;
}
add_filter('login_redirect', 'fcjmp_custom_login_redirect', 10, 3);

function fcjmp_custom_logout_redirect()
{
    wp_redirect(home_url());
    exit;
}
add_action('wp_logout', 'fcjmp_custom_logout_redirect');


// =======================
// FORCE DEV MODE
// =======================

add_filter('wp_is_application_passwords_available', '__return_true');


// =======================
// ESPACE-MEMBRE REACT (dev ou prod)
// =======================

function fcjmp_enqueue_espace_membre_react()
{
    // Mode DEV : on injecte Vite + ES module + les variables JS
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // 1) HMR client Vite
        wp_enqueue_script(
            'vite-client',
            'http://localhost:5173/@vite/client',
            array(),
            null,
            false
        );
        // 2) Entrypoint React en dev
        wp_enqueue_script(
            'fcjmp-react-dev',
            'http://localhost:5173/src/main.jsx',
            array('vite-client'),
            null,
            true
        );
        // 3) On fournit rest_url et nonce même en dev
        wp_localize_script(
            'fcjmp-react-dev',
            'FCJMP_REACT',
            array(
                'rest_url' => esc_url_raw(rest_url()),
                'nonce'    => wp_create_nonce('wp_rest'),
            )
        );
        return;
    }

    // ---- En PRODUCTION : on charge les bundles buildés ----
    $dir = get_template_directory() . '/espace-membre';
    $uri = get_template_directory_uri() . '/espace-membre';

    // CSS build
    $css = $dir . '/index.css';
    if (file_exists($css)) {
        wp_enqueue_style(
            'fcjmp-espace-membre-css',
            $uri . '/index.css',
            array(),
            filemtime($css)
        );
    }

    // JS build
    $js = $dir . '/index.js';
    if (file_exists($js)) {
        wp_enqueue_script(
            'fcjmp-espace-membre-js',
            $uri . '/index.js',
            array(),
            filemtime($js),
            true
        );
        wp_localize_script(
            'fcjmp-espace-membre-js',
            'FCJMP_REACT',
            array(
                'rest_url' => esc_url_raw(rest_url()),
                'nonce'    => wp_create_nonce('wp_rest'),
            )
        );
    } else {
        // Message dans le HTML si build manquant
        echo "<!-- Espace‑membre build non trouvé : lancez 'npm run build' -->";
    }
}
add_action('wp_enqueue_scripts', 'fcjmp_enqueue_espace_membre_react');


// =======================
// Ajout de type="module" pour Vite en dev
// =======================
function fcjmp_add_module_type($tag, $handle, $src)
{
    if (in_array($handle, array('vite-client', 'fcjmp-react-dev'), true)) {
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}
add_filter('script_loader_tag', 'fcjmp_add_module_type', 10, 3);
