<?php
/**
 * One-off content change (Nov 21 2026): every app screenshot on the home and
 * feature pages went out of date after the app's redesign (brand headers,
 * summary rows, restyled lists and phone pages). The replacements live next
 * to this file in screenshots-2026-12/. This adds each one to the media library and
 * points the page section at it, keeping the old image's alt text.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/update-screenshots-2026-12.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/update-screenshots-2026-12.php apply     (make the changes)
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
$dir     = __DIR__ . '/screenshots-2026-12';
$marker  = '_framework_screenshot_update';
$changes = 0;
$skipped = 0;

// page slug => [ section image meta key, old file name (no extension), new file ]
$swaps = [
    [ 'clients-office', 'sections_0_image', 'clients-hero-2026-11', 'clients-hero-2026-12.png' ],
    [ 'clients-office', 'sections_1_image', 'clients-show-2026-11', 'clients-show-2026-12.png' ],
    [ 'clients-office', 'sections_2_image', 'clients-list', 'clients-list-2026-12.png' ],
    [ 'clients-office', 'sections_3_image', 'clients-money', 'clients-money-2026-12.png' ],
    [ 'clients-office', 'sections_4_image', 'clients-team', 'clients-team-2026-12.png' ],
    [ 'crew-app', 'sections_0_image', 'crew-app-hero-2026-11', 'crew-app-hero-2026-12.png' ],
    [ 'crew-app', 'sections_1_image', 'crew-app-day', 'crew-app-day-2026-12.png' ],
    [ 'crew-app', 'sections_2_image', 'crew-app-job-2026-11', 'crew-app-job-2026-12.png' ],
    [ 'crew-app', 'sections_3_image', 'crew-app-proof-2026-11', 'crew-app-proof-2026-12.png' ],
    [ 'crew-app', 'sections_4_image', 'crew-app-offline-2026-11', 'crew-app-offline-2026-12.png' ],
    [ 'crew-app', 'sections_5_image', 'crew-app-time', 'crew-app-time-2026-12.png' ],
    [ 'estimates-invoices', 'sections_0_image', 'estimates-hero', 'estimates-hero-2026-12.png' ],
    [ 'estimates-invoices', 'sections_1_image', 'estimates-options', 'estimates-options-2026-12.png' ],
    [ 'estimates-invoices', 'sections_2_image', 'estimates-signed', 'estimates-signed-2026-12.png' ],
    [ 'estimates-invoices', 'sections_3_image', 'estimates-paid-2026-11', 'estimates-paid-2026-12.png' ],
    [ 'estimates-invoices', 'sections_4_image', 'estimates-chase', 'estimates-chase-2026-12.png' ],
    [ 'home', 'sections_0_image', 'framework-app-hero-2026-11', 'framework-app-hero-2026-12.png' ],
    [ 'routes-map', 'sections_0_image', 'routes-hero', 'routes-hero-2026-12.png' ],
    [ 'routes-map', 'sections_1_image', 'routes-board', 'routes-board-2026-12.png' ],
    [ 'routes-map', 'sections_2_image', 'routes-opt', 'routes-opt-2026-12.png' ],
    [ 'routes-map', 'sections_3_image', 'routes-travel', 'routes-travel-2026-12.png' ],
    [ 'scheduling-dispatch', 'sections_0_image', 'scheduling-hero', 'scheduling-hero-2026-12.png' ],
    [ 'scheduling-dispatch', 'sections_1_image', 'scheduling-calendar', 'scheduling-calendar-2026-12.png' ],
    [ 'scheduling-dispatch', 'sections_2_image', 'scheduling-dispatch', 'scheduling-dispatch-2026-12.png' ],
    [ 'scheduling-dispatch', 'sections_3_image', 'scheduling-smart', 'scheduling-smart-2026-12.png' ],
    [ 'scheduling-dispatch', 'sections_4_image', 'scheduling-series-2026-11', 'scheduling-series-2026-12.png' ],
    [ 'time-payroll', 'sections_0_image', 'time-hero', 'time-hero-2026-12.png' ],
    [ 'time-payroll', 'sections_1_image', 'time-clock', 'time-clock-2026-12.png' ],
    [ 'time-payroll', 'sections_2_image', 'time-sheets', 'time-sheets-2026-12.png' ],
    [ 'time-payroll', 'sections_3_image', 'time-payroll', 'time-payroll-2026-12.png' ],
    [ 'time-payroll', 'sections_4_image', 'time-off', 'time-off-2026-12.png' ],
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
