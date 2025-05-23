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

// Récupérer les catégories sélectionnées (sous forme de tableau)
$selected_categories = isset($_GET['categorie']) ? explode(',', sanitize_text_field($_GET['categorie'])) : [];

// Pagination sécurisée
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
?>

<div class="hero-banner hero-green">
    <h2 class="hero-banner_text hero-banner_text-green">Actualités</h2>
</div>

<section id="actualites" class="section">
    <div class="content">
        <h3 class="title title-medium">Les dernières nouvelles du secteur</h3>

        <!-- Filtres par boutons -->
        <div class="filter-bar" style="margin-bottom: 2rem;">
            <div class="filter-categories">
                <?php
                $allowed_slugs = array('emplois', 'actualité', 'BDL', 'formation');
                $categories = get_categories(array(
                    'taxonomy'   => 'category',
                    'hide_empty' => true,
                    'slug'       => $allowed_slugs,
                ));

                $current_url = get_permalink();

                foreach ($categories as $category) {
                    $is_active = in_array($category->slug, $selected_categories);
                    $url_categories = $selected_categories;

                    if ($is_active) {
                        // On retire la catégorie si elle est déjà sélectionnée (toggle off)
                        $url_categories = array_diff($url_categories, array($category->slug));
                    } else {
                        // On l'ajoute (toggle on)
                        $url_categories[] = $category->slug;
                    }

                    $url = add_query_arg(array(
                        'categorie' => implode(',', $url_categories)
                    ), $current_url);

                    $active = $is_active ? 'active' : '';
                    echo '<a href="' . esc_url($url) . '" class="button button-filter ' . $active . '">' . esc_html($category->name) . '</a> ';
                }

                if (!empty($selected_categories)) {
                    echo '<a href="' . esc_url($current_url) . '" class="button button-filter reset">
                        ✕
                    </a>';
                }
                ?>
            </div>
        </div>

        <?php
        $args = array(
            'posts_per_page' => 12,
            'paged' => $paged,
        );

        if (!empty($selected_categories)) {
            $args['category_name'] = implode(',', $selected_categories);
        } else {
            $args['category_name'] = 'emplois,actualité,formation,bdl';
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
                            $thumbnail_class = has_category('Emplois') ? 'card-thumbnail card-logo' : 'card-thumbnail';
                        ?>
                            <div class="<?php echo $thumbnail_class; ?>">
                                <?php echo get_the_post_thumbnail(get_the_ID(), 'medium'); ?>
                            </div>
                            <?php
                        } else {
                            $first_img = get_first_image_in_post(get_the_ID());
                            if ($first_img) {
                            ?>
                                <div class="card-thumbnail">
                                    <img src="<?php echo esc_url($first_img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                                </div>
                        <?php
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

            <?php
                wp_reset_postdata();
            else :
                echo '<p>Aucun article trouvé.</p>';
            endif;
            ?>
        </div>
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
    </div>
</section>

<?php
get_footer();
?>