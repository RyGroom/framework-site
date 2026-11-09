<?php
$anchor     = get_sub_field( 'anchor' );
$background = get_sub_field( 'background' ) ?: 'light';
$eyebrow    = get_sub_field( 'eyebrow' );
$heading    = get_sub_field( 'heading' );
$intro      = get_sub_field( 'intro' );
?>
<section class="c-section c-section--<?php echo esc_attr( $background ); ?> c-feature-list"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
    <div class="container">
        <?php if ( $eyebrow || $heading || $intro ) : ?>
            <div class="c-section__head">
                <?php if ( $eyebrow ) : ?><p class="c-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
                <?php if ( $heading ) : ?><h2 class="c-section__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
                <?php if ( $intro ) : ?><p class="lead mb-0"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ( have_rows( 'groups' ) ) : ?>
            <div class="row g-4">
                <?php while ( have_rows( 'groups' ) ) : the_row(); ?>
                    <?php $page = get_sub_field( 'page' ); ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="c-feature-list__group">
                            <div class="c-feature-list__head">
                                <?php if ( $icon = get_sub_field( 'icon' ) ) : ?>
                                    <span class="c-feature-list__icon" aria-hidden="true"><i class="bi bi-<?php echo esc_attr( $icon ); ?>"></i></span>
                                <?php endif; ?>
                                <h3 class="c-feature-list__title">
                                    <?php if ( $page ) : ?><a href="<?php echo esc_url( $page ); ?>"><?php endif; ?>
                                    <?php echo esc_html( get_sub_field( 'title' ) ); ?>
                                    <?php if ( $page ) : ?></a><?php endif; ?>
                                </h3>
                            </div>
                            <?php if ( have_rows( 'items' ) ) : ?>
                                <ul class="c-feature-list__items list-unstyled mb-0">
                                    <?php while ( have_rows( 'items' ) ) : the_row(); ?>
                                        <li>
                                            <i class="bi bi-check-lg c-tick" aria-hidden="true"></i>
                                            <span>
                                                <?php echo esc_html( get_sub_field( 'item' ) ); ?>
                                                <?php if ( get_sub_field( 'optional' ) ) : ?><span class="c-feature-list__tag">Optional</span><?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if ( $page ) : ?>
                                <a class="c-feature-list__more" href="<?php echo esc_url( $page ); ?>">See how it works <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
