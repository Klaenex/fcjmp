<?php
/*
Template Name: Actualités
*/

get_header();
?>

<div class="hero-banner hero-green">
    <h2 class="hero-banner_text hero-banner_text-green">Actualités</h2>
</div>

<section id="actualites" class="section">
    <div class="content">

        <h3 class="title title-medium">Les dernières nouvelles du secteur</h3>

        <!-- Formulaire de recherche -->
        <form role="search" method="get" action="<?php echo home_url('/'); ?>">
            <input type="text" name="s" placeholder="Rechercher des actualités..." value="<?php echo get_search_query(); ?>">
            <input type="hidden" name="post_type" value="post">
            <input type="hidden" name="category_name" value="actualité">
            <button type="submit">Rechercher</button>
        </form>

        <?php
        $args = array(
            'category_name' => 'actualité', // Assurez-vous que le slug de la catégorie est correct
            'posts_per_page' => 12,
            'paged' => get_query_var('paged')
        );

        if (get_search_query()) {
            $args['s'] = get_search_query();
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
                        } ?>
                        <div class="card-section">
                            <h2 class="card-title"><?php echo get_the_title(); ?></h2>
                            <p><?php the_excerpt(); ?></p>

                        </div>
                        <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="card-link">Lire l'article</a>
                    </div>
            <?php
                endwhile;

                // Pagination
                the_posts_pagination();

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