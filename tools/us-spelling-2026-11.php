<?php
/**
 * One-off content change (Nov 10 2026): the site is American, so the few
 * British spellings in page content become US spellings: colour → color,
 * favours → favors, cancelled/cancelling → canceled/canceling.
 * ("Cancellation" is standard US spelling too, so it stays.)
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/us-spelling-2026-11.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/us-spelling-2026-11.php apply     (make the changes)
 *
 * Only exact words are swapped, only on the pages listed, so anything
 * edited since is left alone. Safe to run twice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

$apply   = in_array( 'apply', $args ?? [], true );
$changes = 0;

// Whole-word swaps (case kept as written).
$words = [
    'colour'     => 'color',
    'Colour'     => 'Color',
    'colours'    => 'colors',
    'Favours'    => 'Favors',
    'favours'    => 'favors',
    'favour'     => 'favor',
    'cancelled'  => 'canceled',
    'Cancelled'  => 'Canceled',
    'cancelling' => 'canceling',
    'Cancelling' => 'Canceling',
];

$swap = static function ( string $text ) use ( $words ): string {
    foreach ( $words as $uk => $us ) {
        $text = preg_replace( '/\b' . preg_quote( $uk, '/' ) . '\b/', $us, $text );
    }
    return $text;
};

// page slug => meta keys to fix ('post_content' = the page body).
$targets = [
    'scheduling-dispatch' => [ 'sections_3_bullets_2_item' ],
    'routes-map'          => [ 'sections_1_text', 'sections_1_bullets_0_item' ],
    'terms'               => [ 'post_content' ],
];

foreach ( $targets as $slug => $keys ) {
    $pages = get_posts( [ 'post_type' => 'page', 'name' => $slug, 'post_status' => [ 'publish', 'private', 'draft' ], 'numberposts' => 1 ] );
    if ( ! $pages ) {
        WP_CLI::log( "  SKIPPED (no page '$slug')" );
        continue;
    }
    $page = $pages[0];

    foreach ( $keys as $key ) {
        $old = $key === 'post_content' ? $page->post_content : (string) get_post_meta( $page->ID, $key, true );
        $new = $swap( $old );
        if ( $new === $old ) {
            WP_CLI::log( "  already done: $slug $key" );
            continue;
        }

        $changes++;
        WP_CLI::log( "  $slug $key" );
        if ( ! $apply ) {
            continue;
        }
        if ( $key === 'post_content' ) {
            wp_update_post( [ 'ID' => $page->ID, 'post_content' => $new ] );
        } else {
            update_post_meta( $page->ID, $key, $new );
        }
    }
}

WP_CLI::log( '' );
WP_CLI::log( sprintf( '%s: %d to change.', $apply ? 'Applied' : 'Dry run', $changes ) );
if ( ! $apply && $changes ) {
    WP_CLI::log( 'Run again with "apply" to make the changes.' );
}
