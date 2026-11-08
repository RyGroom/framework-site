<?php
get_header();
?>
<section class="c-content text-center">
    <div class="container">
        <h1 class="c-content__title">Page not found</h1>
        <p class="lead mb-4">That page doesn't exist, or it moved.</p>
        <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to the homepage</a>
    </div>
</section>
<?php
get_footer();
