<?php get_header(); ?>

<div class="hero-banner">
    <!-- Section pour le Hero Banner -->
    <img class="hero-banner_img" src="<?php echo get_template_directory_uri(); ?>/assets/img/Bracelet%20PALM%20fcjmp.jpg" alt="Bracelet PALM FCJMP">
    <h2 class="hero-banner_text">Ensemble pour l'égalité des chances</h2>
</div>
<div class="content">
    <p class="text-big">La FCJMP est une <strong>Organisation de jeunesse</strong> pluraliste, fédérant des <strong>Maisons de Jeunes</strong>, des <strong>Centres de Rencontre et d’hébergement</strong> et des <strong>Centres d’Expression Culturelle</strong>, dans une perspective d’<strong>Egalité des Chances.</strong></p>


    <a class="button button-bigtext">En savoir plus ?</a>
</div>
<section id="formation" class="section section-formation section-green">
    <div class="content">
        <h2 class="title">Formations</h2>
        <h3 class="title title-medium">Nos prochaines formations</h3>
        <?php
        $args = array(
            'category_name'   => 'Formation', // Nom de la catégorie
            'posts_per_page'  => 4,           // Nombre d'articles à afficher
            'meta_key'        => 'date_formation', // Nom du champ ACF
            'orderby'         => 'meta_value',     // Trier par la valeur du champ
            'order'           => 'ASC',           // Trier en ordre croissant (dates proches en premier)
            'meta_type'       => 'DATE',          // Type du champ (date)
        );

        $the_query = new WP_Query($args);
        ?>
        <div class="card">
            <?php
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
        </div>
    </div>

</section>

<section class="section">
    <div class="content content-double">
        <div>
            <h2 class="title">Actualités</h2>
            <h3 class="title title-medium">Les actus du secteur</h3>
            <?php
            $args = array(
                'category_name' => 'Actualité',
                'posts_per_page' => 4,

            );

            $the_query = new WP_Query($args);
            ?>


            <div class="card card-duo">
                <?php
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
                            <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="card-link">Lire l'article</a>
                        </div>
                <?php
                    }
                    wp_reset_postdata();
                } else {
                    echo '<p>Aucun article trouvé dans cette catégorie.</p>';
                }
                ?>
            </div>
        </div>
        <div>
            <h2 class="title">Emplois</h2>
            <h3 class="title title-medium">Les emplois du secteur</h3>
            <?php
            $args = array(
                'category_name' => 'Emplois',
                'posts_per_page' => 4,

            );
            $the_query = new WP_Query($args);
            ?>
            <div class="card card-duo">
                <?php
                if ($the_query->have_posts()) {
                    while ($the_query->have_posts()) {
                        $the_query->the_post();
                ?>
                        <div class="card-item">
                            <?php
                            if (has_post_thumbnail()) {
                                echo '<div class="card-thumbnail card-logo">';
                                echo get_the_post_thumbnail(get_the_ID(), 'medium');
                                echo '</div>';
                            } ?>
                            <div class="card-section">
                                <h2 class="card-title"> <?php echo get_the_title(); ?> </h2>
                            </div>
                            <a href="<?php echo get_the_permalink(); ?>" rel="noopener noreferrer" class="card-link">Vers l'offre</a>
                        </div>
                <?php
                    }
                    wp_reset_postdata();
                } else {
                    echo '<p>Aucun article trouvé dans cette catégorie.</p>';
                }
                ?>
            </div>
        </div>
    </div>

</section>

<?php get_footer(); ?>