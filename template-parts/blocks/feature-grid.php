<?php
$anchor     = get_sub_field( 'anchor' );
$background = get_sub_field( 'background' ) ?: 'light';
$eyebrow    = get_sub_field( 'eyebrow' );
$heading    = get_sub_field( 'heading' );
$intro      = get_sub_field( 'intro' );
?>
<section class="c-section c-section--<?php echo esc_attr( $background ); ?> c-feature-grid"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
    <div class="container">
        <div class="c-section__head">
            <?php if ( $eyebrow ) : ?><p class="c-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
            <?php if ( $heading ) : ?><h2 class="c-section__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
            <?php if ( $intro ) : ?><p class="lead mb-0"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
        </div>
        <?php if ( have_rows( 'features' ) ) : ?>
            <div class="row g-4 g-lg-5">
                <?php while ( have_rows( 'features' ) ) : the_row(); ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="c-feature-grid__item position-relative">
                            <?php if ( $icon = get_sub_field( 'icon' ) ) : ?>
                                <span class="c-feature-grid__icon" aria-hidden="true"><i class="bi bi-<?php echo esc_attr( $icon ); ?>"></i></span>
                            <?php endif; ?>
                            <?php $page = get_sub_field( 'page' ); ?>
                            <h3 class="c-feature-grid__title">
                                <?php if ( $page ) : ?><a class="stretched-link" href="<?php echo esc_url( $page ); ?>"><?php endif; ?>
                                <?php echo esc_html( get_sub_field( 'title' ) ); ?>
                                <?php if ( $page ) : ?></a><?php endif; ?>
                            </h3>
                            <p class="mb-0"><?php echo esc_html( get_sub_field( 'text' ) ); ?></p>
                            <?php if ( $page ) : ?><p class="c-feature-grid__more mb-0" aria-hidden="true">See how it works <i class="bi bi-arrow-right"></i></p><?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
