<?php get_header(); ?>

<main class="single-article container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>



                <div class="article-content">
                    <h1 class="article-title"><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </div>

                <?php
                $tags = get_the_tags();
                if ($tags) :
                ?>
                    <footer class="article-tags">
                        <span>Mots-clés : </span>
                        <?php the_tags('', ', ', ''); ?>
                    </footer>
                <?php endif; ?>

            </article>



        <?php endwhile;
    else : ?>
        <p>Aucun contenu disponible.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>