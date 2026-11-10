<?php
/**
 * One-off content change (Nov 2026): the Google Ads landing page at /start/
 * (template page-landing.php). Publishes it, or points an existing /start/
 * page at the template. Safe to run twice.
 *
 * From the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/landing-page-2026-11.php          (dry run)
 *   wp eval-file wp-content/themes/framework/tools/landing-page-2026-11.php apply    (make the change)
 *
 * Then, for ads tracking, add to wp-config.php (see inc/google-tag.php):
 *   define( 'FRAMEWORK_GTAG_ID', 'AW-…' );
 *   define( 'FRAMEWORK_ADS_CONVERSION', 'AW-…/…' );
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

$apply    = in_array( 'apply', $args ?? [], true );
$template = 'page-landing.php';
$page     = get_page_by_path( 'start', OBJECT, 'page' );

if ( $page && get_post_meta( $page->ID, '_wp_page_template', true ) === $template && $page->post_status === 'publish' ) {
    WP_CLI::success( "/start/ is already the landing page (page {$page->ID}). Nothing to do." );
    return;
}

if ( ! $apply ) {
    WP_CLI::log( $page
        ? "Would set page {$page->ID} (/start/) to the landing page template and publish it."
        : 'Would create and publish /start/ with the landing page template.' );
    WP_CLI::log( 'Run again with "apply" to make the change.' );
    return;
}

if ( $page ) {
    wp_update_post( [ 'ID' => $page->ID, 'post_status' => 'publish' ] );
    update_post_meta( $page->ID, '_wp_page_template', $template );
    $id = $page->ID;
} else {
    $id = wp_insert_post( [
        'post_type'   => 'page',
        'post_title'  => 'Start your free month',
        'post_name'   => 'start',
        'post_status' => 'publish',
    ], true );
    if ( is_wp_error( $id ) ) {
        WP_CLI::error( $id->get_error_message() );
    }
    update_post_meta( $id, '_wp_page_template', $template );
}

WP_CLI::success( 'Landing page live at ' . get_permalink( $id ) );
