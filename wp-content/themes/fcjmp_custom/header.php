<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> | <?php wp_title(); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="header">
        <?php if (is_front_page()) : ?>
            <h1 class="logo"><?php the_custom_logo(); ?></h1>
        <?php else : ?>
            <div class="logo"><?php the_custom_logo(); ?></div>
        <?php endif; ?>

        <button class="nav_burger" aria-label="Ouvrir le menu">
            <span class="nav_burger-top"></span>
            <span class="nav_burger-middle"></span>
            <span class="nav_burger-bottom"></span>
        </button>

        <nav class="nav_custom" role="navigation">
            <?php if (is_user_logged_in()) : ?>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'fcjmp_basic-member',
                    'container'      => false,
                    'menu_class'     => 'menu menu-member',
                    'fallback_cb'    => 'wp_page_menu',
                ));
                ?>
                <a class="button button-menu" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                    Déconnexion
                </a>
            <?php else : ?>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'fcjmp_basic-guest',
                    'container'      => false,
                    'menu_class'     => 'menu menu-guest',
                    'fallback_cb'    => 'wp_page_menu',
                ));
                ?>
                <a class="button button-menu" href="<?php echo esc_url(wp_login_url()); ?>">
                    Connexion
                </a>
            <?php endif; ?>
        </nav>
    </header>