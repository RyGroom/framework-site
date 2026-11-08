<?php

/**
 * ACF field groups, registered in PHP (the acf-json folder catches anything
 * later added or edited in the admin).
 *
 * - Page builder: a flexible content field on every page. Each layout maps
 *   to template-parts/blocks/{layout}.php.
 * - Site settings: the options page from functions.php.
 */

add_action( 'acf/init', function (): void {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    // Reused field shapes. $k builds a field key unique to its layout.
    $text = fn ( string $k, string $name, string $label, array $extra = [] ) => [ 'key' => "field_framework_{$k}_{$name}", 'name' => $name, 'label' => $label, 'type' => 'text' ] + $extra;
    $area = fn ( string $k, string $name, string $label, array $extra = [] ) => [ 'key' => "field_framework_{$k}_{$name}", 'name' => $name, 'label' => $label, 'type' => 'textarea', 'rows' => 3, 'new_lines' => '' ] + $extra;
    $link = fn ( string $k, string $name, string $label ) => [ 'key' => "field_framework_{$k}_{$name}", 'name' => $name, 'label' => $label, 'type' => 'link', 'return_format' => 'array' ];
    $image = fn ( string $k, string $name, string $label ) => [ 'key' => "field_framework_{$k}_{$name}", 'name' => $name, 'label' => $label, 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium', 'library' => 'all' ];
    $background = fn ( string $k ) => [
        'key' => "field_framework_{$k}_background", 'name' => 'background', 'label' => 'Background', 'type' => 'button_group',
        'choices' => [ 'light' => 'Light', 'cream' => 'Cream' ], 'default_value' => 'light',
    ];

    acf_add_local_field_group( [
        'key'      => 'group_framework_page_builder',
        'title'    => 'Page builder',
        'position' => 'acf_after_title',
        'style'    => 'seamless',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ] ] ],
        'fields'   => [ [
            'key'          => 'field_framework_sections',
            'name'         => 'sections',
            'label'        => 'Sections',
            'type'         => 'flexible_content',
            'button_label' => 'Add section',
            'layouts'      => [
                'layout_framework_hero' => [
                    'key' => 'layout_framework_hero', 'name' => 'hero', 'label' => 'Hero', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'hero', 'eyebrow', 'Eyebrow', [ 'instructions' => 'Small label above the heading.' ] ),
                        $text( 'hero', 'heading', 'Heading', [ 'instructions' => 'Wrap a word in *asterisks* to highlight it in orange.' ] ),
                        $area( 'hero', 'text', 'Text' ),
                        $link( 'hero', 'primary_button', 'Primary button' ),
                        $link( 'hero', 'secondary_button', 'Secondary button' ),
                        [
                            'key' => 'field_framework_hero_points', 'name' => 'points', 'label' => 'Points under the buttons',
                            'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Add point', 'max' => 4,
                            'sub_fields' => [
                                $text( 'hero_point', 'icon', 'Icon', [ 'instructions' => 'Bootstrap Icons name.' ] ),
                                $text( 'hero_point', 'text', 'Text' ),
                            ],
                        ],
                        $image( 'hero', 'image', 'Image' ),
                    ],
                ],
                'layout_framework_pricing' => [
                    'key' => 'layout_framework_pricing', 'name' => 'pricing', 'label' => 'Pricing', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'pricing', 'eyebrow', 'Eyebrow' ),
                        $text( 'pricing', 'heading', 'Heading', [ 'instructions' => 'The page title.' ] ),
                        $area( 'pricing', 'intro', 'Intro' ),
                        [
                            'key' => 'field_framework_pricing_plans', 'name' => 'plans', 'label' => 'Plans',
                            'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add plan',
                            'sub_fields' => [
                                $text( 'pricing_plan', 'name', 'Plan name' ),
                                $text( 'pricing_plan', 'price', 'Price', [ 'instructions' => 'e.g. $59.99. Must match the store.' ] ),
                                $text( 'pricing_plan', 'period', 'Period', [ 'instructions' => 'e.g. per month' ] ),
                                $text( 'pricing_plan', 'period_returning', 'Period for returning companies', [ 'instructions' => 'Shown to a company that has subscribed before, which gets no free month. Leave empty for "per month".' ] ),
                                $area( 'pricing_plan', 'summary', 'Who it is for' ),
                                $text( 'pricing_plan', 'sku', 'Store SKU', [ 'instructions' => 'Must match the WooCommerce product exactly. Sends people from the app to checkout for this plan.' ] ),
                                [
                                    'key' => 'field_framework_pricing_plan_items', 'name' => 'items', 'label' => 'Included',
                                    'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Add line',
                                    'sub_fields' => [
                                        $text( 'pricing_plan_item', 'item', 'Line' ),
                                        $text( 'pricing_plan_item', 'url', 'Link (optional)', [ 'instructions' => 'e.g. #features for a section on this page.' ] ),
                                    ],
                                ],
                            ],
                        ],
                        $text( 'pricing', 'button_label', 'Button label', [ 'instructions' => 'New visitors go to the app sign-up page. People who came from the app go to checkout.' ] ),
                        $text( 'pricing', 'heading_returning', 'Heading for returning companies', [ 'instructions' => 'A company that has subscribed before gets no free month, so it sees these instead. Leave empty for the built-in wording.' ] ),
                        $area( 'pricing', 'intro_returning', 'Intro for returning companies' ),
                        $text( 'pricing', 'button_label_returning', 'Button label for returning companies' ),
                        $area( 'pricing', 'note', 'Small print under the plans' ),
                    ],
                ],
                'layout_framework_feature_grid' => [
                    'key' => 'layout_framework_feature_grid', 'name' => 'feature_grid', 'label' => 'Feature grid', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'feature_grid', 'anchor', 'Anchor', [ 'instructions' => 'Optional id for in-page links, e.g. "features".' ] ),
                        $background( 'feature_grid' ),
                        $text( 'feature_grid', 'eyebrow', 'Eyebrow' ),
                        $text( 'feature_grid', 'heading', 'Heading' ),
                        $area( 'feature_grid', 'intro', 'Intro' ),
                        [
                            'key' => 'field_framework_feature_grid_features', 'name' => 'features', 'label' => 'Features',
                            'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add feature',
                            'sub_fields' => [
                                $text( 'feature_grid_feature', 'icon', 'Icon', [ 'instructions' => 'A Bootstrap Icons name, e.g. "calendar-check".' ] ),
                                $text( 'feature_grid_feature', 'title', 'Title' ),
                                $area( 'feature_grid_feature', 'text', 'Text' ),
                                [ 'key' => 'field_framework_feature_grid_feature_page', 'name' => 'page', 'label' => 'Links to', 'type' => 'page_link', 'post_type' => [ 'page' ], 'allow_null' => 1, 'instructions' => 'Optional page with the full story.' ],
                            ],
                        ],
                    ],
                ],
                'layout_framework_feature_list' => [
                    'key' => 'layout_framework_feature_list', 'name' => 'feature_list', 'label' => 'Feature checklist', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'feature_list', 'anchor', 'Anchor', [ 'instructions' => 'Optional id for in-page links.' ] ),
                        $background( 'feature_list' ),
                        $text( 'feature_list', 'eyebrow', 'Eyebrow' ),
                        $text( 'feature_list', 'heading', 'Heading' ),
                        $area( 'feature_list', 'intro', 'Intro' ),
                        [
                            'key' => 'field_framework_feature_list_groups', 'name' => 'groups', 'label' => 'Groups',
                            'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add group',
                            'sub_fields' => [
                                $text( 'feature_list_group', 'icon', 'Icon', [ 'instructions' => 'A Bootstrap Icons name, e.g. "calendar-check".' ] ),
                                $text( 'feature_list_group', 'title', 'Title' ),
                                [ 'key' => 'field_framework_feature_list_group_page', 'name' => 'page', 'label' => 'Links to', 'type' => 'page_link', 'post_type' => [ 'page' ], 'allow_null' => 1 ],
                                [
                                    'key' => 'field_framework_feature_list_group_items', 'name' => 'items', 'label' => 'Features',
                                    'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Add feature',
                                    'sub_fields' => [
                                        $text( 'feature_list_item', 'item', 'Feature' ),
                                        [ 'key' => 'field_framework_feature_list_item_optional', 'name' => 'optional', 'label' => 'Optional', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Adds an "Optional" tag: a setting each business chooses to use.' ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'layout_framework_split' => [
                    'key' => 'layout_framework_split', 'name' => 'split', 'label' => 'Text + image', 'display' => 'block',
                    'sub_fields' => [
                        $background( 'split' ),
                        [ 'key' => 'field_framework_split_image_side', 'name' => 'image_side', 'label' => 'Image side', 'type' => 'button_group', 'choices' => [ 'right' => 'Right', 'left' => 'Left' ], 'default_value' => 'right' ],
                        $text( 'split', 'eyebrow', 'Eyebrow' ),
                        $text( 'split', 'heading', 'Heading' ),
                        $area( 'split', 'text', 'Text' ),
                        [
                            'key' => 'field_framework_split_bullets', 'name' => 'bullets', 'label' => 'Bullet points',
                            'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Add point',
                            'sub_fields' => [ $text( 'split_bullet', 'item', 'Point' ) ],
                        ],
                        $link( 'split', 'button', 'Button' ),
                        $image( 'split', 'image', 'Image' ),
                    ],
                ],
                'layout_framework_steps' => [
                    'key' => 'layout_framework_steps', 'name' => 'steps', 'label' => 'Steps', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'steps', 'anchor', 'Anchor', [ 'instructions' => 'Optional id for in-page links, e.g. "how-it-works".' ] ),
                        $text( 'steps', 'eyebrow', 'Eyebrow' ),
                        $text( 'steps', 'heading', 'Heading' ),
                        [
                            'key' => 'field_framework_steps_steps', 'name' => 'steps', 'label' => 'Steps',
                            'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add step',
                            'sub_fields' => [ $text( 'steps_step', 'title', 'Title' ), $area( 'steps_step', 'text', 'Text' ) ],
                        ],
                    ],
                ],
                'layout_framework_faq' => [
                    'key' => 'layout_framework_faq', 'name' => 'faq', 'label' => 'FAQ', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'faq', 'heading', 'Heading' ),
                        [
                            'key' => 'field_framework_faq_items', 'name' => 'items', 'label' => 'Questions',
                            'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add question',
                            'sub_fields' => [
                                $text( 'faq_item', 'question', 'Question' ),
                                $area( 'faq_item', 'answer', 'Answer' ),
                                $area( 'faq_item', 'answer_returning', 'Answer for returning companies', [ 'instructions' => 'Optional. Shown instead to a company that has subscribed before, which gets no free month.' ] ),
                            ],
                        ],
                    ],
                ],
                'layout_framework_cta' => [
                    'key' => 'layout_framework_cta', 'name' => 'cta', 'label' => 'Call to action', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'cta', 'heading', 'Heading' ),
                        $area( 'cta', 'text', 'Text' ),
                        $link( 'cta', 'button', 'Button' ),
                        $link( 'cta', 'secondary_button', 'Secondary button' ),
                    ],
                ],
                'layout_framework_text' => [
                    'key' => 'layout_framework_text', 'name' => 'text', 'label' => 'Text', 'display' => 'block',
                    'sub_fields' => [
                        $text( 'text', 'heading', 'Heading' ),
                        [ 'key' => 'field_framework_text_body', 'name' => 'body', 'label' => 'Body', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1 ],
                    ],
                ],
            ],
        ] ],
    ] );

    acf_add_local_field_group( [
        'key'      => 'group_framework_contact',
        'title'    => 'Contact page',
        'position' => 'acf_after_title',
        'location' => [ [ [ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-contact.php' ] ] ],
        'fields'   => [
            $text( 'contact', 'contact_heading', 'Heading', [ 'instructions' => 'Big heading on the left. Falls back to the page title.' ] ),
            [
                'key' => 'field_framework_contact_points', 'name' => 'contact_points', 'label' => 'What to expect',
                'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add point', 'max' => 4,
                'sub_fields' => [
                    $text( 'contact_point', 'icon', 'Icon', [ 'instructions' => 'Bootstrap Icons name.' ] ),
                    $text( 'contact_point', 'title', 'Title' ),
                    $text( 'contact_point', 'text', 'Text' ),
                ],
            ],
        ],
    ] );

    acf_add_local_field_group( [
        'key'      => 'group_framework_site_settings',
        'title'    => 'Site settings',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'framework-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_framework_settings_app_url', 'name' => 'app_url', 'label' => 'App URL', 'type' => 'url',
              'instructions' => 'Where the Framework app lives. "Log in" goes here and "Get started" goes to its /register page.',
              'default_value' => 'http://localhost:8000' ],
            [ 'key' => 'field_framework_settings_contact_email', 'name' => 'contact_email', 'label' => 'Contact email', 'type' => 'email' ],
            [ 'key' => 'field_framework_settings_footer_blurb', 'name' => 'footer_blurb', 'label' => 'Footer blurb', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '' ],
        ],
    ] );
} );
