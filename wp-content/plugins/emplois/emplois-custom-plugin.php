<?php

/**
 * Plugin Name: Offres emplois – Interface Membres
 * Description: Crée un rôle « Membre » et un CPT « offres », avec interface front-end de soumission, liste et validation par l’admin.
 * Version:     1.0
 * Author:      Votre Nom
 * Text Domain: offres
 */

/**
 * À l'activation : création du rôle “Membre”
 */
function offres_activer_plugin()
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
register_activation_hook(__FILE__, 'offres_activer_plugin');

/**
 * À la désactivation : suppression du rôle “Membre”
 */
function offres_desactiver_plugin()
{
    remove_role('membre');
}
register_deactivation_hook(__FILE__, 'offres_desactiver_plugin');

/**
 * 1. Enregistrement du Custom Post Type “offres”
 */
function offres_creer_cpt_offres()
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
add_action('init', 'offres_creer_cpt_offres');

/**
 * 2. Suppression des auto-drafts créés par les membres
 */
function offres_supprimer_autodrafts_membres()
{
    if (current_user_can('membre') && is_admin()) {
        $args = array(
            'post_type'      => 'offres',
            'post_status'    => 'auto-draft',
            'author'         => get_current_user_id(),
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );
        $autodrafts = get_posts($args);
        foreach ($autodrafts as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
}
add_action('admin_init', 'offres_supprimer_autodrafts_membres');

/**
 * 3. Masquer les menus non pertinents pour les Membres
 */
function offres_cacher_menus_membres()
{
    if (current_user_can('membre')) {
        remove_menu_page('index.php');
        remove_menu_page('edit.php');
        remove_menu_page('upload.php');
        remove_menu_page('tools.php');
        remove_menu_page('plugins.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('themes.php');
        remove_menu_page('users.php');
        remove_menu_page('options-general.php');
    }
}
add_action('admin_menu', 'offres_cacher_menus_membres', 999);

/**
 * 4. Désactiver la barre d’admin en front pour les Membres
 */
function offres_desactiver_admin_bar_pour_membres($show_admin_bar)
{
    if (current_user_can('membre')) {
        return false;
    }
    return $show_admin_bar;
}
add_filter('show_admin_bar', 'offres_desactiver_admin_bar_pour_membres', 10, 1);

/**
 * 5. Shortcode [soumettre_offre] : formulaire de soumission front-end
 */
function offres_formulaire_soumission_offre()
{
    if (! is_user_logged_in() || ! current_user_can('membre')) {
        return '<p>Vous devez être connecté en tant que membre pour soumettre une offre.</p>';
    }

    ob_start();

    if (isset($_POST['offres_offre_nonce']) && wp_verify_nonce($_POST['offres_offre_nonce'], 'soumettre_offre')) {
        $titre   = sanitize_text_field($_POST['offres_titre']);
        $contenu = wp_kses_post($_POST['offres_contenu']);

        $offre_id = wp_insert_post(array(
            'post_type'    => 'offres',
            'post_title'   => $titre,
            'post_content' => $contenu,
            'post_status'  => 'pending',
            'post_author'  => get_current_user_id(),
        ));

        if (! is_wp_error($offre_id)) {
            wp_set_object_terms($offre_id, 'Emplois', 'Emplois', true);
            echo '<div class="offres-message">Merci, votre offre a bien été soumise pour validation.</div>';
        } else {
            echo '<div class="offres-erreur">Une erreur est survenue. Merci de réessayer.</div>';
        }
    }
?>
    <form method="post" class="offres-formulaire-offre">
        <p>
            <label for="offres_titre">Titre de l’offre :</label><br>
            <input type="text" name="offres_titre" id="offres_titre" required style="width:100%;">
        </p>
        <p>
            <label for="offres_contenu">Description :</label><br>
            <textarea name="offres_contenu" id="offres_contenu" rows="8" required style="width:100%;"></textarea>
        </p>
        <?php wp_nonce_field('soumettre_offre', 'offres_offre_nonce'); ?>
        <p><button type="submit">Soumettre l’offre</button></p>
    </form>
<?php

    return ob_get_clean();
}
add_shortcode('soumettre_offre', 'offres_formulaire_soumission_offre');

/**
 * 6. Shortcode [liste_offres_membre] : afficher les offres du membre
 */
function offres_liste_offres_membre()
{
    if (! is_user_logged_in() || ! current_user_can('membre')) {
        return '<p>Vous devez être connecté pour voir vos offres.</p>';
    }

    $args = array(
        'post_type'      => 'offres',
        'author'         => get_current_user_id(),
        'post_status'    => array('pending', 'publish'),
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $offres = get_posts($args);

    ob_start();

    if ($offres) {
        echo '<ul class="offres-liste-offres">';
        foreach ($offres as $offre) {
            echo '<li>';
            echo '<strong>' . esc_html($offre->post_title) . '</strong><br>';
            echo 'Statut : <em>' . esc_html(get_post_status($offre)) . '</em><br>';
            echo '<a href="' . esc_url(get_permalink($offre)) . '" target="_blank">Voir</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Aucune offre soumise pour le moment.</p>';
    }

    return ob_get_clean();
}
add_shortcode('liste_offres_membre', 'offres_liste_offres_membre');
