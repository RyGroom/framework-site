<?php
/**
 * Renders the page's "sections" flexible content field — each layout's
 * template is template-parts/blocks/{layout name}.php.
 */
while ( have_rows( 'sections' ) ) :
    the_row();
    get_template_part( 'template-parts/blocks/' . str_replace( '_', '-', get_row_layout() ) );
endwhile;
