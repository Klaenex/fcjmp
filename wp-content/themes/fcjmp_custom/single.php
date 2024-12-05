<?php get_header(); ?>

<main class="single-article">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
    ?>
            <article>
                <h1><?php the_title(); ?></h1>
                <div class="content">
                    <?php the_content(); ?>
                </div>
            </article>
    <?php
        endwhile;
    else :
        echo '<p>Aucun contenu disponible.</p>';
    endif;
    ?>
</main>

<?php get_footer(); ?>