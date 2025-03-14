<?php

/* 
Template Name: nos outils
*/

get_header();



?>

<section class="section">

    <div class="content">
        <?php if (!is_user_logged_in()):
            $args = array(
                'category_name' => 'Nos outils',
                'order' => 'ASC'
            );
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()) {
                while ($the_query->have_posts()) {

                    $the_query->the_post();
        ?>
                    <div class="card-item">
                        <?php
                        if (has_post_thumbnail()) {
                            echo '<div class="card-thumbnail">';
                            echo get_the_post_thumbnail(get_the_ID(), 'medium');
                            echo '</div>';
                        } ?>
                        <div class="card-section">

                            <h2 class="card-title"> <?php echo get_the_title(); ?> </h2>


                        </div>
                        <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="card-link">Plus d'infos</a>
                    </div>
            <?php
                }
                wp_reset_postdata();
            } else {
                echo '<p>Aucun article trouvé dans cette catégorie.</p>';
            }
            ?>
            else : ?>


        <?php endif; ?>
    </div>

</section>