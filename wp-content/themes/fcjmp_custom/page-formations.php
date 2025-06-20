<?php

/**
 * Template Name: Formation
 * Description: Affiche les articles tagués "formation"
 */

get_header();

// Pagination
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// WP_Query pour récupérer les articles avec le tag "formation"
$formations_query = new WP_Query(array(
    'category_name'   => 'Formation',
    'posts_per_page'  => 4,
    'meta_key'        => 'date_formation',
    'orderby'         => 'meta_value',
    'order'           => 'ASC',
    'meta_type'       => 'DATE',
));
?>

<div class="hero-banner hero-green">
    <h2 class="hero-banner_text hero-banner_text-green">Formations</h2>
</div>

<section id="formations" class="section">
    <div class="content">
        <h3 class="title title-medium">Toutes nos formations à venir:</h3>

        <div class="card">
            <?php if ($formations_query->have_posts()) : ?>
                <?php while ($formations_query->have_posts()) : $formations_query->the_post(); ?>
                    <div class="card-item">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="card-thumbnail">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="card-section">
                            <h2 class="card-title"><?php the_title(); ?></h2>
                            <div class="card-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 20, '…'); ?>
                            </div>
                        </div>

                        <a href="<?php the_permalink(); ?>"
                            rel="noopener noreferrer"
                            class="card-link">Plus d'infos!</a>
                    </div>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p>Aucun article trouvé pour le tag “formation”.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            echo paginate_links(array(
                'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'total'     => $formations_query->max_num_pages,
                'current'   => max(1, get_query_var('paged')),
                'format'    => '?paged=%#%',
                'prev_text' => '<<',
                'next_text' => '>>',
            ));
            ?>
        </div>
    </div>
</section>

<?php
get_footer();
?>