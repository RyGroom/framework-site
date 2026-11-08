<?php
/**
 * Order received — Framework override of WooCommerce's checkout/thankyou.php
 * (CHECKOUT_FIXES.md frame 72h).
 *
 * One fact list in place of the order overview, order details table and
 * related-subscriptions table, which between them showed $0.00 three times.
 * The failed-payment branch and every action WooCommerce and the gateways
 * hook into are kept as in core. Helpers: inc/woocommerce-checkout.php.
 *
 * @var WC_Order|false $order
 *
 * @package WooCommerce\Templates
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$framework_icon = static function ( string $name ): string {
	// Lucide "phone" and "mail", stroke 1.5.
	$paths = [
		'phone' => '<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>',
		'mail'  => '<path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/>',
	];

	return '<svg class="c-order__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>';
};
?>

<div class="woocommerce-order c-order">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<?php
			$default_order_failed_text = esc_html__( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' );
			$order_failed_text         = apply_filters( 'woocommerce_thankyou_order_failed_text', $default_order_failed_text, $order );
			$order_failed_text         = is_string( $order_failed_text ) ? $order_failed_text : $default_order_failed_text;
			?>

			<h1 class="c-order__title"><?php esc_html_e( "Your card didn't go through", 'framework' ); ?></h1>
			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php echo wp_kses_post( $order_failed_text ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Try again', 'framework' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<?php
			$subscription = framework_order_subscription( $order );
			$next_ts      = $subscription ? $subscription->get_time( 'next_payment', 'site' ) : 0;
			$trial_end    = $subscription ? $subscription->get_time( 'trial_end', 'site' ) : 0;
			$on_trial     = $trial_end > time();
			$next_amount  = $subscription ? framework_plain_price( (float) $subscription->get_total() ) : '';
			$next_date    = $next_ts ? wp_date( 'M j, Y', $next_ts ) : '';

			// A trial of about a month reads as "your free month".
			$trial_days = $subscription && $on_trial ? round( ( $trial_end - $subscription->get_time( 'start', 'site' ) ) / DAY_IN_SECONDS ) : 0;
			$free_what  = $trial_days >= 28 && $trial_days <= 31 ? __( 'month', 'framework' ) : __( 'trial', 'framework' );

			$plan_names = [];
			foreach ( ( $subscription ?: $order )->get_items() as $item ) {
				$plan_names[] = $item->get_name();
			}
			$plan = implode( ', ', $plan_names ) . ( $on_trial ? ( $free_what === 'month' ? ' · first month free' : ' · free trial' ) : '' );

			$is_owner = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
			$email    = $order->get_billing_email();
			$active   = $subscription && $subscription->has_status( [ 'active', 'pending-cancel' ] );
			$pending  = $subscription && $subscription->has_status( [ 'pending', 'on-hold' ] );
			?>

			<?php if ( $active || $pending ) : ?>
				<p class="c-order__status<?php echo $active ? '' : ' is-pending'; ?>">
					<span class="c-order__status-sq" aria-hidden="true"></span>
					<?php echo esc_html( $active ? __( 'Subscription active', 'framework' ) : __( 'Payment processing', 'framework' ) ); ?>
				</p>
			<?php endif; ?>
			<h1 class="c-order__title"><?php esc_html_e( "You're all set", 'framework' ); ?></h1>

			<p class="c-order__lead">
				<?php
				if ( $on_trial && $next_date ) {
					/* translators: 1: "month" or "trial", 2: amount, 3: date */
					printf( esc_html__( "Your free %1\$s has started. We'll charge %2\$s on %3\$s unless you cancel before then.", 'framework' ), esc_html( $free_what ), esc_html( $next_amount ), esc_html( $next_date ) );
				} elseif ( $next_date ) {
					printf( esc_html__( 'Your subscription has started. Your next charge is %1$s on %2$s.', 'framework' ), esc_html( $next_amount ), esc_html( $next_date ) );
				} else {
					esc_html_e( 'Thanks, your order is in.', 'framework' );
				}
				echo ' ';
				if ( $is_owner && $email ) {
					/* translators: %s: email address */
					printf( esc_html__( 'A receipt is on its way to %s.', 'framework' ), esc_html( $email ) );
				} else {
					esc_html_e( 'A receipt is on its way to your email.', 'framework' );
				}
				?>
			</p>

			<p class="c-order__continue">
				<a class="btn btn-primary c-order__btn" href="<?php echo esc_url( framework_app_url( '/subscribe?from_checkout=1' ) ); ?>"><?php esc_html_e( 'Continue to Framework', 'framework' ); ?></a>
			</p>

			<dl class="c-order__facts woocommerce-order-overview woocommerce-thankyou-order-details order_details">
				<div><dt><?php esc_html_e( 'Order', 'framework' ); ?></dt><dd>#<?php echo esc_html( $order->get_order_number() ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Date', 'framework' ); ?></dt><dd><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'M j, Y' ) ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Plan', 'framework' ); ?></dt><dd><?php echo esc_html( $plan ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Paid today', 'framework' ); ?></dt><dd><?php echo esc_html( framework_plain_price( (float) $order->get_total() ) ); ?></dd></div>
				<?php if ( $next_date ) : ?>
					<div class="is-key"><dt><?php esc_html_e( 'Next charge', 'framework' ); ?></dt><dd><?php echo esc_html( sprintf( __( '%1$s on %2$s', 'framework' ), $next_amount, $next_date ) ); ?></dd></div>
				<?php endif; ?>
				<?php if ( $order->get_payment_method_title() ) : ?>
					<div><dt><?php esc_html_e( 'Card', 'framework' ); ?></dt><dd><?php echo esc_html( framework_order_card_label( $order, $subscription ) ); ?></dd></div>
				<?php endif; ?>
			</dl>

			<?php
			$address = $order->get_formatted_billing_address();
			$phone   = $order->get_billing_phone();
			?>
			<?php if ( $address || $phone || $email ) : ?>
				<section class="c-order__address">
					<h2 class="c-order__subtitle"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>
					<?php if ( $address ) : ?>
						<address><?php echo wp_kses_post( $address ); ?></address>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<p class="c-order__contact"><?php echo $framework_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( framework_format_phone( $phone ) ); ?></p>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<p class="c-order__contact"><?php echo $framework_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $email ); ?></p>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( $subscription ) : ?>
				<p class="c-order__footer">
					<?php
					printf(
						/* translators: %s: link to the account */
						esc_html__( 'Manage or cancel your subscription any time in %s.', 'framework' ),
						'<a href="' . esc_url( $subscription->get_view_order_url() ) . '">' . esc_html__( 'your account', 'framework' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
