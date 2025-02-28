<?php
// Enqueuer les styles CSS
function fcjmp_enqueue_styles()
{
    // Charger le fichier style.css (par défaut)
    wp_enqueue_style('theme-style', get_stylesheet_uri());

    // Charger le fichier index.css
    wp_enqueue_style('theme-index', get_template_directory_uri() . '/assets/css/index.css', array(), filemtime(get_template_directory() . '/assets/css/index.css'), 'all');
}
add_action('wp_enqueue_scripts', 'fcjmp_enqueue_styles');

// Enqueuer les scripts JavaScript
function fcjmp_enqueue_scripts()
{
    // Charger le fichier JavaScript custom.js
    wp_enqueue_script('theme-custom-js', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), filemtime(get_template_directory() . '/assets/js/custom.js'), true);
}
add_action('wp_enqueue_scripts', 'fcjmp_enqueue_scripts');

// Ajouter le support pour les menus
function mon_theme_setup()
{
    register_nav_menus(array(
        'main-menu' => 'Menu Principal',
    ));
}
add_action('after_setup_theme', 'mon_theme_setup');

// Ajouter le support pour les images mises en avant
add_theme_support('post-thumbnails');

// Ajout du Logo dans l'admin
function theme_support_custom_logo()
{
    add_theme_support('custom-logo', array(
        'height'      => 400,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}
add_action('after_setup_theme', 'theme_support_custom_logo');

// La Newsletter
function handle_newsletter_subscription()
{
    if (isset($_POST['newsletter_email']) && is_email($_POST['newsletter_email'])) {
        $email = sanitize_email($_POST['newsletter_email']);

        wp_redirect(home_url('/merci-pour-votre-inscription'));
        exit;
    }
}
add_action('admin_post_nopriv_subscribe_newsletter', 'handle_newsletter_subscription');
add_action('admin_post_subscribe_newsletter', 'handle_newsletter_subscription');


// Redirection après connexion
function custom_login_redirect($redirect_to, $request, $user)
{
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        } else {
            return home_url(); // Redirige vers la page d'accueil
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);

// Redirection après déconnexion
function custom_logout_redirect()
{
    wp_redirect(home_url());
    exit();
}
add_action('wp_logout', 'custom_logout_redirect');
