<?php
/**
 * Google tag (Google Ads / GA4). Off until an ID is set in wp-config.php:
 *
 *   define( 'FRAMEWORK_GTAG_ID', 'AW-123456789' );              // or 'AW-…,G-…' for Ads + GA4
 *   define( 'FRAMEWORK_ADS_CONVERSION', 'AW-123456789/AbCdEf' ); // Ads conversion action's "send_to"
 *
 * Loaded on every page, so Google's conversion linker keeps the ad click
 * (gclid) in a first-party cookie from the landing page (/start/,
 * page-landing.php, which prints this itself) through to the order-received
 * page, even with the trip to the app's sign-up in between.
 *
 * The conversion fires once per order on order-received. Its value is the
 * subscription's monthly price: the order itself is $0 in the free month.
 */

defined( 'ABSPATH' ) || exit;

/** The configured tag IDs, e.g. [ 'AW-123', 'G-ABC' ]. */
function framework_gtag_ids(): array {
    $raw = defined( 'FRAMEWORK_GTAG_ID' ) ? (string) FRAMEWORK_GTAG_ID : '';

    return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ), static fn ( $id ) => preg_match( '/^[A-Z]{1,3}-[A-Z0-9-]+$/', $id ) ) );
}

/** The loader + config script, or '' when no tag is set. */
function framework_gtag_snippet(): string {
    $ids = framework_gtag_ids();
    if ( ! $ids ) {
        return '';
    }

    $config = '';
    foreach ( $ids as $id ) {
        $config .= 'gtag(\'config\',' . wp_json_encode( $id ) . ');';
    }

    return '<script async src="https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ids[0] ) . '"></script>' . "\n"
        . '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag(\'js\',new Date());' . $config . '</script>' . "\n";
}

add_action( 'wp_head', function (): void {
    echo framework_gtag_snippet(); // phpcs:ignore WordPress.Security.EscapeOutput -- built from validated IDs
}, 1 );

// The Google Ads conversion, once per order (a reload of the page doesn't count it twice).
add_action( 'woocommerce_thankyou', function ( $order_id ): void {
    $send_to = defined( 'FRAMEWORK_ADS_CONVERSION' ) ? (string) FRAMEWORK_ADS_CONVERSION : '';
    $order   = $order_id ? wc_get_order( $order_id ) : null;
    if ( $send_to === '' || ! framework_gtag_ids() || ! $order || $order->has_status( 'failed' ) || $order->get_meta( '_framework_ads_conversion_sent' ) ) {
        return;
    }

    $value = (float) $order->get_total();
    if ( function_exists( 'framework_order_subscription' ) && ( $subscription = framework_order_subscription( $order ) ) ) {
        $value = max( $value, (float) $subscription->get_total() );
    }

    printf(
        "<script>gtag('event','conversion',%s);</script>\n",
        wp_json_encode( [
            'send_to'        => $send_to,
            'value'          => round( $value, 2 ),
            'currency'       => $order->get_currency(),
            'transaction_id' => (string) $order->get_order_number(),
        ] )
    );

    $order->update_meta_data( '_framework_ads_conversion_sent', time() );
    $order->save();
}, 5 );
