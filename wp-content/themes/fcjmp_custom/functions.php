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
// FORCE DEV MODE (Application Passwords)
// =======================

add_filter('wp_is_application_passwords_available', '__return_true');



// Filtre pour forcer l'enqueue des assets
add_filter('im_force_enqueue_assets', function ($force, $post) {
    if (is_page('espace-membre')) { // par ID ou slug si tu veux
        return true;
    }
    return $force;
}, 10, 2);

// =======================
// ENQUEUE SCRIPTS POUR LA PAGE MEMBRES
function fcjmp_enqueue_members_script()
{
    // On ne charge le script que sur le template des membres
    if (!is_page_template('page-membres.php')) { // adapte le nom du fichier
        return;
    }

    wp_enqueue_script(
        'fcjmp-members',
        get_template_directory_uri() . '/assets/js/members.js',
        [],
        '1.0.0',
        true
    );

    wp_localize_script('fcjmp-members', 'fcjmpMembers', [
        'endpoint'            => rest_url('fcjmp/v1/members'),
        'templateDirectoryUri' => get_template_directory_uri(),
    ]);
}
add_action('wp_enqueue_scripts', 'fcjmp_enqueue_members_script');
