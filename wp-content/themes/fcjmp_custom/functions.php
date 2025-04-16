<?php
// =======================
// ENQUEUE STYLES & SCRIPTS
// =======================

function fcjmp_enqueue_assets()
{
    // Feuilles de style
    wp_enqueue_style('fcjmp-style', get_stylesheet_uri());
    wp_enqueue_style(
        'fcjmp-index',
        get_template_directory_uri() . '/assets/css/index.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/index.css'),
        'all'
    );

    // Scripts JS
    wp_enqueue_script(
        'fcjmp-custom-js',
        get_template_directory_uri() . '/assets/js/custom.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/custom.js'),
        true
    );

    // Transfert de données PHP vers JS
    wp_localize_script('fcjmp-custom-js', 'themeData', array(
        'themeUrl' => get_template_directory_uri()
    ));
}
add_action('wp_enqueue_scripts', 'fcjmp_enqueue_assets');

// =======================
// SETUP DU THÈME
// =======================

function fcjmp_theme_setup()
{
    // Menus
    register_nav_menus(array(
        'main-menu' => 'Menu Principal',
    ));

    // Image mise en avant
    add_theme_support('post-thumbnails');

    // Logo personnalisé
    add_theme_support('custom-logo', array(
        'height'      => 400,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}
add_action('after_setup_theme', 'fcjmp_theme_setup');

// =======================
// NEWSLETTER
// =======================

function fcjmp_handle_newsletter_subscription()
{
    if (isset($_POST['newsletter_email']) && is_email($_POST['newsletter_email'])) {
        $email = sanitize_email($_POST['newsletter_email']);

        // Ici tu pourrais ajouter un enregistrement en base ou appel à un service tiers

        wp_redirect(home_url('/merci-pour-votre-inscription'));
        exit;
    }
}
add_action('admin_post_nopriv_subscribe_newsletter', 'fcjmp_handle_newsletter_subscription');
add_action('admin_post_subscribe_newsletter', 'fcjmp_handle_newsletter_subscription');

// =======================
// REDIRECTIONS CONNEXION / DÉCONNEXION
// =======================

function fcjmp_custom_login_redirect($redirect_to, $request, $user)
{
    if (isset($user->roles) && is_array($user->roles)) {
        return in_array('administrator', $user->roles) ? admin_url() : home_url();
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
