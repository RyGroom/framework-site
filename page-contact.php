<?php
/**
 * Template Name: Contact
 *
 * Two columns: heading, intro (the editor content) and a short "what to
 * expect" list on the left; the form card on the right. The form is the
 * first [contact-form-7] shortcode in the content, pulled out of the intro
 * and rendered in the card.
 */
get_header();

while ( have_posts() ) :
    the_post();

    $content = get_the_content();
    $form    = '';
    if ( preg_match( '/\[contact-form-7[^\]]*\]/', $content, $m ) ) {
        $form    = $m[0];
        $content = str_replace( $form, '', $content );
    }

    $heading = function_exists( 'get_field' ) ? get_field( 'contact_heading' ) : '';
    ?>
    <section class="c-contact">
        <div class="container">
            <div class="row gy-5 gx-lg-5">
                <div class="col-lg-5">
                    <p class="c-eyebrow"><?php the_title(); ?></p>
                    <h1 class="c-contact__heading"><?php echo esc_html( $heading ?: get_the_title() ); ?></h1>
                    <div class="c-contact__intro"><?php echo apply_filters( 'the_content', $content ); ?></div>

                    <?php if ( function_exists( 'have_rows' ) && have_rows( 'contact_points' ) ) : ?>
                        <ul class="c-contact__points list-unstyled mb-0">
                            <?php while ( have_rows( 'contact_points' ) ) : the_row(); ?>
                                <li>
                                    <span class="c-contact__icon" aria-hidden="true"><i class="bi bi-<?php echo esc_attr( get_sub_field( 'icon' ) ?: 'check2' ); ?>"></i></span>
                                    <span>
                                        <strong><?php echo esc_html( get_sub_field( 'title' ) ); ?></strong>
                                        <?php if ( $text = get_sub_field( 'text' ) ) : ?><span class="d-block"><?php echo esc_html( $text ); ?></span><?php endif; ?>
                                    </span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <?php if ( $form ) : ?>
                    <div class="col-lg-7">
                        <div class="c-contact__card"><?php echo do_shortcode( $form ); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
endwhile;

get_footer();
