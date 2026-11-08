<?php
/**
 * Fallback template (posts, archives, search) — a simple list.
 */
get_header();
?>
<section class="c-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ( have_posts() ) : ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <article class="mb-5">
                            <h2 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div class="c-content__body"><?php the_excerpt(); ?></div>
                        </article>
                    <?php endwhile; ?>
                    <?php the_posts_pagination(); ?>
                <?php else : ?>
                    <p>Nothing here yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
get_footer();
