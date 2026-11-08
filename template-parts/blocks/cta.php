<?php
$heading   = get_sub_field( 'heading' );
$text      = get_sub_field( 'text' );
$button    = get_sub_field( 'button' );
$secondary = get_sub_field( 'secondary_button' );

// A sign-up call to action is for new companies, and it promises their free
// month. A company that has subscribed before already has an account and gets
// no free month, so it doesn't see one.
if ( function_exists( 'framework_store_is_returning' ) && framework_store_is_returning()
    && is_array( $button ) && untrailingslashit( (string) ( $button['url'] ?? '' ) ) === untrailingslashit( framework_signup_url() ) ) {
    return;
}
?>
<section class="c-cta">
    <div class="container text-center">
        <?php if ( $heading ) : ?><h2 class="c-cta__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
        <?php if ( $text ) : ?><p class="lead c-cta__text mx-auto"><?php echo esc_html( $text ); ?></p><?php endif; ?>
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <?php framework_button( $button, 'btn btn-primary btn-lg' ); ?>
            <?php framework_button( $secondary, 'btn btn-outline-light btn-lg' ); ?>
        </div>
    </div>
</section>
