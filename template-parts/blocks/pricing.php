<?php
$eyebrow      = get_sub_field( 'eyebrow' );
$heading      = get_sub_field( 'heading' );
$intro        = get_sub_field( 'intro' );
$button_label = get_sub_field( 'button_label' ) ?: __( 'Get started', 'framework' );
$note         = get_sub_field( 'note' );

// A company that has subscribed before gets no free month, so nothing here may promise one.
$returning = function_exists( 'framework_store_is_returning' ) && framework_store_is_returning();
if ( $returning ) {
    $heading      = get_sub_field( 'heading_returning' ) ?: __( 'Welcome back', 'framework' );
    $intro        = get_sub_field( 'intro_returning' ) ?: __( 'Choose a plan to turn your account back on. Every plan includes every feature and integration, with nothing extra to unlock. Your price depends on how many people need to log in.', 'framework' );
    $button_label = get_sub_field( 'button_label_returning' ) ?: __( 'Subscribe', 'framework' );
}

// The plan picked on the ads landing page (/start/), passed on by the app's
// subscribe page as ?plan=. Only marked; both plans can still be chosen.
$picked = isset( $_GET['plan'] ) && in_array( $_GET['plan'], [ 'individual', 'crew' ], true ) ? 'framework-' . $_GET['plan'] : '';
?>
<section class="c-section c-section--cream c-pricing">
    <div class="container">
        <div class="c-pricing__intro text-center mx-auto">
            <?php if ( $eyebrow ) : ?><p class="c-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
            <?php if ( $heading ) : ?><h1 class="c-pricing__heading"><?php echo esc_html( $heading ); ?></h1><?php endif; ?>
            <?php if ( $intro ) : ?><p class="lead"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
        </div>

        <?php if ( have_rows( 'plans' ) ) : ?>
            <div class="row g-4 justify-content-center">
                <?php while ( have_rows( 'plans' ) ) : the_row(); ?>
                    <?php $is_pick = $picked !== '' && (string) get_sub_field( 'sku' ) === $picked; ?>
                    <div class="col-md-6 col-lg-5">
                        <div class="c-pricing__plan<?php echo $is_pick ? ' is-pick' : ''; ?>">
                            <?php if ( $is_pick ) : ?><p class="c-pricing__pick"><?php esc_html_e( 'Your pick', 'framework' ); ?></p><?php endif; ?>
                            <h2 class="c-pricing__name"><?php echo esc_html( get_sub_field( 'name' ) ); ?></h2>
                            <p class="c-pricing__price">
                                <span class="c-pricing__amount"><?php echo esc_html( get_sub_field( 'price' ) ); ?></span>
                                <span class="c-pricing__period"><?php echo esc_html( $returning ? ( get_sub_field( 'period_returning' ) ?: __( 'per month', 'framework' ) ) : get_sub_field( 'period' ) ); ?></span>
                            </p>
                            <p class="c-pricing__summary"><?php echo esc_html( get_sub_field( 'summary' ) ); ?></p>
                            <?php if ( have_rows( 'items' ) ) : ?>
                                <ul class="c-pricing__items">
                                    <?php while ( have_rows( 'items' ) ) : the_row(); ?>
                                        <?php $item_url = get_sub_field( 'url' ); ?>
                                        <li>
                                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                                            <?php if ( $item_url ) : ?>
                                                <a href="<?php echo esc_url( $item_url ); ?>"><?php echo esc_html( get_sub_field( 'item' ) ); ?></a>
                                            <?php else : ?>
                                                <?php echo esc_html( get_sub_field( 'item' ) ); ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                            <a class="btn btn-primary btn-lg c-pricing__button" href="<?php echo esc_url( framework_plan_checkout_url( (string) get_sub_field( 'sku' ) ) ?: framework_signup_url() ); ?>"><?php echo esc_html( $button_label ); ?></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <?php if ( $note ) : ?><p class="c-pricing__note text-center mx-auto"><?php echo esc_html( $note ); ?></p><?php endif; ?>
    </div>
</section>
