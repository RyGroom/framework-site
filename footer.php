</main>

<?php
$blurb = function_exists( 'get_field' ) ? get_field( 'footer_blurb', 'option' ) : '';
$email = function_exists( 'get_field' ) ? get_field( 'contact_email', 'option' ) : '';
?>
<footer class="c-footer">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-5">
                <img class="c-footer__logo mb-3" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/framework-wordmark-accent.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="217" height="26">
                <?php if ( $blurb ) : ?>
                    <p class="c-footer__blurb mb-0"><?php echo esc_html( $blurb ); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-6 col-lg-3 offset-lg-1">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'list-unstyled c-footer__menu mb-0',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ] );
                ?>
            </div>
            <div class="col-6 col-lg-3">
                <ul class="list-unstyled c-footer__menu mb-0">
                    <li><a href="<?php echo esc_url( framework_app_url() ); ?>">Log in</a></li>
                    <li><a href="<?php echo esc_url( framework_signup_url() ); ?>">Get started</a></li>
                    <?php if ( $email ) : ?>
                        <?php
                        // Lets a narrow phone column break the address after
                        // the "@" instead of pushing the page sideways.
                        $email_parts = explode( '@', $email, 2 );
                        $email_label = count( $email_parts ) === 2
                            ? esc_html( antispambot( $email_parts[0] ) ) . '@<wbr>' . esc_html( antispambot( $email_parts[1] ) )
                            : esc_html( antispambot( $email ) );
                        ?>
                        <li><a class="c-footer__email" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo $email_label; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="c-footer__bottom d-flex flex-column flex-md-row justify-content-between gap-2 mt-5 pt-4">
            <p class="mb-0">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
            <?php
            wp_nav_menu( [
                'theme_location' => 'legal',
                'container'      => false,
                'menu_class'     => 'list-inline mb-0 c-footer__legal',
                'fallback_cb'    => false,
                'depth'          => 1,
            ] );
            ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
