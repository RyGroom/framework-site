<?php
/**
 * One-off content change (Nov 2026): what Twilio / carrier reviewers need to
 * see for A2P 10DLC.
 *
 *   - The public /sms page (template page-sms.php) with the working request
 *     form of the app's "Framework Demo Co." embedded in it.
 *   - Terms of Service: a "Text Messaging Terms" section, anchor #sms.
 *   - Privacy Policy: the HELP placeholder replaced, an anchor on the text
 *     messages section (#text-messages), the confirmation text mentioned,
 *     and STOP described per business (each texts from its own number).
 *   - "Last updated" on both: today's date.
 *   - Footer legal menu: an "SMS Terms" link to /terms/#sms.
 *
 * First, on the app server:
 *   php artisan app:sms-demo-company --embed-out=/tmp/sms-embed.html
 * Then from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/sms-optin-2026-11.php embed=/tmp/sms-embed.html          (dry run)
 *   wp eval-file wp-content/themes/framework/tools/sms-optin-2026-11.php embed=/tmp/sms-embed.html apply    (make the changes)
 *
 * Text is only changed where it still says what's expected, so a later hand
 * edit is never overwritten. Safe to run twice; re-running with a new embed
 * file just replaces the form.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

$apply   = in_array( 'apply', $args ?? [], true );
$changes = 0;
$say     = function ( string $line ) {
    WP_CLI::log( $line );
};
$embed_path = null;
foreach ( $args ?? [] as $arg ) {
    if ( str_starts_with( $arg, 'embed=' ) ) {
        $embed_path = substr( $arg, 6 );
    }
}

$say( $apply ? 'Applying.' : 'Dry run: nothing will be saved. Add "apply" to make the changes.' );
$today = wp_date( 'F j, Y' );

/** Swap $old for $new in a page's content, once, only if $old is there. */
$swap = function ( WP_Post $page, string $old, string $new, string $what ) use ( $apply, &$changes, $say ) {
    $content = get_post_field( 'post_content', $page->ID, 'raw' );
    if ( str_contains( $content, $new ) ) {
        $say( "  already done: {$page->post_title}: $what" );
        return;
    }
    if ( ! str_contains( $content, $old ) ) {
        $say( "  SKIPPED (text has changed): {$page->post_title}: $what" );
        return;
    }
    $changes++;
    $say( "  {$page->post_title}: $what" );
    if ( $apply ) {
        wp_update_post( [ 'ID' => $page->ID, 'post_content' => wp_slash( str_replace( $old, $new, $content ) ) ] );
        clean_post_cache( $page->ID );
    }
};

// --------------------------------------------------------------------------
// 1. Terms of Service
// --------------------------------------------------------------------------
$say( '' );
$say( 'Terms of Service:' );
$terms = get_page_by_path( 'terms' );
if ( ! $terms ) {
    $say( '  MISSING: no page with the slug "terms"' );
} else {
    $sms_terms = <<<'HTML'
<h2 id="sms">14. Text Messaging Terms</h2>
<p>These terms cover <strong>Framework service texts</strong>: text messages Framework sends on behalf of a business that uses the Service, to that business's clients who have agreed to receive them.</p>
<ul>
<li><strong>How you agree:</strong> by ticking the text message box on a business's request form, or by agreeing with the business by phone or in person, after which the business records your consent in Framework. You get one text confirming you're signed up. Consent is not a condition of purchase.</li>
<li><strong>What we send:</strong> messages about your appointments with that business, including visit reminders, schedule changes, and on-my-way or running-late updates. We don't send marketing.</li>
<li><strong>How often:</strong> message frequency varies with your appointments.</li>
<li><strong>Cost:</strong> Msg &amp; data rates may apply.</li>
<li><strong>Opting out:</strong> reply STOP at any time. Each business texts from its own number, so STOP stops texts from that business only; other businesses that use Framework aren't affected. Reply START to resubscribe.</li>
<li><strong>Help:</strong> reply HELP for help, or email support@theframework.app.</li>
<li><strong>Delivery:</strong> carriers are not liable for delayed or undelivered messages.</li>
<li><strong>Numbers:</strong> US phone numbers only.</li>
<li><strong>Privacy:</strong> see the text messages section of our <a href="/privacy/#text-messages">Privacy Policy</a>.</li>
</ul>
<h2>15. Contact</h2>
HTML;
    if ( str_contains( get_post_field( 'post_content', $terms->ID, 'raw' ), '<h2 id="sms">' ) ) {
        $say( '  already done: Terms of Service: "Text Messaging Terms" section (#sms)' );
    } else {
        $swap( $terms, '<h2>14. Contact</h2>', $sms_terms, 'add "14. Text Messaging Terms" (#sms), Contact becomes 15' );
    }
    $swap( $terms, '<p>Last updated: [EFFECTIVE DATE]</p>', "<p>Last updated: $today</p>", "Last updated: $today" );
    // Each business now texts from its own number (Nov 2026): if the first
    // version of this section is already live, bring its STOP / HELP lines up to date.
    $swap( $terms, '<li><strong>Opting out:</strong> reply STOP at any time. Because businesses share one sending number, STOP stops texts from all Framework businesses to that number, as our Privacy Policy explains. Reply START to resubscribe.</li>', '<li><strong>Opting out:</strong> reply STOP at any time. Each business texts from its own number, so STOP stops texts from that business only; other businesses that use Framework aren\'t affected. Reply START to resubscribe.</li>', 'STOP stops one business, not all' );
}

// --------------------------------------------------------------------------
// 2. Privacy Policy (consistency only)
// --------------------------------------------------------------------------
$say( '' );
$say( 'Privacy Policy:' );
$privacy = get_page_by_path( 'privacy' );
if ( ! $privacy ) {
    $say( '  MISSING: no page with the slug "privacy"' );
} else {
    $swap( $privacy, '<h3>Text messages</h3>', '<h3 id="text-messages">Text messages</h3>', 'anchor #text-messages on "Text messages"' );
    $swap( $privacy,
        'Reply HELP for help: [CONFIRM HELP RESPONSE IS SET UP IN TWILIO], or contact support@theframework.app.',
        'Reply HELP for help, or contact support@theframework.app.',
        'HELP placeholder replaced' );
    $swap( $privacy,
        'either by ticking the box on a request form or through the business recording their agreement. We record when and how consent was given.',
        'either by ticking the box on a request form or by agreeing with the business by phone or in person, after which the business records it. We record when and how consent was given, and send one text confirming they\'re signed up.',
        'consent line matches the opt-in wording' );
    $swap( $privacy,
        'Because businesses share one sending number, STOP turns off texts from all Framework businesses to that number.',
        'Each business texts from its own number, so STOP turns off texts from that business only.',
        'STOP stops one business, not all' );
    $swap( $privacy, '<p>Last updated: [EFFECTIVE DATE]</p>', "<p>Last updated: $today</p>", "Last updated: $today" );
}

// --------------------------------------------------------------------------
// 3. The /sms page
// --------------------------------------------------------------------------
$say( '' );
$say( '/sms page:' );
$page = get_page_by_path( 'sms' );
if ( ! $page ) {
    $changes++;
    $say( '  create page "Text messages" at /sms (template page-sms.php)' );
    if ( $apply ) {
        $id   = wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Text messages', 'post_name' => 'sms', 'post_content' => '' ] );
        $page = get_post( $id );
    }
}
if ( $page && get_page_template_slug( $page->ID ) !== 'page-sms.php' ) {
    $changes++;
    $say( '  set template page-sms.php' );
    if ( $apply ) {
        update_post_meta( $page->ID, '_wp_page_template', 'page-sms.php' );
    }
} elseif ( $page ) {
    $say( '  already done: template' );
}

if ( $embed_path === null ) {
    $say( '  embed: none given (embed=/path/to/file). The page shows a "not connected" note until it is.' );
} elseif ( ! is_readable( $embed_path ) ) {
    WP_CLI::error( "Can't read the embed file: $embed_path" );
} else {
    $embed = trim( (string) file_get_contents( $embed_path ) );
    if ( ! str_contains( $embed, 'sms_opt_in' ) || ! str_contains( $embed, 'Framework Demo Co.' ) ) {
        WP_CLI::error( 'That file doesn\'t look like the demo company\'s embed code with the text message box (run php artisan app:sms-demo-company).' );
    }
    if ( $page && get_post_meta( $page->ID, 'framework_sms_demo_embed', true ) === $embed ) {
        $say( '  already done: embed code' );
    } else {
        $changes++;
        $say( '  store the demo company\'s embed code (' . strlen( $embed ) . ' bytes)' );
        if ( $apply && $page ) {
            update_post_meta( $page->ID, 'framework_sms_demo_embed', wp_slash( $embed ) );
        }
    }
}

// --------------------------------------------------------------------------
// 4. Footer legal menu: "SMS Terms"
// --------------------------------------------------------------------------
$say( '' );
$say( 'Footer:' );
$locations = get_nav_menu_locations();
$menu_id   = $locations['legal'] ?? 0;
if ( ! $menu_id ) {
    $say( '  MISSING: no menu assigned to the "legal" footer location' );
} else {
    $url   = home_url( '/terms/#sms' );
    $items = wp_get_nav_menu_items( $menu_id ) ?: [];
    $has   = array_filter( $items, fn ( $i ) => $i->title === 'SMS Terms' );
    if ( $has ) {
        $say( '  already done: "SMS Terms" link' );
    } else {
        $changes++;
        $say( "  add \"SMS Terms\" -> $url" );
        if ( $apply ) {
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'    => 'SMS Terms',
                'menu-item-url'      => $url,
                'menu-item-type'     => 'custom',
                'menu-item-status'   => 'publish',
                'menu-item-position' => count( $items ) + 1,
            ] );
        }
    }
}

$say( '' );
WP_CLI::success( $apply ? "$changes change(s) made." : "$changes change(s) to make. Run again with \"apply\" to save them." );
