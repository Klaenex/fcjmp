<?php

/**
 * Template Name: Espace membre React
 */
get_header();
?>

<main id="primary" class="site-main">

    <section class="section section-green ">
        <div class="section-green_wrap">
            <h1 class="title title-big">Espace membre</h1>
        </div>
    </section>
    <section class="content">
        <?php
        // Exécute le shortcode explicitement :
        echo do_shortcode('[interface_membres]');
        ?>
    </section>
</main>

<?php get_footer(); ?>