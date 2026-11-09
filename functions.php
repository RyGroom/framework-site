<?php

/**
 * Framework marketing theme — functions and definitions.
 */

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/texting-feature.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/woocommerce-checkout.php';

/**
 * Theme setup.
 */
function framework_setup(): void {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );

    register_nav_menus( [
        'primary' => __( 'Primary Navigation', 'framework' ),
        'footer'  => __( 'Footer Navigation', 'framework' ),
        'legal'   => __( 'Legal Links', 'framework' ),
    ] );
}
add_action( 'after_setup_theme', 'framework_setup' );

/**
 * Styles and scripts. Bootstrap from the CDN; the theme's own CSS is the
 * expanded dev build while WP_DEBUG is on, the minified build otherwise.
 * File modification times bust the cache.
 */
function framework_assets(): void {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    wp_enqueue_style( 'framework-fonts', 'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700&display=swap', [], null );
    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3' );
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css', [], '1.11.3' );

    $css = ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( "$dir/assets/css/main.css" ) ) ? 'main.css' : 'main.min.css';
    if ( file_exists( "$dir/assets/css/$css" ) ) {
        wp_enqueue_style( 'framework', "$uri/assets/css/$css", [ 'bootstrap' ], filemtime( "$dir/assets/css/$css" ) );
    }

    wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], '5.3.3', true );

    $js = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'main.js' : 'main.min.js';
    if ( file_exists( "$dir/assets/js/$js" ) ) {
        wp_enqueue_script( 'framework', "$uri/assets/js/$js", [], filemtime( "$dir/assets/js/$js" ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'framework_assets' );

/**
 * Scroll-in motion (assets/scss/_motion.scss + main.js) is switched on before the
 * first paint by adding `has-motion` to <html> — and only then is anything hidden.
 * No JavaScript: the class never appears, so all content shows as normal. Reduced
 * motion or no IntersectionObserver: the class isn't added either. If main.js
 * hasn't taken over within 3 seconds (blocked or failed to load), the class comes
 * off again so nothing can stay hidden.
 */
add_action( 'wp_head', function (): void {
    echo "<script>(function(e){if(!('IntersectionObserver' in window)||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;e.classList.add('has-motion');setTimeout(function(){if(!window.frameworkReveal)e.classList.remove('has-motion');},3000);})(document.documentElement);</script>\n";
}, 1 );

// Let the Google Fonts and Bootstrap CDN hosts connect early.
add_filter( 'wp_resource_hints', function ( array $urls, string $relation ): array {
    if ( $relation === 'preconnect' ) {
        $urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' ];
        $urls[] = 'https://cdn.jsdelivr.net';
    }
    return $urls;
}, 10, 2 );

/**
 * ACF local JSON: field groups edited in the admin save into the theme, so
 * they travel with it in git.
 */
add_filter( 'acf/settings/save_json', fn () => get_template_directory() . '/acf-json' );
add_filter( 'acf/settings/load_json', function ( array $paths ): array {
    $paths[] = get_template_directory() . '/acf-json';
    return $paths;
} );

/**
 * "Site settings" options page — where the app lives (for the Log in /
 * Get started buttons) and the contact email.
 */
add_action( 'acf/init', function (): void {
    if ( function_exists( 'acf_add_options_page' ) ) {
        acf_add_options_page( [
            'page_title' => 'Site settings',
            'menu_slug'  => 'framework-settings',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-admin-generic',
            'position'   => 59,
        ] );
    }
} );

// No comments anywhere on a marketing site.
add_action( 'init', function (): void {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
} );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/**
 * Bootstrap's nav classes on the primary menu's items and links, which
 * wp_nav_menu() doesn't add on its own.
 */
add_filter( 'nav_menu_css_class', function ( array $classes, $item, $args, $depth = 0 ): array {
    if ( ( $args->theme_location ?? '' ) === 'primary' && 0 === $depth ) {
        $classes[] = 'nav-item';
    }
    return $classes;
}, 10, 4 );

add_filter( 'nav_menu_link_attributes', function ( array $atts, $item, $args, $depth = 0 ): array {
    if ( ( $args->theme_location ?? '' ) === 'primary' && 0 === $depth ) {
        $atts['class'] = trim( ( $atts['class'] ?? '' ) . ' nav-link' );
    }
    return $atts;
}, 10, 4 );

/**
 * Primary menu: a top-level item with children (Features) opens a Bootstrap
 * dropdown panel. Each child shows an icon (a "bi-…" CSS class on the menu
 * item), its title and its description; a child with the "is-all" class is
 * the panel's footer link.
 */
class Framework_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<div class="dropdown-menu c-nav-panel"><ul class="c-nav-panel__grid list-unstyled mb-0">';
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul></div>';
    }

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
        $item = $data_object;
        if ( 0 === $depth ) {
            $has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
            $classes      = implode( ' ', array_filter( (array) $item->classes ) ) . ' nav-item' . ( $has_children ? ' dropdown' : '' );
            $output      .= '<li class="' . esc_attr( $classes ) . '">';
            if ( $has_children ) {
                $output .= sprintf(
                    '<a class="nav-link dropdown-toggle" href="%s" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">%s</a>',
                    esc_url( $item->url ),
                    esc_html( $item->title )
                );
            } else {
                // In-page anchors (#how-it-works) aren't pages, even though WordPress marks them current on the homepage.
                $current = in_array( 'current-menu-item', (array) $item->classes, true ) && 'custom' !== $item->type ? ' aria-current="page"' : '';
                $output .= sprintf( '<a class="nav-link" href="%s"%s>%s</a>', esc_url( $item->url ), $current, esc_html( $item->title ) );
            }
            return;
        }

        $icon = '';
        foreach ( (array) $item->classes as $class ) {
            if ( str_starts_with( (string) $class, 'bi-' ) ) {
                $icon = $class;
            }
        }
        $is_all = in_array( 'is-all', (array) $item->classes, true );

        if ( $is_all ) {
            $output .= sprintf(
                '<li class="c-nav-panel__all"><a href="%s">%s <i class="bi bi-arrow-right" aria-hidden="true"></i></a>',
                esc_url( $item->url ),
                esc_html( $item->title )
            );
            return;
        }

        $output .= sprintf(
            '<li><a class="c-nav-panel__item" href="%s">%s<span><span class="c-nav-panel__title">%s</span>%s</span></a>',
            esc_url( $item->url ),
            $icon ? '<span class="c-nav-panel__icon" aria-hidden="true"><i class="bi ' . esc_attr( $icon ) . '"></i></span>' : '',
            esc_html( $item->title ),
            $item->description ? '<span class="c-nav-panel__desc">' . esc_html( $item->description ) . '</span>' : ''
        );
    }

    public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
}

// Contact Form 7: our forms are laid out with their own markup, so don't let
// CF7 wrap every line in <p>/<br>.
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/**
 * Favicon — the same set the app uses (copied from its public/ folder). A Site
 * Icon set under Appearance → Customize takes over if one is ever added.
 */
add_action( 'wp_head', function (): void {
    if ( has_site_icon() ) {
        return;
    }
    $uri = get_template_directory_uri() . '/assets/img/favicon';
    printf( '<link rel="icon" href="%s" sizes="any">' . "\n", esc_url( "$uri/favicon.ico" ) );
    printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( "$uri/favicon.svg" ) );
    printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( "$uri/apple-touch-icon.png" ) );
}, 2 );
