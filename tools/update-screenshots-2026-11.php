<?php
/**
 * One-off content change (Nov 9 2026): nine app screenshots went out of date
 * after the app's style fixes (back arrow level with the page name, status
 * line, crew photo tiles, invoice actions, white unticked checkboxes). The replacements live next to this
 * file in screenshots-2026-11/. This adds each one to the media library and
 * points the page section at it, keeping the old image's alt text.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/update-screenshots-2026-11.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/update-screenshots-2026-11.php apply     (make the changes)
 *
 * Each swap checks that the section still shows the screenshot we expect
 * (by file name) and is skipped, with a note, if someone has changed it since.
 * Old images stay in the media library. Safe to run twice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$apply   = in_array( 'apply', $args ?? [], true );
$dir     = __DIR__ . '/screenshots-2026-11';
$marker  = '_framework_screenshot_update';
$changes = 0;
$skipped = 0;

// page slug => [ section image meta key, old file name (no extension), new file ]
$swaps = [
    [ 'home', 'sections_0_image', 'framework-app-hero', 'framework-app-hero-2026-11.png' ],
    [ 'crew-app', 'sections_0_image', 'crew-app-hero', 'crew-app-hero-2026-11.png' ],
    [ 'crew-app', 'sections_2_image', 'crew-app-job', 'crew-app-job-2026-11.png' ],
    [ 'crew-app', 'sections_3_image', 'crew-app-proof', 'crew-app-proof-2026-11.png' ],
    [ 'crew-app', 'sections_4_image', 'crew-app-offline', 'crew-app-offline-2026-11.png' ],
    [ 'estimates-invoices', 'sections_3_image', 'estimates-paid', 'estimates-paid-2026-11.png' ],
    [ 'scheduling-dispatch', 'sections_4_image', 'scheduling-series', 'scheduling-series-2026-11.png' ],
    [ 'clients-office', 'sections_0_image', 'clients-hero', 'clients-hero-2026-11.png' ],
    [ 'clients-office', 'sections_1_image', 'clients-show', 'clients-show-2026-11.png' ],
];

foreach ( $swaps as [ $slug, $key, $old_name, $new_file ] ) {
    $pages = get_posts( [ 'post_type' => 'page', 'name' => $slug, 'post_status' => 'publish', 'numberposts' => 1 ] );
    if ( ! $pages ) {
        $skipped++;
        WP_CLI::log( "  SKIPPED (no published page '$slug')" );
        continue;
    }
    $page    = $pages[0];
    $current = (int) get_post_meta( $page->ID, $key, true );
    $file    = $current ? (string) get_attached_file( $current ) : '';
    $base    = pathinfo( $file, PATHINFO_FILENAME );

    if ( $current && get_post_meta( $current, $marker, true ) === $new_file ) {
        WP_CLI::log( "  already done: $slug $key ($new_file)" );
        continue;
    }
    // Allow WordPress's "-1" style suffixes on the old upload.
    if ( ! preg_match( '/^' . preg_quote( $old_name, '/' ) . '(-\d+)?$/', $base ) ) {
        $skipped++;
        WP_CLI::log( "  SKIPPED (section now shows '" . ( $base ?: 'nothing' ) . "', expected '$old_name'): $slug $key" );
        continue;
    }
    if ( ! is_readable( "$dir/$new_file" ) ) {
        $skipped++;
        WP_CLI::log( "  SKIPPED (missing $new_file)" );
        continue;
    }

    $changes++;
    WP_CLI::log( "  $slug $key: $base -> $new_file" );
    if ( ! $apply ) {
        continue;
    }

    // Sideload from a temp copy (WordPress moves the file it's given).
    $tmp = wp_tempnam( $new_file );
    copy( "$dir/$new_file", $tmp );
    $id = media_handle_sideload( [ 'name' => $new_file, 'tmp_name' => $tmp ], $page->ID, get_the_title( $current ) );
    if ( is_wp_error( $id ) ) {
        @unlink( $tmp );
        WP_CLI::warning( "    upload failed: " . $id->get_error_message() );
        continue;
    }
    update_post_meta( $id, '_wp_attachment_image_alt', (string) get_post_meta( $current, '_wp_attachment_image_alt', true ) );
    update_post_meta( $id, $marker, $new_file );
    update_post_meta( $page->ID, $key, $id );
    WP_CLI::log( "    now attachment $id" );
}

WP_CLI::log( '' );
WP_CLI::log( sprintf( '%s: %d to change, %d skipped.', $apply ? 'Applied' : 'Dry run', $changes, $skipped ) );
if ( ! $apply && $changes ) {
    WP_CLI::log( 'Run again with "apply" to make the changes.' );
}
