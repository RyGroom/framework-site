<?php
$background = get_sub_field( 'background' ) ?: 'light';
$image_side = get_sub_field( 'image_side' ) ?: 'right';
$eyebrow    = get_sub_field( 'eyebrow' );
$heading    = get_sub_field( 'heading' );
$text       = get_sub_field( 'text' );
$button     = get_sub_field( 'button' );
$image      = get_sub_field( 'image' );
?>
<section class="c-section c-section--<?php echo esc_attr( $background ); ?> c-split">
    <div class="container">
        <div class="row align-items-center gy-5 gx-lg-5">
            <div class="col-lg-6 <?php echo $image_side === 'left' ? 'order-lg-2' : ''; ?>">
                <?php if ( $eyebrow ) : ?><p class="c-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
                <?php if ( $heading ) : ?><h2 class="c-section__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
                <?php if ( $text ) : ?><p class="lead"><?php echo esc_html( $text ); ?></p><?php endif; ?>
                <?php if ( have_rows( 'bullets' ) ) : ?>
                    <ul class="c-split__list list-unstyled mt-4 mb-0">
                        <?php while ( have_rows( 'bullets' ) ) : the_row(); ?>
                            <li><i class="bi bi-check-lg c-tick" aria-hidden="true"></i><?php echo esc_html( get_sub_field( 'item' ) ); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
                <?php if ( $button ) : ?><div class="mt-4"><?php framework_button( $button, 'btn btn-outline-dark' ); ?></div><?php endif; ?>
            </div>
            <div class="col-lg-6 <?php echo $image_side === 'left' ? 'order-lg-1' : ''; ?>">
                <?php if ( $image ) : ?>
                    <?php echo wp_get_attachment_image( $image['ID'], 'large', false, [ 'class' => 'img-fluid c-split__image' ] ); ?>
                <?php else : ?>
                    <div class="c-split__placeholder" aria-hidden="true"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
