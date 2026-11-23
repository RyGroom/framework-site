<?php
/**
 * One-off content change (Nov 23 2026): the privacy policy's Cloudflare row
 * names Turnstile and links Cloudflare's Turnstile Privacy Addendum, which
 * Cloudflare requires as a condition of using Turnstile's invisible mode
 * (the app's public request form uses Turnstile). "Last updated" moves to
 * November 23, 2026.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/privacy-turnstile-2026-11.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/privacy-turnstile-2026-11.php apply     (make the change)
 *
 * Only the exact old row and date are replaced, so a policy edited since is
 * left alone (and reported). Safe to run twice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

$apply = in_array( 'apply', $args ?? [], true );

$old_row = "<tr>\n<td>Cloudflare</td>\n<td>Blocking spam on hosted request forms, if turned on</td>\n<td>Security token and IP address</td>\n</tr>";
$new_row = "<tr>\n<td>Cloudflare (Turnstile)</td>\n<td>Blocking spam and bots on hosted request forms, if turned on</td>\n"
    . "<td>Security token, IP address, and details about the browser and device. See Cloudflare's "
    . '<a href="https://www.cloudflare.com/turnstile-privacy-policy/" target="_blank" rel="noopener">Turnstile Privacy Addendum</a>.'
    . "</td>\n</tr>";

$old_date = '<p>Last updated: November 10, 2026</p>';
$new_date = '<p>Last updated: November 23, 2026</p>';

$pages = get_posts( [ 'post_type' => 'page', 'name' => 'privacy', 'post_status' => [ 'publish', 'private', 'draft' ], 'numberposts' => 1 ] );
if ( ! $pages ) {
    WP_CLI::error( "No page with the slug 'privacy'." );
}
$page    = $pages[0];
$content = $page->post_content;

if ( str_contains( $content, 'turnstile-privacy-policy' ) ) {
    WP_CLI::success( 'Already done: the policy links the Turnstile Privacy Addendum.' );
    return;
}
// Saved in the editor, the row may have \r\n line ends.
$normalized = str_replace( "\r\n", "\n", $content );
if ( ! str_contains( $normalized, $old_row ) ) {
    WP_CLI::error( "The Cloudflare row isn't the expected text (edited since?). Nothing changed; add the addendum link by hand." );
}

$updated = str_replace( $old_row, $new_row, $normalized );
$dated   = str_contains( $updated, $old_date );
$updated = str_replace( $old_date, $new_date, $updated );

WP_CLI::log( '  Cloudflare row: names Turnstile and links the addendum' );
WP_CLI::log( $dated ? '  Last updated: November 10 → November 23, 2026' : '  Last updated: not the expected date, left as is' );

if ( ! $apply ) {
    WP_CLI::log( 'Dry run. Add "apply" to make the change.' );
    return;
}

wp_update_post( [ 'ID' => $page->ID, 'post_content' => $updated ] );
WP_CLI::success( 'Privacy policy updated (page ' . $page->ID . ').' );
