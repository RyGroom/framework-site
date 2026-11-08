<?php
$anchor  = get_sub_field( 'anchor' );
$eyebrow = get_sub_field( 'eyebrow' );
$heading = get_sub_field( 'heading' );
?>
<section class="c-section c-section--light c-steps"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
    <div class="container">
        <div class="c-section__head">
            <?php if ( $eyebrow ) : ?><p class="c-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
            <?php if ( $heading ) : ?><h2 class="c-section__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
        </div>
        <?php if ( have_rows( 'steps' ) ) : ?>
            <ol class="row g-4 g-lg-5 list-unstyled mb-0">
                <?php $n = 0; while ( have_rows( 'steps' ) ) : the_row(); $n++; ?>
                    <li class="col-md-4">
                        <span class="c-steps__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) ); ?></span>
                        <h3 class="c-steps__title"><?php echo esc_html( get_sub_field( 'title' ) ); ?></h3>
                        <p class="mb-0"><?php echo esc_html( get_sub_field( 'text' ) ); ?></p>
                    </li>
                <?php endwhile; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
