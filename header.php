<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="visually-hidden-focusable c-skip-link" href="#main">Skip to content</a>

<header class="c-header" data-header>
    <nav class="navbar navbar-expand-lg" aria-label="Main">
        <div class="container">
            <a class="navbar-brand c-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/framework-wordmark.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="150" height="28">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#site-nav" aria-controls="site-nav" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="site-nav">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'navbar-nav mx-lg-auto mb-3 mb-lg-0 gap-lg-2',
                    'fallback_cb'    => false,
                    'depth'          => 2,
                    'walker'         => new Framework_Nav_Walker(),
                ] );
                ?>
                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 pb-3 pb-lg-0">
                    <a class="btn btn-link c-header__login" href="<?php echo esc_url( framework_app_url() ); ?>">Log in</a>
                    <a class="btn btn-primary" href="<?php echo esc_url( framework_signup_url() ); ?>">Get started</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main id="main">
