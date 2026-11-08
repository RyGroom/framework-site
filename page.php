<?php
/**
 * Pages: the page builder sections when there are any, otherwise the
 * editor content in a plain reading column (legal pages, simple pages).
 */
get_header();

while ( have_posts() ) :
    the_post();

    if ( function_exists( 'have_rows' ) && have_rows( 'sections' ) ) :
        get_template_part( 'template-parts/page-builder' );
    else :
        ?>
        <article class="c-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="c-content__inner">
                            <?php // Filterable so a page can set its own heading, or none (store pages). ?>
                            <?php $framework_title = apply_filters( 'framework_page_title', get_the_title() ); ?>
                            <?php if ( $framework_title !== '' ) : ?>
                                <h1 class="c-content__title"><?php echo esc_html( $framework_title ); ?></h1>
                            <?php endif; ?>
                            <div class="c-content__body"><?php the_content(); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <?php
    endif;
endwhile;

get_footer();
