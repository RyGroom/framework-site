<?php
/**
 * One-off content change (Nov 2026): Intuit didn't approve the app, so the
 * QuickBooks Online and QuickBooks Time integrations are switched off in the
 * app. This takes every mention of them out of the site's page content.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/remove-quickbooks-content.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/remove-quickbooks-content.php apply     (make the changes)
 *
 * Every change checks the current text first and is skipped, with a note,
 * if the page no longer says what's expected (edited since), so it never
 * overwrites someone's later edit. Safe to run twice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

$apply   = in_array( 'apply', $args ?? [], true );
$changes = 0;
$skipped = 0;

$say = function ( string $line ) {
    WP_CLI::log( $line );
};

/** Replace a meta value only if it's exactly what we expect. */
$set_meta = function ( int $post_id, string $key, string $expected, string $new ) use ( $apply, &$changes, &$skipped, $say ) {
    $current = get_post_meta( $post_id, $key, true );
    if ( $current === $new ) {
        $say( "  already done: $post_id $key" );
        return;
    }
    if ( $current !== $expected ) {
        $skipped++;
        $say( "  SKIPPED (text has changed): $post_id $key = " . wp_trim_words( (string) $current, 20 ) );
        return;
    }
    $changes++;
    $say( "  $post_id $key: \"$expected\" -> \"$new\"" );
    if ( $apply ) {
        update_post_meta( $post_id, $key, wp_slash( $new ) );
    }
};

/**
 * Remove row $row from an ACF repeater stored as {$prefix}_{n}_{sub}, shifting
 * later rows up. $expect_key/$expect_value confirm it's the right row.
 */
$delete_row = function ( int $post_id, string $prefix, int $row, array $subs, string $expect_key, string $expect_value ) use ( $apply, &$changes, &$skipped, $say ) {
    $count   = (int) get_post_meta( $post_id, $prefix, true );
    $current = get_post_meta( $post_id, "{$prefix}_{$row}_{$expect_key}", true );
    if ( $current !== $expect_value ) {
        $already = true;
        for ( $i = 0; $i < $count; $i++ ) {
            if ( get_post_meta( $post_id, "{$prefix}_{$i}_{$expect_key}", true ) === $expect_value ) {
                $already = false;
            }
        }
        if ( $already ) {
            $say( "  already done: $post_id {$prefix} row \"$expect_value\"" );
        } else {
            $skipped++;
            $say( "  SKIPPED (rows have moved): $post_id {$prefix} row $row" );
        }
        return;
    }
    $changes++;
    $say( "  $post_id {$prefix}: delete row $row \"$expect_value\" ($count -> " . ( $count - 1 ) . ' rows)' );
    if ( ! $apply ) {
        return;
    }
    for ( $i = $row; $i < $count - 1; $i++ ) {
        foreach ( $subs as $sub ) {
            update_post_meta( $post_id, "{$prefix}_{$i}_{$sub}", wp_slash( (string) get_post_meta( $post_id, "{$prefix}_" . ( $i + 1 ) . "_{$sub}", true ) ) );
        }
    }
    $last = $count - 1;
    foreach ( $subs as $sub ) {
        delete_post_meta( $post_id, "{$prefix}_{$last}_{$sub}" );
        delete_post_meta( $post_id, "_{$prefix}_{$last}_{$sub}" );
    }
    update_post_meta( $post_id, $prefix, $count - 1 );
};

/** Replace one exact piece of a page's body text. */
$replace_content = function ( int $post_id, string $old, string $new ) use ( $apply, &$changes, &$skipped, $say ) {
    $content = (string) get_post_field( 'post_content', $post_id );
    if ( substr_count( $content, $old ) !== 1 ) {
        if ( $new !== '' && str_contains( $content, $new ) ) {
            $say( "  already done: $post_id content" );
        } elseif ( $new === '' && ! str_contains( $content, $old ) ) {
            $say( "  already done: $post_id content" );
        } else {
            $skipped++;
            $say( "  SKIPPED (text has changed): $post_id content: " . wp_trim_words( wp_strip_all_tags( $old ), 12 ) );
        }
        return;
    }
    $changes++;
    $say( "  $post_id content: \"" . wp_trim_words( wp_strip_all_tags( $old ), 14 ) . '" -> "' . ( $new === '' ? '(removed)' : wp_trim_words( wp_strip_all_tags( $new ), 14 ) ) . '"' );
    if ( $apply ) {
        // WP-CLI runs as no user, so content filtering would otherwise strip markup.
        kses_remove_filters();
        wp_update_post( [ 'ID' => $post_id, 'post_content' => wp_slash( str_replace( $old, $new, $content ) ) ] );
    }
};

$page = function ( string $slug ): int {
    // By slug at any depth: the feature pages sit under /features/.
    $ids = get_posts( [ 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 2 ] );
    if ( count( $ids ) !== 1 ) {
        WP_CLI::warning( count( $ids ) ? "More than one page \"$slug\"; skipped" : "No page \"$slug\"" );
        return 0;
    }
    return (int) $ids[0];
};

WP_CLI::log( $apply ? 'Applying:' : 'Dry run (add "apply" to make the changes):' );

// Home.
if ( $id = $page( 'home' ) ) {
    $set_meta( $id, 'sections_0_points_2_text', 'QuickBooks sync', 'Recurring billing' );
    $set_meta( $id, 'sections_3_bullets_2_item', 'Invoices, payments and time sent to QuickBooks', 'Payroll totals and CSV exports for your accountant' );
    $set_meta( $id, 'sections_5_items_2_question', 'Does it work with QuickBooks?', 'Can I get my data out?' );
    $set_meta( $id, 'sections_5_items_2_answer',
        'Yes. Connect QuickBooks Online and Framework sends it your customers, invoices and payments, and crew time goes to QuickBooks Time.',
        'Yes, any time. The owner can download everything in one zip file: clients, jobs, invoices, payments, timesheets and the rest as spreadsheets, plus receipts and job photos.' );
}

// Estimates & invoices.
if ( $id = $page( 'estimates-invoices' ) ) {
    $set_meta( $id, 'sections_5_features_2_icon', 'arrow-left-right', 'eye' );
    $set_meta( $id, 'sections_5_features_2_title', 'QuickBooks Online sync', 'Know when it\'s opened' );
    $set_meta( $id, 'sections_5_features_2_text', 'Invoices, payments and voids can push to QuickBooks automatically.', 'See when a customer opened an estimate or invoice, so you know when to follow up.' );
    $set_meta( $id, 'sections_5_features_4_text', 'Refunds made in Stripe show on the invoice, and QuickBooks is updated too when auto-sync is on.', 'Refunds made in Stripe show on the invoice, so its balance always matches.' );
    $delete_row( $id, 'sections_6_items', 3, [ 'question', 'answer' ], 'question', 'Does it work with QuickBooks?' );
}

// Time & payroll.
if ( $id = $page( 'time-payroll' ) ) {
    $set_meta( $id, 'sections_5_features_2_icon', 'arrow-repeat', 'exclamation-triangle' );
    $set_meta( $id, 'sections_5_features_2_title', 'QuickBooks Time sync', 'Long shifts caught' );
    $set_meta( $id, 'sections_5_features_2_text', 'Connect QuickBooks Time and each finished shift is sent over, coded to the job it was spent on.', 'A shift of 12 hours or more has to be fixed or confirmed before the week is submitted, so a forgotten clock-out doesn\'t slip through.' );
    $set_meta( $id, 'sections_6_items_0_answer',
        "No. It works out each person's hours, overtime, paid time off and gross pay for the pay period, and exports it as a CSV for your payroll service or accountant. If you use QuickBooks Time, shifts can sync there too.",
        "No. It works out each person's hours, overtime, paid time off and gross pay for the pay period, and exports it as a CSV for your payroll service or accountant." );
}

// Features.
if ( $id = $page( 'features' ) ) {
    $delete_row( $id, 'sections_1_groups_3_items', 11, [ 'item', 'optional' ], 'item', 'QuickBooks Online sync' );
    $delete_row( $id, 'sections_1_groups_4_items', 8, [ 'item', 'optional' ], 'item', 'QuickBooks Time sync' );
}

// Pricing.
if ( $id = $page( 'pricing' ) ) {
    $set_meta( $id, 'sections_2_items_1_answer',
        'No. Every plan includes every feature and integration, including QuickBooks and Stripe. Extra logins are $14.99 per month each, and taxes may apply. If you take card payments from clients, Stripe charges its own fees.',
        'No. Every plan includes every feature, including card payments through Stripe. Extra logins are $14.99 per month each, and taxes may apply. If you take card payments from clients, Stripe charges its own fees.' );
}

// Terms of Service.
if ( $id = $page( 'terms' ) ) {
    $replace_content( $id, 'You can choose to connect outside services, including QuickBooks Online and QuickBooks Time.', 'You can choose to connect outside services, such as Stripe.' );
}

// Privacy Policy.
if ( $id = $page( 'privacy' ) ) {
    $replace_content( $id,
        "<p><strong>Services a Customer chooses to connect:</strong> Intuit receives the following when a Customer connects it:</p>\n<ul>\n<li><strong>QuickBooks Online:</strong> client names, contact details, addresses, invoices, estimates, payments, refunds, and tips. Client records can also be imported.</li>\n<li><strong>QuickBooks Time:</strong> time entries and paid leave details, including notes. Location isn't sent.</li>\n</ul>\n",
        '' );
    $replace_content( $id, 'Records already held by providers such as Stripe or QuickBooks stay under their policies.', 'Records already held by providers such as Stripe stay under their policies.' );
}

WP_CLI::success( sprintf( '%d change%s %s, %d skipped.', $changes, $changes === 1 ? '' : 's', $apply ? 'made' : 'to make', $skipped ) );
