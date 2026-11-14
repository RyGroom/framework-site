<?php
/**
 * One-off content change (Nov 14 2026): adds "Problem with a feature" to
 * the Contact form's "What can we help with?" dropdown, after "Help with
 * my account". "Something else" stays last.
 *
 * The form lives in the database (Contact Form 7, titled "Contact"), so
 * this edits it through Contact Form 7's own API. Expects the dropdown as
 * tools/contact-topics-2026-11.php left it.
 *
 * Run with WP-CLI from the WordPress root:
 *   wp eval-file wp-content/themes/framework/tools/contact-topic-feature-problem-2026-11.php           (dry run)
 *   wp eval-file wp-content/themes/framework/tools/contact-topic-feature-problem-2026-11.php apply     (make the change)
 *
 * Only swaps the exact dropdown it expects; if the form's been edited
 * since, it says so and changes nothing. Safe to run twice.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}

if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
    WP_CLI::error( 'Contact Form 7 is not active.' );
}

$apply = in_array( 'apply', $args ?? [], true );

$old = '[select* your-topic "A walkthrough for my team" "Pricing" "Moving from another tool" "Help with my account" "Feature request" "Something else"]';
$new = '[select* your-topic "A walkthrough for my team" "Pricing" "Moving from another tool" "Help with my account" "Problem with a feature" "Feature request" "Something else"]';

$posts = get_posts( [ 'post_type' => 'wpcf7_contact_form', 'title' => 'Contact', 'post_status' => 'publish', 'numberposts' => 1 ] );
if ( ! $posts ) {
    WP_CLI::error( 'No Contact Form 7 form titled "Contact".' );
}

$form  = WPCF7_ContactForm::get_instance( $posts[0]->ID );
$props = $form->get_properties();

if ( str_contains( $props['form'], $new ) ) {
    WP_CLI::success( 'Already done: the dropdown has "Problem with a feature".' );
    return;
}
if ( ! str_contains( $props['form'], $old ) ) {
    WP_CLI::error( 'The dropdown isn\'t the one expected (someone may have edited it, or tools/contact-topics-2026-11.php hasn\'t been run). Nothing changed.' );
}

WP_CLI::log( "Form {$posts[0]->ID} \"Contact\": add \"Problem with a feature\" after \"Help with my account\"." );
if ( ! $apply ) {
    WP_CLI::log( 'Dry run. Run again with "apply" to make the change.' );
    return;
}

$props['form'] = str_replace( $old, $new, $props['form'] );
$form->set_properties( $props );
$form->save();
WP_CLI::success( 'Dropdown updated.' );
