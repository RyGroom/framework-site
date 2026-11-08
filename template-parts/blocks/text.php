<?php
$heading = get_sub_field( 'heading' );
$body    = get_sub_field( 'body' );
?>
<section class="c-section c-section--light c-text">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8"><div class="c-content__inner">
                <?php if ( $heading ) : ?><h2 class="c-section__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
                <?php if ( $body ) : ?><div class="c-content__body"><?php echo wp_kses_post( $body ); ?></div><?php endif; ?>
            </div></div>
        </div>
    </div>
</section>
