<?php

/**
 * The store side of Framework's subscriptions.
 *
 * A purchase only means something if it can be tied to a company in the app.
 * The app's subscribe page links here with a signed framework_ref. This file
 * remembers it through checkout and saves it on the subscription, where the
 * app's webhook reads it. Only the app can check the signature, so here it's
 * just carried along. See docs/integrations.md in the app.
 *
 * Checked on a local store with WooCommerce 11.2, Subscriptions 9.4, the
 * Stripe gateway and block checkout: the reference reaches the subscription's
 * meta through woocommerce_store_api_checkout_order_processed, the thank-you
 * hook runs, and the redirects, renewals and no-second-free-month rules below
 * behave as described. Every hook does nothing without Subscriptions.
 */

const FRAMEWORK_REF_PARAM = 'framework_ref';

// The app adds this for a company that has subscribed before: no second free month.
const FRAMEWORK_RETURNING_PARAM = 'framework_returning';

// And the store customer that company is linked to, when it has one.
const FRAMEWORK_CUSTOMER_PARAM = 'framework_customer';

/** The store customer the app says this company is linked to, or 0. */
function framework_store_expected_customer(): int {
    foreach ( [ $_GET[ FRAMEWORK_CUSTOMER_PARAM ] ?? null, $_COOKIE[ FRAMEWORK_CUSTOMER_PARAM ] ?? null ] as $value ) {
        if ( is_string( $value ) && ctype_digit( $value ) ) {
            return (int) $value;
        }
    }

    return 0;
}

/** Signed out, or signed in to a different account from the one the company is linked to. */
function framework_store_needs_signin(): bool {
    if ( ! is_user_logged_in() ) {
        return true;
    }

    $expected = framework_store_expected_customer();

    return $expected > 0 && get_current_user_id() !== $expected;
}

/** The reference from this request or an earlier page, or '' if there is none. */
function framework_store_ref(): string {
    foreach ( [ $_GET[ FRAMEWORK_REF_PARAM ] ?? '', $_COOKIE[ FRAMEWORK_REF_PARAM ] ?? '' ] as $value ) {
        $value = is_string( $value ) ? wp_unslash( $value ) : '';
        if ( preg_match( '/^\d+\.[0-9a-f]{32}$/', $value ) ) {
            return $value;
        }
    }

    return '';
}

/** A checkout link for one plan, or '' to fall back to the app's sign-up. */
function framework_plan_checkout_url( string $sku ): string {
    if ( $sku === '' || framework_store_ref() === '' || ! class_exists( 'WooCommerce' ) ) {
        return '';
    }

    $product_id = wc_get_product_id_by_sku( $sku );

    return $product_id ? add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() ) : '';
}

/**
 * A company coming back after a subscription, which gets no second free month.
 * The app's flag can be deleted from the link by hand, so a store account that
 * has had a subscription counts too, and checkout makes returning companies
 * sign in to it. The app also refuses to turn access on for a new
 * subscription that is still in a free month, so deleting the flag only ever
 * gets a free month with no access.
 */
function framework_store_is_returning(): bool {
    foreach ( [ $_GET[ FRAMEWORK_RETURNING_PARAM ] ?? null, $_COOKIE[ FRAMEWORK_RETURNING_PARAM ] ?? null ] as $value ) {
        if ( $value === '1' ) {
            return true;
        }
    }

    // Only a subscription that really started. A declined card leaves a pending
    // one behind, and retrying must not lose the free month they were promised.
    return is_user_logged_in() && function_exists( 'wcs_user_has_subscription' )
        && wcs_user_has_subscription( get_current_user_id(), 0, [ 'active', 'on-hold', 'pending-cancel', 'cancelled', 'expired', 'switched' ] );
}

function framework_set_ref_cookie( string $value, int $expires, string $name = FRAMEWORK_REF_PARAM ): void {
    setcookie( $name, $value, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    ] );
}

// Remember the reference when arriving from the app. Those visitors see
// checkout buttons on the pricing page, so it can't be served from a page cache.
add_action( 'init', function (): void {
    $ref = framework_store_ref();
    if ( $ref === '' ) {
        return;
    }

    if ( ! defined( 'DONOTCACHEPAGE' ) ) {
        define( 'DONOTCACHEPAGE', true );
    }

    if ( ( $_COOKIE[ FRAMEWORK_REF_PARAM ] ?? '' ) !== $ref && ! headers_sent() ) {
        // Sent here for a different company than last time (or the first time
        // since a purchase): what an earlier visitor typed into checkout is
        // stale. See framework_forget_stale_checkout_details().
        if ( isset( $_GET[ FRAMEWORK_REF_PARAM ] ) ) {
            $GLOBALS['framework_new_company'] = true;
        }
        framework_set_ref_cookie( $ref, time() + DAY_IN_SECONDS );
        $_COOKIE[ FRAMEWORK_REF_PARAM ] = $ref;
    }

    // A new link from the app says afresh whether this company is returning,
    // and which store account it uses.
    if ( isset( $_GET[ FRAMEWORK_REF_PARAM ] ) && ! headers_sent() ) {
        $returning = ( $_GET[ FRAMEWORK_RETURNING_PARAM ] ?? '' ) === '1' ? '1' : '';
        $customer  = ctype_digit( (string) ( $_GET[ FRAMEWORK_CUSTOMER_PARAM ] ?? '' ) ) ? (string) $_GET[ FRAMEWORK_CUSTOMER_PARAM ] : '';
        foreach ( [ FRAMEWORK_RETURNING_PARAM => $returning, FRAMEWORK_CUSTOMER_PARAM => $customer ] as $name => $value ) {
            if ( ( $_COOKIE[ $name ] ?? '' ) !== $value ) {
                framework_set_ref_cookie( $value, $value !== '' ? time() + DAY_IN_SECONDS : time() - HOUR_IN_SECONDS, $name );
                $_COOKIE[ $name ] = $value;
            }
        }
    }
} );

/**
 * WooCommerce keeps what a signed-out visitor typed into checkout (email,
 * name, address) in their session. A different company arriving from the app
 * in the same browser then found the previous sign-up's email filled in, and
 * could buy under it. Clear those details when the company changes. Signed-in
 * store accounts keep theirs, and so does the same company coming back.
 */
function framework_forget_stale_checkout_details(): void {
    if ( empty( $GLOBALS['framework_new_company'] ) || is_user_logged_in() || ! function_exists( 'WC' ) || ! WC()->customer ) {
        return;
    }

    $customer = WC()->customer;
    $customer->set_billing_email( '' );
    foreach ( [ 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'phone' ] as $field ) {
        $customer->{"set_billing_$field"}( '' );
        $customer->{"set_shipping_$field"}( '' );
    }
    $customer->save();
}
// After WooCommerce loads the session's customer, before add-to-cart runs (20).
add_action( 'wp_loaded', 'framework_forget_stale_checkout_details', 15 );

// No second free month: a returning company is charged at checkout. This also
// keeps "Free trial" off renewal and resubscribe carts, which are always theirs.
add_filter( 'woocommerce_subscriptions_product_trial_length', function ( $length ) {
    return framework_store_is_returning() ? 0 : $length;
} );

/** Save the reference once. An existing one stays, so it can't be swapped later. */
function framework_save_ref_on_subscription( $subscription ): void {
    $ref = framework_store_ref();
    if ( $ref === '' || ! is_object( $subscription ) || $subscription->get_meta( FRAMEWORK_REF_PARAM ) ) {
        return;
    }

    $subscription->update_meta_data( FRAMEWORK_REF_PARAM, $ref );
    $subscription->save();
}

function framework_save_ref_on_order_subscriptions( $order ): void {
    if ( function_exists( 'wcs_get_subscriptions_for_order' ) ) {
        foreach ( wcs_get_subscriptions_for_order( $order, [ 'order_type' => 'parent' ] ) as $subscription ) {
            framework_save_ref_on_subscription( $subscription );
        }
    }
}

// Classic checkout, then the order-level hooks that also cover block checkout.
add_action( 'woocommerce_checkout_subscription_created', 'framework_save_ref_on_subscription' );
add_action( 'woocommerce_checkout_order_processed', 'framework_save_ref_on_order_subscriptions' );
add_action( 'woocommerce_store_api_checkout_order_processed', 'framework_save_ref_on_order_subscriptions' );

// There's no cart page to view, so "added to your cart" only gets in the way.
add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

// One plan at a time: choosing a plan on the pricing page replaces whatever
// was in the cart. Only for that link's own product: WooCommerce Subscriptions
// rebuilds a renewal or resubscribe by adding each line in turn, and emptying
// the cart then left only the last line, so a Crew + Extra Login renewal
// came out as $14.99 for the extra login alone.
add_filter( 'woocommerce_add_to_cart_validation', function ( $passed, $product_id = 0 ) {
    $from_link = isset( $_REQUEST['add-to-cart'] ) && absint( $_REQUEST['add-to-cart'] ) === absint( $product_id );

    // Extra logins are added to a plan from the app's Billing tab. Bought alone
    // from a link they'd charge $14.99 a month and do nothing. Renewals rebuild
    // their lines without this link, so they aren't affected.
    $product = $from_link && function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
    if ( $product && $product->get_sku() === 'framework-extra-login' ) {
        wc_add_notice( 'Extra logins are added from Billing in the Framework app.', 'error' );

        return false;
    }

    if ( $passed && $from_link && function_exists( 'WC' ) && WC()->cart ) {
        WC()->cart->empty_cart();
    }

    return $passed;
}, 10, 2 );

// Plans and logins change only from the app's Billing tab, which edits the
// subscription and keeps the app's login count in step. Removing a line in My
// Account would leave the app's count alone: removing the plan and keeping an
// extra login charged $14.99 while the app still allowed a Crew's 7 logins.
add_filter( 'wcs_can_items_be_removed', '__return_false' );

// No "Renew now" in My Account. Paying next month early only confuses people
// ("why was I charged?"), and renewals happen on their own.
add_filter( 'woocommerce_subscriptions_can_user_renew_early', '__return_false' );

/** Renewals and existing customers don't start from the app, so they carry no reference. */
function framework_checkout_is_allowed_without_ref(): bool {
    if ( function_exists( 'is_wc_endpoint_url' ) && ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) ) {
        return true;
    }

    if ( function_exists( 'wcs_cart_contains_renewal' ) && wcs_cart_contains_renewal() ) {
        return true;
    }

    if ( function_exists( 'wcs_cart_contains_resubscribe' ) && wcs_cart_contains_resubscribe() ) {
        return true;
    }

    return is_user_logged_in() && function_exists( 'wcs_user_has_subscription' ) && wcs_user_has_subscription( get_current_user_id() );
}

// There's no catalog to browse, and nobody should be able to pay without a
// company to attach it to: they'd be charged and have no way in.
add_action( 'template_redirect', function (): void {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    if ( is_shop() || is_product() || is_product_taxonomy() || is_cart() ) {
        $has_items = is_cart() && WC()->cart && ! WC()->cart->is_empty();
        wp_safe_redirect( $has_items ? wc_get_checkout_url() : home_url( '/pricing/' ) );
        exit;
    }

    // A returning company buys from the store account it had: its company is
    // already linked to that customer, so a purchase from a new account would
    // be charged and turn nothing on.
    if ( is_checkout() && ! is_wc_endpoint_url() && framework_store_is_returning() && framework_store_needs_signin() ) {
        wp_safe_redirect( add_query_arg( 'framework_signin', '1', wc_get_page_permalink( 'myaccount' ) ) );
        exit;
    }

    if ( is_checkout() && framework_store_ref() === '' && ! framework_checkout_is_allowed_without_ref() ) {
        // Not wp_safe_redirect: the app is a different host.
        wp_redirect( framework_signup_url() );
        exit;
    }
} );

add_action( 'woocommerce_before_customer_login_form', function (): void {
    if ( isset( $_GET['framework_signin'] ) ) {
        echo '<p class="c-store-signin woocommerce-info">', esc_html__( "You've subscribed before, so sign in to the account you used then to continue. Your company stays linked to it.", 'framework' ), '</p>';
    }
} );

// Signed in, but to a different account from the one the company is linked to.
add_action( 'woocommerce_before_account_navigation', function (): void {
    if ( isset( $_GET['framework_signin'] ) && framework_store_needs_signin() ) {
        printf(
            '<p class="c-store-signin woocommerce-info">%s <a href="%s">%s</a></p>',
            esc_html__( "You're signed in to a different account from the one your company used before. Your company stays linked to that one, so a purchase from here wouldn't turn anything on.", 'framework' ),
            esc_url( wc_logout_url() ),
            esc_html__( 'Sign out and sign in with that account', 'framework' )
        );
    }
} );

// Back to checkout after signing in, with the plan still in the cart, or to
// the plans if signing out of another account emptied it.
add_filter( 'woocommerce_login_redirect', function ( $redirect ) {
    if ( framework_store_ref() === '' ) {
        return $redirect;
    }
    $has_cart = function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty();

    return $has_cart ? wc_get_checkout_url() : home_url( '/pricing/' );
} );

// Leaving the store: the reference cookies have done their job. The way back
// to the app (it waits there for the webhook to turn access on) is the
// "Continue to Framework" button in woocommerce/checkout/thankyou.php.
add_action( 'woocommerce_thankyou', function (): void {
    foreach ( [ FRAMEWORK_REF_PARAM, FRAMEWORK_RETURNING_PARAM, FRAMEWORK_CUSTOMER_PARAM ] as $name ) {
        if ( isset( $_COOKIE[ $name ] ) && ! headers_sent() ) {
            framework_set_ref_cookie( '', time() - HOUR_IN_SECONDS, $name );
        }
    }

    // The "Continue to Framework" button is in woocommerce/checkout/thankyou.php.
}, 1 );
