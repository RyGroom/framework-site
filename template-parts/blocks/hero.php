<?php
$eyebrow   = get_sub_field( 'eyebrow' );
$heading   = get_sub_field( 'heading' );
$text      = get_sub_field( 'text' );
$primary   = get_sub_field( 'primary_button' );
$secondary = get_sub_field( 'secondary_button' );
$image     = get_sub_field( 'image' );

// *word* in the heading becomes an orange highlight.
$heading_html = preg_replace( '/\*([^*]+)\*/', '<span class="c-hero__mark">$1</span>', esc_html( (string) $heading ) );
?>
<section class="c-hero<?php echo $image ? ' c-hero--split' : ''; ?>">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="<?php echo $image ? 'col-lg-5' : 'col-lg-9 mx-auto text-center'; ?>">
                <?php if ( $eyebrow ) : ?><p class="c-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
                <?php if ( $heading ) : ?><h1 class="c-hero__heading"><?php echo $heading_html; ?></h1><?php endif; ?>
                <?php if ( $text ) : ?><p class="lead c-hero__text"><?php echo esc_html( $text ); ?></p><?php endif; ?>
                <?php if ( $primary || $secondary ) : ?>
                    <div class="d-flex flex-wrap gap-3 mt-4 <?php echo $image ? '' : 'justify-content-center'; ?>">
                        <?php framework_button( $primary, 'btn btn-primary btn-lg' ); ?>
                        <?php framework_button( $secondary, 'btn btn-outline-dark btn-lg' ); ?>
                    </div>
                <?php endif; ?>
                <?php if ( have_rows( 'points' ) ) : ?>
                    <ul class="c-hero__points list-unstyled d-flex flex-wrap gap-3 gap-sm-4 mt-4 mb-0 <?php echo $image ? '' : 'justify-content-center'; ?>">
                        <?php while ( have_rows( 'points' ) ) : the_row(); ?>
                            <li>
                                <?php if ( $icon = get_sub_field( 'icon' ) ) : ?><i class="bi bi-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i><?php endif; ?>
                                <?php echo esc_html( get_sub_field( 'text' ) ); ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if ( $image ) : ?>
                <div class="col-lg-7">
                    <div class="c-hero__media">
                        <?php echo wp_get_attachment_image( $image['ID'], 'full', false, [ 'class' => 'c-hero__image', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '(min-width: 992px) 700px, 100vw' ] ); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
