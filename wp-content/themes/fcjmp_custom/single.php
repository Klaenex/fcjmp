<?php get_header(); ?>

<main class="single-article container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <header class="article-header">
                    <h1 class="article-title"><?php the_title(); ?></h1>
                    <div class="article-meta">
                        <span class="article-author">
                            Publié par <?php the_author_posts_link(); ?>
                        </span>
                        <span class="article-date">
                            le <?php echo get_the_date(); ?>
                        </span>
                        <span class="article-categories">
                            dans <?php the_category(', '); ?>
                        </span>
                    </div>
                </header>

                <div class="article-content">
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

            <nav class="post-navigation">
                <div class="nav-previous">
                    <?php previous_post_link('%link', '← Article précédent : %title'); ?>
                </div>
                <div class="nav-next">
                    <?php next_post_link('%link', 'Article suivant : %title →'); ?>
                </div>
            </nav>

        <?php endwhile;
    else : ?>
        <p>Aucun contenu disponible.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>