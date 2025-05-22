<?php
/*
Template Name: Actualités
*/

get_header();

// Fonction pour extraire la première image d’un article
function get_first_image_in_post($post_id)
{
    $post_content = get_post_field('post_content', $post_id);
    $output = preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $post_content, $matches);
    if (isset($matches['src'])) {
        return $matches['src'];
    }
    return false;
}

// Récupérer la recherche (si elle existe)
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Récupérer la catégorie sélectionnée (si elle existe)
$selected_category = isset($_GET['categorie']) ? sanitize_text_field($_GET['categorie']) : '';

// Pagination sécurisée
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
?>

<div class="hero-banner hero-green">
    <h2 class="hero-banner_text hero-banner_text-green">Actualités</h2>
</div>

<section id="actualites" class="section">
    <div class="content">

        <h3 class="title title-medium">Les dernières nouvelles du secteur</h3>

        <!-- Formulaire de filtre par catégorie -->
        <form method="get" class="filter-form" style="margin-bottom: 2rem;">
            <label for="categorie">Filtrer par catégorie :</label>
            <select name="categorie" id="categorie" onchange="this.form.submit()">
                <option value="">Toutes les catégories</option>
                <?php
                // Récupérer uniquement les catégories "emploi" et "actualité"
                $allowed_slugs = array('emploi', 'actualité');

                $categories = get_categories(array(
                    'taxonomy'   => 'category',
                    'hide_empty' => true,
                    'slug'       => $allowed_slugs,
                ));

                foreach ($categories as $category) {
                    $selected = ($selected_category === $category->slug) ? 'selected' : '';
                    echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
                }
                ?>
            </select>

            <?php if (!empty($search_query)) : ?>
                <input type="hidden" name="s" value="<?php echo esc_attr($search_query); ?>">
            <?php endif; ?>
        </form>

        <?php
        $args = array(
            'posts_per_page' => 12,
            'paged' => $paged,
        );

        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }

        if (!empty($selected_category)) {
            $args['category_name'] = $selected_category;
        } else {
            // Par défaut, afficher les deux catégories
            $args['category_name'] = 'Emplois,actualité,Formation,BDL';
        }

        $actualites_query = new WP_Query($args);
        ?>

        <div class="card">
            <?php
            if ($actualites_query->have_posts()) :
                while ($actualites_query->have_posts()) : $actualites_query->the_post();
            ?>
                    <div class="card-item">
                        <?php
                        if (has_post_thumbnail()) {
                            echo '<div class="card-thumbnail">';
                            echo get_the_post_thumbnail(get_the_ID(), 'medium');
                            echo '</div>';
                        } else {
                            $first_img = get_first_image_in_post(get_the_ID());
                            if ($first_img) {
                                echo '<div class="card-thumbnail">';
                                echo '<img src="' . esc_url($first_img) . '" alt="' . esc_attr(get_the_title()) . '" />';
                                echo '</div>';
                            }
                        }
                        ?>
                        <div class="card-section">
                            <h2 class="card-title"><?php echo get_the_title(); ?></h2>
                        </div>
                        <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="card-link">Lire l'article</a>
                    </div>
                <?php
                endwhile;
                ?>
                <div class="pagination">
                    <?php
                    echo paginate_links(array(
                        'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'total'        => $actualites_query->max_num_pages,
                        'current'      => max(1, get_query_var('paged')),
                        'format'       => '?paged=%#%',
                        'show_all'     => false,
                        'type'         => 'plain',
                        'end_size'     => 2,
                        'mid_size'     => 1,
                        'prev_next'    => true,
                        'prev_text'    => __('<<', 'text-domain'),
                        'next_text'    => __('>>', 'text-domain'),
                    ));
                    ?>
                </div>
            <?php
                wp_reset_postdata();
            else :
                echo '<p>Aucun article trouvé.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<?php
get_footer();
?>