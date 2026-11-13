<?php
/**
 * One-off pricing change (Nov 2026): the Individual plan goes $59 -> $69 / month.
 * Crew ($149) and extra logins ($15) are unchanged.
 *
 * Updates the Individual store product (found by SKU), the Pricing page's
 * plan price, and the price sentence in the Terms of Service. The app's own
 * label (App\Support\SeatPlan::INDIVIDUAL_PRICE_LABEL) changes with the app
 * deploy.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/pricing-2026-11-individual-69.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/pricing-2026-11-individual-69.php apply     (make the changes)
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

$say( $apply ? 'Applying the Individual price change.' : 'Dry run: nothing will be saved. Add "apply" to make the changes.' );

// --------------------------------------------------------------------------
// 1. Store product
// --------------------------------------------------------------------------
$say( '' );
$say( 'Product:' );
$sku   = 'framework-individual';
$price = '69';
$id    = wc_get_product_id_by_sku( $sku );
if ( ! $id ) {
    $say( "  MISSING: no product with SKU $sku" );
} else {
    $current = get_post_meta( $id, '_subscription_price', true );
    if ( (float) $current === (float) $price && (float) get_post_meta( $id, '_regular_price', true ) === (float) $price ) {
        $say( "  already done: $sku = $price" );
    } else {
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
}

// --------------------------------------------------------------------------
// 2. Page text (Pricing page plan price, Terms of Service)
// --------------------------------------------------------------------------
$say( '' );
$say( 'Page text:' );

// The Pricing page's Individual plan price field (ACF, "sections_N_plans_N_price").
$exact_price = [ '$59' => '$69' ];
// The sentence in the Terms of Service.
$phrases = [
    'The Individual plan is $59 per month' => 'The Individual plan is $69 per month',
];

global $wpdb;
$pages = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status IN ('publish','draft','private')" );

foreach ( $pages as $page_id ) {
    $page_id = (int) $page_id;
    $title   = get_the_title( $page_id );

    foreach ( get_post_meta( $page_id ) as $key => $values ) {
        if ( str_starts_with( $key, '_' ) || ! is_string( $values[0] ?? null ) ) {
            continue;
        }
        $value = $values[0];
        // Only plan price fields take the exact swap, so no other "$59" is touched.
        $new = preg_match( '/_price$/', $key ) && isset( $exact_price[ $value ] ) ? $exact_price[ $value ] : strtr( $value, $phrases );
        if ( $new !== $value ) {
            $changes++;
            $say( "  $title ($page_id) $key: " . wp_trim_words( $value, 14 ) . ' -> ' . wp_trim_words( $new, 14 ) );
            if ( $apply ) {
                update_post_meta( $page_id, $key, wp_slash( $new ) );
            }
        }
    }

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

// Anything that still mentions $59 needs a look by hand.
if ( $apply ) {
    $left = $wpdb->get_results(
        "SELECT p.ID, p.post_title FROM {$wpdb->posts} p
         WHERE p.post_type = 'page' AND p.post_status IN ('publish','draft','private')
           AND ( p.post_content LIKE '%\$59%'
              OR EXISTS ( SELECT 1 FROM {$wpdb->postmeta} m WHERE m.post_id = p.ID AND m.meta_key NOT LIKE '\\_%' AND m.meta_value LIKE '%\$59%' ) )"
    );
    foreach ( $left as $row ) {
        $say( "  STILL MENTIONS \$59 (edit by hand): {$row->post_title} ({$row->ID})" );
    }
}

$say( '' );
if ( $apply ) {
    WP_CLI::success( "$changes change(s) made." );
} else {
    WP_CLI::success( "$changes change(s) to make. Run again with \"apply\" to save them." );
}
