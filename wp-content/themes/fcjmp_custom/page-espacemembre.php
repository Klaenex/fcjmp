<?php

/**
 * Template Name: Espace membre React
 */
get_header();
?>

<main id="primary" class="site-main">
    <article class="entry">
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        <div class="entry-content">
            <?php
            // Exécute le shortcode explicitement :
            echo do_shortcode('[interface_membres]');
            ?>
        </div>
    </article>
</main>

<?php get_footer(); ?>