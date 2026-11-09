<?php

/**
 * Checkout and order-received presentation (CHECKOUT_FIXES.md, frames 72g/72h).
 *
 * Checkout is WooCommerce's Checkout block, so it's shaped from here rather
 * than by template overrides: the page title and a "$0.00 today" line come
 * from the cart, the block's wording is overridden through WordPress's
 * script translations, a small checkout filter renames the plan line and the
 * button, Stripe's card fields get the site's look through the gateway's
 * appearance setting, and the rest is CSS (_woocommerce.scss). Payment
 * processing is untouched. Order received is a template override
 * (woocommerce/checkout/thankyou.php) using the helpers at the bottom.
 */

/**
 * The subscription in the cart, for the header line, button and terms.
 *
 * @return array{name:string, trial:bool, trial_text:string, today:string, recurring:string, first_charge:?int}|null
 */
function framework_checkout_plan(): ?array {
    if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() || ! class_exists( 'WC_Subscriptions_Product' ) ) {
        return null;
    }

    foreach ( WC()->cart->get_cart() as $item ) {
        $product = $item['data'] ?? null;
        if ( ! $product || ! WC_Subscriptions_Product::is_subscription( $product ) ) {
            continue;
        }

        $trial_length = (int) WC_Subscriptions_Product::get_trial_length( $product );
        $trial_period = (string) WC_Subscriptions_Product::get_trial_period( $product );
        $first        = WC_Subscriptions_Product::get_first_renewal_payment_date( $product, '', 'site' );

        return [
            'name'         => $product->get_name() . ' plan',
            'trial'        => $trial_length > 0,
            'trial_text'   => framework_trial_text( $trial_length, $trial_period ),
            'today'        => framework_plain_price( (float) WC()->cart->get_total( 'edit' ) ),
            'recurring'    => framework_plain_price( (float) WC_Subscriptions_Product::get_price( $product ) )
                . framework_period_suffix( (int) WC_Subscriptions_Product::get_interval( $product ), (string) WC_Subscriptions_Product::get_period( $product ) ),
            'first_charge' => $first ? strtotime( $first ) : null,
        ];
    }

    return null;
}

/** "$99.99" — wc_price without its markup. */
function framework_plain_price( float $amount ): string {
    return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
}

/** "/month", or " every 3 months". */
function framework_period_suffix( int $interval, string $period ): string {
    if ( $period === '' ) {
        return '';
    }

    return $interval <= 1 ? '/' . $period : sprintf( ' every %d %ss', $interval, $period );
}

/** "first month free", "first 14 days free", or ''. */
function framework_trial_text( int $length, string $period ): string {
    if ( $length <= 0 || $period === '' ) {
        return '';
    }

    return $length === 1 ? "first $period free" : sprintf( 'first %d %ss free', $length, $period );
}

/** The checkout form itself (not order received or order pay). */
function framework_is_checkout_form(): bool {
    return function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url();
}

// ── Page title ─────────────────────────────────────────────────────────────
// page.php prints this as the page's h1; '' hides it.
add_filter( 'framework_page_title', function ( string $title ): string {
    if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
        return ''; // The template has its own "You're all set".
    }

    if ( framework_is_checkout_form() && ( $plan = framework_checkout_plan() ) ) {
        return $plan['trial'] ? __( 'Start your free month', 'framework' ) : __( 'Start your subscription', 'framework' );
    }

    return $title;
} );

// The line under the title: says up front that nothing is charged today.
add_filter( 'render_block_woocommerce/checkout', function ( string $content ): string {
    $plan = framework_checkout_plan();
    if ( ! $plan ) {
        return $content;
    }

    if ( $plan['trial'] && $plan['first_charge'] ) {
        $line = sprintf(
            /* translators: 1: plan, 2: today's total, 3: recurring price, 4: date */
            __( '%1$s · %2$s today, then %3$s from %4$s', 'framework' ),
            $plan['name'], $plan['today'], $plan['recurring'], wp_date( 'M j, Y', $plan['first_charge'] )
        );
    } else {
        $line = sprintf( __( '%1$s · %2$s, starting today', 'framework' ), $plan['name'], $plan['recurring'] );
    }

    return '<p class="c-checkout-lead">' . esc_html( $line ) . '</p>' . framework_checkout_account_notice() . $content;
} );

/**
 * A shop admin who came from the app isn't signed out (see the init hook in
 * inc/woocommerce.php), so say whose account this purchase goes on.
 */
function framework_checkout_account_notice(): string {
    if ( ! is_user_logged_in() || ! current_user_can( 'manage_woocommerce' ) || framework_store_ref() === ''
        || framework_store_expected_customer() === get_current_user_id() ) {
        return '';
    }

    return sprintf(
        '<p class="c-store-signin woocommerce-info">%s <a href="%s">%s</a></p>',
        esc_html( sprintf(
            /* translators: %s: the signed-in account's email */
            __( "You're signed in to the store as %s, so this subscription will go on that account.", 'framework' ),
            wp_get_current_user()->user_email
        ) ),
        esc_url( wc_logout_url( home_url( '/pricing/' ) ) ),
        esc_html__( 'Sign out to use a different email', 'framework' )
    );
}

// ── Fields ─────────────────────────────────────────────────────────────────
// The store never uses a phone number, and a note means nothing on a
// subscription. (The block checkout reads the phone setting from this option.)
add_filter( 'pre_option_woocommerce_checkout_phone_field', fn () => 'hidden' );
add_filter( 'render_block_woocommerce/checkout-order-note-block', '__return_empty_string' );

// ── Wording ────────────────────────────────────────────────────────────────
// The block's strings are ordinary script translations, so they're replaced
// before the checkout scripts load. Only the checkout form gets these.
add_action( 'wp_enqueue_scripts', function (): void {
    if ( ! framework_is_checkout_form() ) {
        return;
    }

    $plan = framework_checkout_plan();

    $woocommerce = [
        'Contact information' => 'Contact',
        'Payment options'     => 'Card',
        'Or continue below'   => 'or pay with a card',
        'Add coupons'         => 'Have a coupon?',
    ];
    $subscriptions = [
        'Total due today'         => 'Due today',
        'Starting: %1$s'          => 'From %1$s',
        // Set after the price by the CSS: "$99.99" + "/month".
        'Monthly recurring total' => '/month',
        'Yearly recurring total'  => '/year',
        'Weekly recurring total'  => '/week',
        'Daily recurring total'   => '/day',
    ];

    if ( $plan && $plan['trial'] ) {
        if ( $plan['first_charge'] ) {
            $woocommerce['By proceeding with your purchase you agree to our %1$s and %2$s'] = sprintf(
                "Cancel any time before %s and you won't be charged. By continuing you agree to the %%1\$s and %%2\$s.",
                wp_date( 'M j', $plan['first_charge'] )
            );
        }
    }

    $data = static function ( array $strings, string $domain ): array {
        $out = [ '' => [ 'domain' => $domain, 'lang' => 'en' ] ];
        foreach ( $strings as $from => $to ) {
            $out[ $from ] = [ $to ];
        }
        return $out;
    };

    wp_add_inline_script(
        'wp-i18n',
        sprintf(
            'wp.i18n.setLocaleData(%s,"woocommerce");wp.i18n.setLocaleData(%s,"woocommerce-subscriptions");',
            wp_json_encode( $data( $woocommerce, 'woocommerce' ) ),
            wp_json_encode( $data( $subscriptions, 'woocommerce-subscriptions' ) )
        )
    );

    // The plan line in the summary: "Crew plan · first month free". Its own
    // handle, so it declares the checkout API it uses.
    wp_register_script( 'framework-checkout', false, [ 'wc-blocks-checkout' ], null, true );
    wp_enqueue_script( 'framework-checkout' );
    wp_add_inline_script(
        'framework-checkout',
        <<<'JS'
(function () {
    var api = window.wc && window.wc.blocksCheckout;
    if (!api || !api.registerCheckoutFilters) { return; }
    api.registerCheckoutFilters('framework', {
        itemName: function (name, extensions, args) {
            var s = extensions && extensions.subscriptions;
            if (!args || args.context !== 'summary' || !s || !s.billing_period) { return name; }
            var trial = '';
            if (s.trial_length > 0 && s.trial_period) {
                trial = s.trial_length === 1 ? 'first ' + s.trial_period + ' free' : 'first ' + s.trial_length + ' ' + s.trial_period + 's free';
            }
            return name + ' plan' + (trial ? ' · ' + trial : '');
        }
    });
})();
JS
    );
}, 20 );

// The button. Subscriptions sets it for subscription carts (block checkout
// reads it from there), so it's changed through Subscriptions' own filter.
add_filter( 'wcs_place_subscription_order_text', function ( $text ) {
    $plan = framework_checkout_plan();

    return $plan && $plan['trial'] ? __( 'Start free month', 'framework' ) : $text;
} );

// ── Stripe card fields ─────────────────────────────────────────────────────
// They're inside Stripe's iframe, out of reach of CSS. The gateway takes an
// appearance for the block checkout; without one it guesses from the page,
// which is where the pale yellow borders came from.
add_filter( 'wc_stripe_upe_params', function ( array $params ): array {
    $params['blocksAppearance'] = [
        'theme'     => 'stripe',
        'labels'    => 'above',
        'variables' => [
            'fontFamily'           => 'Barlow, system-ui, sans-serif',
            'fontSizeBase'         => '16px',
            'colorPrimary'         => '#122e50',
            'colorText'            => '#122e50',
            'colorTextSecondary'   => '#5f7c99',
            'colorTextPlaceholder' => '#5f7c99',
            'colorBackground'      => '#ffffff',
            'colorDanger'          => '#b3261e',
            'borderRadius'         => '0px',
            'spacingUnit'          => '4px',
        ],
        'rules'     => [
            '.Input'          => [ 'border' => '1px solid rgba(18,46,80,.22)', 'boxShadow' => 'none', 'padding' => '12px 13px', 'fontSize' => '16px' ],
            '.Input:focus'    => [ 'border' => '1px solid #122e50', 'boxShadow' => '0 0 0 1px #122e50' ],
            // The site's form-label style: condensed caps.
            '.Label'          => [ 'fontFamily' => '"Barlow Condensed", system-ui, sans-serif', 'fontSize' => '14px', 'fontWeight' => '700', 'letterSpacing' => '0.06em', 'textTransform' => 'uppercase', 'color' => '#122e50' ],
            '.Tab'            => [ 'border' => '1px solid rgba(18,46,80,.22)', 'boxShadow' => 'none' ],
            '.Tab--selected'  => [ 'border' => '1px solid #122e50', 'boxShadow' => '0 0 0 1px #122e50' ],
            '.Error'          => [ 'fontSize' => '14px' ],
        ],
    ];

    return $params;
} );

// Lets the gateway hand Barlow (from Google Fonts) to the card fields.
add_filter( 'wc_stripe_upe_permitted_font_domains', function ( $domains ) {
    return array_merge( (array) $domains, [ 'fonts.googleapis.com' ] );
} );

// ── Order received helpers (woocommerce/checkout/thankyou.php) ──────────────

/** The subscription an order started, if any. */
function framework_order_subscription( WC_Order $order ): ?WC_Subscription {
    if ( ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
        return null;
    }

    $subscriptions = wcs_get_subscriptions_for_order( $order, [ 'order_type' => [ 'parent', 'renewal' ] ] );

    return $subscriptions ? reset( $subscriptions ) : null;
}

/** "Visa ending 4242" from the saved Stripe card, or the method's title. */
function framework_order_card_label( WC_Order $order, ?WC_Subscription $subscription ): string {
    $source = '';
    foreach ( array_filter( [ $subscription, $order ] ) as $object ) {
        $source = (string) $object->get_meta( '_stripe_source_id' );
        if ( $source !== '' ) {
            break;
        }
    }

    if ( $source !== '' && $order->get_customer_id() ) {
        foreach ( WC_Payment_Tokens::get_customer_tokens( $order->get_customer_id() ) as $token ) {
            if ( $token->get_token() === $source && method_exists( $token, 'get_last4' ) ) {
                $brand = method_exists( $token, 'get_card_type' ) ? wc_get_credit_card_type_label( $token->get_card_type() ) : '';
                return trim( sprintf( '%s ending %s', $brand ?: __( 'Card', 'framework' ), $token->get_last4() ) );
            }
        }
    }

    return $order->get_payment_method_title();
}

/** "(724) 759-0223" for a 10-digit US number, otherwise as entered. */
function framework_format_phone( string $phone ): string {
    $digits = preg_replace( '/\D+/', '', $phone );
    if ( strlen( $digits ) === 11 && $digits[0] === '1' ) {
        $digits = substr( $digits, 1 );
    }

    return strlen( $digits ) === 10
        ? sprintf( '(%s) %s-%s', substr( $digits, 0, 3 ), substr( $digits, 3, 3 ), substr( $digits, 6 ) )
        : $phone;
}

// The template prints its own summary, so WooCommerce's order and related-
// subscription tables, and Subscriptions' "will be activated" note, stay off.
add_action( 'woocommerce_thankyou', function (): void {
    remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
    if ( class_exists( 'WC_Subscriptions_Order' ) ) {
        remove_action( 'woocommerce_thankyou', [ 'WC_Subscriptions_Order', 'subscription_thank_you' ] );
    }
}, 0 );
