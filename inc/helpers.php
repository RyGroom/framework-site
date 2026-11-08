<?php

/**
 * Small helpers shared by the templates.
 */

/**
 * A URL in the Framework app — Site settings → App URL, plus a path.
 * framework_app_url( '/register' ) for sign-up; framework_app_url() is the
 * app's front door, which is its login form.
 */
function framework_app_url( string $path = '' ): string {
    $base = function_exists( 'get_field' ) ? (string) get_field( 'app_url', 'option' ) : '';
    $base = $base !== '' ? $base : 'http://localhost:8000';

    return rtrim( $base, '/' ) . ( $path !== '' ? '/' . ltrim( $path, '/' ) : '' );
}

/** The "Get started" button target — the app's sign-up page. */
function framework_signup_url(): string {
    return framework_app_url( '/register' );
}

/**
 * A button from an ACF link field (array return format). Prints nothing when
 * the link is empty, so templates can call it unconditionally.
 */
function framework_button( $link, string $classes = 'btn btn-primary' ): void {
    if ( empty( $link['url'] ) || empty( $link['title'] ) ) {
        return;
    }
    $target = ! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : '';
    printf(
        '<a class="%s" href="%s"%s>%s</a>',
        esc_attr( $classes ),
        esc_url( $link['url'] ),
        $target,
        esc_html( $link['title'] )
    );
}
