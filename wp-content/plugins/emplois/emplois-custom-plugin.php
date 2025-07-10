<?php

/**
 * Plugin Name: Offres emplois – Interface Membres
 * Description: Crée un rôle « Membre » et un CPT « offres », avec interface simplifiée et validation par l’admin.
 * Version: 1.0
 * Author: Votre Nom
 * Text Domain: oj-offres
 */

// À l'activation : création du rôle “Membre”
function oj_activer_plugin()
{
    add_role(
        'membre',
        'Membre',
        array(
            'read'            => true,
            'edit_posts'      => true,
            'delete_posts'    => false,
            'publish_posts'   => false,
            'upload_files'    => false,
        )
    );
}
register_activation_hook(__FILE__, 'oj_activer_plugin');

// À la désactivation : suppression du rôle
function oj_desactiver_plugin()
{
    remove_role('membre');
}
register_deactivation_hook(__FILE__, 'oj_desactiver_plugin');

// 1. Enregistrement du CPT “offres”
function oj_creer_cpt_offres()
{
    register_post_type('offres', array(
        'label'           => 'Offres d’emploi',
        'public'          => true,
        'show_ui'         => true,
        'capability_type' => 'post',
        'map_meta_cap'    => true,
        'has_archive'     => true,
        'rewrite'         => array('slug' => 'offres'),
        'supports'        => array('title', 'editor'),
        'show_in_menu'    => true,
    ));
}
add_action('init', 'oj_creer_cpt_offres');

// 2. Redirection automatique vers “Ajouter une offre” pour les Membres
function oj_rediriger_membres_interface()
{
    if (current_user_can('membre') && is_admin()) {
        $screen = get_current_screen();
        if ($screen && $screen->base === 'dashboard') {
            wp_redirect(admin_url('post-new.php?post_type=offres'));
            exit;
        }
    }
}
add_action('admin_init', 'oj_rediriger_membres_interface');

// 3. Masquer les autres menus pour les Membres
function oj_cacher_menus_membres()
{
    if (current_user_can('membre')) {
        remove_menu_page('index.php');       // Tableau de bord
        remove_menu_page('edit.php');        // Articles
        remove_menu_page('upload.php');      // Médias
        remove_menu_page('tools.php');       // Outils
        remove_menu_page('plugins.php');     // Extensions
        remove_menu_page('edit-comments.php'); // Commentaires
        remove_menu_page('themes.php');      // Apparence
        remove_menu_page('users.php');       // Utilisateurs
        remove_menu_page('options-general.php'); // Réglages
    }
}
add_action('admin_menu', 'oj_cacher_menus_membres', 999);

// 4. Forcer le statut “En attente de relecture” pour leurs offres
function oj_forcer_etat_pending($post_data)
{
    if (current_user_can('membre') && $post_data['post_type'] === 'offres') {
        $post_data['post_status'] = 'pending';
    }
    return $post_data;
}
add_filter('wp_insert_post_data', 'oj_forcer_etat_pending', 10, 1);


function oj_desactiver_admin_bar_pour_membres($show_admin_bar)
{
    if (current_user_can('membre')) {
        return false;
    }
    return $show_admin_bar;
}
add_filter('show_admin_bar', 'oj_desactiver_admin_bar_pour_membres', 10, 1);
