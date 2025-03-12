<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> | <?php wp_title(); ?></title>
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
    <?php wp_head(); ?>
</head>

<body>
    <header class="header">
        <?php if (is_front_page()) : ?>
            <h1 class="logo">
                <?php the_custom_logo(); ?>
            </h1>
        <?php else : ?>
            <div class="logo">
                <?php the_custom_logo(); ?>
            </div>
        <?php endif; ?>
        <div class="nav_burger">
            <span class="nav_burger-top"></span><span class="nav_burger-middle"></span><span class="nav_burger-bottom"></span>
        </div>
        <nav class="nav_custom">
            <?php wp_nav_menu(array('theme_location' => 'main-menu')); ?>


            <?php if (!is_user_logged_in()): ?>
                <a class="button button-menu" href="<?php echo wp_login_url(); ?>">
                    Membres
                </a>
            <?php else : ?>
                <a class="button button-menu" href="<?php echo wp_logout_url(home_url()); ?>">
                    Déconnexion
                </a>
            <?php endif; ?>

        </nav>
    </header>
    <main>


        <?php wp_body_open(); ?>



        <?php wp_footer(); ?>

</body>

</html>