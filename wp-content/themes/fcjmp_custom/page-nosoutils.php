<?php

/* 
Template Name: nos outils
*/
get_header();

?>
<section class="section section-green ">
    <div class="section-green_wrap">
        <h1 class="title title-big">Nos outils</h1>
    </div>

</section>
<section class="section">

    <div class="content">
        <?php if (is_user_logged_in()):
            $args = array(
                'category_name' => 'Nos outils,Nos outils membre',
                'order' => 'ASC'
            );
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()) {
                while ($the_query->have_posts()) {

                    $the_query->the_post();
        ?>
                    <div class="wrapper wrapper-outils">
                        <?php if (has_post_thumbnail()) { ?>
                            <div>
                                <?php
                                echo the_post_thumbnail(get_the_ID(), 'thumbnail');
                                ?>
                            </div>
                        <?php  } ?>
                        <div class="wrapper wrapper-text">
                            <h2 class="title title-outils"> <?php echo get_the_title(); ?> </h2>
                            <?php $content = apply_filters('the_content', get_the_content());
                            $paragraphs = explode('</p>', $content);

                            if (!empty($paragraphs[0])) {
                                echo $paragraphs[0] . '</p>';
                            }
                            ?>
                            <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="button button-outils">Plus d'infos</a>
                        </div>
                    </div>
                <?php
                }
                wp_reset_postdata();
            } else {
                echo '<p>Aucun article trouvé dans cette catégorie.</p>';
            }

        else :
            $args = array(
                'category_name' => 'Nos outils',
                'order' => 'ASC'
            );
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()) {
                while ($the_query->have_posts()) {

                    $the_query->the_post();
                ?>
                    <div class="wrapper wrapper-outils">
                        <?php if (has_post_thumbnail()) { ?>
                            <div>
                                <?php
                                echo the_post_thumbnail(get_the_ID(), 'thumbnail');
                                ?>
                            </div>
                        <?php  } ?>
                        <div class="wrapper wrapper-text">
                            <h2> <?php echo get_the_title(); ?> </h2>
                            <?php $content = apply_filters('the_content', get_the_content());
                            $paragraphs = explode('</p>', $content);

                            if (!empty($paragraphs[0])) {
                                echo $paragraphs[0] . '</p>';
                            }
                            ?>
                            <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="button button-outils">Plus d'infos</a>
                        </div>
                    </div>
        <?php
                }
                wp_reset_postdata();
            } else {
                echo '<p>Aucun article trouvé dans cette catégorie.</p>';
            }

        endif; ?>
    </div>

</section>

<?php
get_footer();
