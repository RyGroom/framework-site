<?php
/**
 * Client text messages are hidden on the public site until Twilio is live.
 *
 * To bring them back, set FRAMEWORK_TEXTING_LIVE to true. Nothing else needs
 * to change: the original copy is still on each page in the WordPress editor,
 * and this only stops it being shown to visitors.
 *
 * If you reword one of these lines in the editor, update its match below too,
 * or it will start showing again.
 */
const FRAMEWORK_TEXTING_LIVE = false;

/**
 * Rows left out of the page, matched on part of their copy.
 *
 * - Home: "What's inside" bullet
 * - Clients & the office: "Text messages" feature and "Do you text my clients?" FAQ
 * - All features: checklist item
 */
const FRAMEWORK_TEXTING_HIDDEN_ROWS = [
    'texts to clients who agree to them',
    'Optional reminders and on-my-way texts',
    'Only if you turn texting on',
    'Text messages to clients who agree',
];

/**
 * Sentences reworded rather than removed.
 *
 * - Scheduling & dispatch: "Customer reminders" feature
 */
const FRAMEWORK_TEXTING_REWORDED = [
    ', sent as a text instead for customers who agree to texts.' => '.',
];

/**
 * Takes the hidden rows out of any repeater as the page loads. Skipped in the
 * admin, so saving a page there can't delete them for good.
 */
function framework_hide_texting_rows( $rows ) {
    if ( ! is_array( $rows ) ) {
        return $rows;
    }

    return array_values(
        array_filter(
            $rows,
            static function ( $row ): bool {
                if ( ! is_array( $row ) ) {
                    return true;
                }

                foreach ( $row as $value ) {
                    if ( ! is_string( $value ) ) {
                        continue;
                    }

                    foreach ( FRAMEWORK_TEXTING_HIDDEN_ROWS as $match ) {
                        if ( str_contains( $value, $match ) ) {
                            return false;
                        }
                    }
                }

                return true;
            }
        )
    );
}

function framework_reword_texting_copy( $value ) {
    return is_string( $value ) ? strtr( $value, FRAMEWORK_TEXTING_REWORDED ) : $value;
}

if ( ! FRAMEWORK_TEXTING_LIVE && ! is_admin() ) {
    add_filter( 'acf/load_value/type=repeater', 'framework_hide_texting_rows', 20 );
    add_filter( 'acf/format_value', 'framework_reword_texting_copy', 20 );
}
