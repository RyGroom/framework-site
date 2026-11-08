<?php
$heading = get_sub_field( 'heading' );
$id      = 'faq-' . get_row_index();
// A company that has subscribed before gets no free month: answers can have a version for them.
$returning = function_exists( 'framework_store_is_returning' ) && framework_store_is_returning();
?>
<section class="c-section c-section--cream c-faq">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ( $heading ) : ?><h2 class="c-section__heading text-center mb-5"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
                <?php if ( have_rows( 'items' ) ) : ?>
                    <div class="accordion accordion-flush c-faq__list" id="<?php echo esc_attr( $id ); ?>">
                        <?php $i = 0; while ( have_rows( 'items' ) ) : the_row(); $i++; ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr( "$id-$i" ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( "$id-$i" ); ?>">
                                        <?php echo esc_html( get_sub_field( 'question' ) ); ?>
                                    </button>
                                </h3>
                                <div id="<?php echo esc_attr( "$id-$i" ); ?>" class="accordion-collapse collapse" data-bs-parent="#<?php echo esc_attr( $id ); ?>">
                                    <div class="accordion-body"><?php echo esc_html( ( $returning && get_sub_field( 'answer_returning' ) ) ? get_sub_field( 'answer_returning' ) : get_sub_field( 'answer' ) ); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
