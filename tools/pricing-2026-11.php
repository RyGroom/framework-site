<?php
/**
 * One-off pricing change (Nov 2026): whole-dollar prices.
 *
 *   Individual   $59.99 -> $59 / month
 *   Crew         $99.99 -> $149 / month (6 logins)
 *   Extra login  $14.99 -> $15 / month each
 *
 * Updates the three store products (found by SKU), the Pricing page's plan
 * prices and FAQ answers, and the price sentence in the Terms of Service.
 * Also creates two private coupons that aren't advertised anywhere:
 *
 *   FW-LIFETIME-2YU54   100% off every renewal, forever (no card needed).
 *   FW-YEAR-VWT5V       100% off the next 11 renewals. With the free first
 *                       month that's a full year free; a card is still
 *                       taken at checkout and charged from month 13.
 *
 * Both work on any plan and any extra logins, once per customer. The
 * lifetime code is capped at 25 uses in total so a leaked code can't run
 * away; raise it in WooCommerce → Marketing → Coupons.
 *
 * Existing subscriptions keep the price they signed up at (WooCommerce
 * stores it on the subscription); only new checkouts and new plan or
 * login changes from the app use the new prices.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/pricing-2026-11.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/pricing-2026-11.php apply     (make the changes)
 *
 * Text is only changed where it still says exactly what's expected, so a
 * later hand edit is never overwritten. Safe to run twice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
    WP_CLI::error( 'WooCommerce is not active.' );
}

$apply   = in_array( 'apply', $args ?? [], true );
$changes = 0;
$say     = function ( string $line ) {
    WP_CLI::log( $line );
};

$say( $apply ? 'Applying pricing changes.' : 'Dry run: nothing will be saved. Add "apply" to make the changes.' );

// --------------------------------------------------------------------------
// 1. Store products
// --------------------------------------------------------------------------
$say( '' );
$say( 'Products:' );
$prices = [
    'framework-individual'  => '59',
    'framework-crew'        => '149',
    'framework-extra-login' => '15',
];
$product_ids = [];
foreach ( $prices as $sku => $price ) {
    $id = wc_get_product_id_by_sku( $sku );
    if ( ! $id ) {
        $say( "  MISSING: no product with SKU $sku" );
        continue;
    }
    $product_ids[] = $id;
    $current       = get_post_meta( $id, '_subscription_price', true );
    if ( (float) $current === (float) $price && (float) get_post_meta( $id, '_regular_price', true ) === (float) $price ) {
        $say( "  already done: $sku = $price" );
        continue;
    }
    $changes++;
    $say( "  $sku: $current -> $price" );
    if ( $apply ) {
        $product = wc_get_product( $id );
        $product->set_regular_price( $price );
        $product->set_price( $price );
        $product->update_meta_data( '_subscription_price', $price );
        $product->save();
    }
}

// --------------------------------------------------------------------------
// 2. Page text (Pricing page fields, FAQ answers, Terms of Service)
// --------------------------------------------------------------------------
$say( '' );
$say( 'Page text:' );

// Exact field values (ACF plan prices).
$exact = [
    '$59.99' => '$59',
    '$99.99' => '$149',
];
// Phrases inside longer text (FAQ answers, plan bullets, Terms).
$phrases = [
    'Extra logins for $14.99 per month each'                    => 'Extra logins for $15 per month each',
    'Extra logins are $14.99 per month each'                    => 'Extra logins are $15 per month each',
    'each extra login is $14.99 per month'                      => 'each extra login is $15 per month',
    'The Individual plan is $59.99 per month'                   => 'The Individual plan is $59 per month',
    'The Crew plan is $99.99 per month'                         => 'The Crew plan is $149 per month',
    'plus $14.99 per month for each additional login'           => 'plus $15 per month for each additional login',
];

global $wpdb;
$pages = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status IN ('publish','draft','private')" );

foreach ( $pages as $page_id ) {
    $page_id = (int) $page_id;
    $title   = get_the_title( $page_id );

    // ACF fields (not the "_field" reference keys).
    foreach ( get_post_meta( $page_id ) as $key => $values ) {
        if ( str_starts_with( $key, '_' ) || ! is_string( $values[0] ?? null ) ) {
            continue;
        }
        $value = $values[0];
        $new   = $exact[ $value ] ?? strtr( $value, $phrases );
        if ( $new !== $value ) {
            $changes++;
            $say( "  $title ($page_id) $key: " . wp_trim_words( $value, 14 ) . ' -> ' . wp_trim_words( $new, 14 ) );
            if ( $apply ) {
                update_post_meta( $page_id, $key, wp_slash( $new ) );
            }
        }
    }

    // Body content (the Terms of Service).
    $content = get_post_field( 'post_content', $page_id, 'raw' );
    $updated = strtr( $content, $phrases );
    if ( $updated !== $content ) {
        $changes++;
        $say( "  $title ($page_id) content: price sentence updated" );
        if ( $apply ) {
            wp_update_post( [ 'ID' => $page_id, 'post_content' => wp_slash( $updated ) ] );
        }
    }
}

// Anything that still mentions an old price needs a look by hand.
$left = $wpdb->get_results(
    "SELECT p.ID, p.post_title FROM {$wpdb->posts} p
     WHERE p.post_type = 'page' AND p.post_status IN ('publish','draft','private')
       AND ( p.post_content REGEXP '59[.]99|99[.]99|14[.]99'
          OR EXISTS ( SELECT 1 FROM {$wpdb->postmeta} m WHERE m.post_id = p.ID AND m.meta_key NOT LIKE '\\_%' AND m.meta_value REGEXP '59[.]99|99[.]99|14[.]99' ) )"
);
if ( $apply ) {
    foreach ( $left as $row ) {
        $say( "  STILL MENTIONS AN OLD PRICE (edit by hand): {$row->post_title} ({$row->ID})" );
    }
}

// --------------------------------------------------------------------------
// 3. Private coupons
// --------------------------------------------------------------------------
$say( '' );
$say( 'Coupons:' );
$coupons = [
    'fw-lifetime-2yu54' => [
        'description' => 'Lifetime free (private, not advertised). 100% off every renewal, forever.',
        'payments'    => '',    // unlimited
        'limit'       => 25,
    ],
    'fw-year-vwt5v'     => [
        'description' => 'One year free (private, not advertised). Free first month + 11 free renewals; full price from month 13.',
        'payments'    => '11',
        'limit'       => 0,     // no overall cap
    ],
];
foreach ( $coupons as $code => $c ) {
    if ( wc_get_coupon_id_by_code( $code ) ) {
        $say( '  already exists: ' . strtoupper( $code ) );
        continue;
    }
    $changes++;
    $say( '  create ' . strtoupper( $code ) . ': 100% recurring, ' . ( $c['payments'] ? "{$c['payments']} renewals" : 'every renewal' ) . ', once per customer' . ( $c['limit'] ? ", max {$c['limit']} uses" : '' ) );
    if ( $apply ) {
        $coupon = new WC_Coupon();
        $coupon->set_code( $code );
        $coupon->set_description( $c['description'] );
        $coupon->set_discount_type( 'recurring_percent' );
        $coupon->set_amount( 100 );
        $coupon->set_individual_use( true );
        $coupon->set_product_ids( $product_ids );
        $coupon->set_usage_limit_per_user( 1 );
        if ( $c['limit'] ) {
            $coupon->set_usage_limit( $c['limit'] );
        }
        $coupon->update_meta_data( '_wcs_number_payments', $c['payments'] );
        $coupon->save();
    }
}

$say( '' );
if ( $apply ) {
    WP_CLI::success( "$changes change(s) made." );
} else {
    WP_CLI::success( "$changes change(s) to make. Run again with \"apply\" to save them." );
}
