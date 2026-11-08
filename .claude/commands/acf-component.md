# ACF Component Generator

Generate a complete ACF component for the Framework WordPress theme. This includes:
1. ACF field group registration (in `functions.php` or a dedicated `inc/acf-fields.php`)
2. A PHP template partial in `wp-content/themes/framework/template-parts/`
3. A SCSS partial stub in `wp-content/themes/framework/assets/scss/`
4. The `@use` line added to `style.scss`

## Project Context

- Theme slug: `framework`
- Editor: Classic Editor (no Gutenberg block registration)
- CSS framework: Bootstrap 5 (use Bootstrap classes first, custom SCSS only when necessary)
- Design: Clean and minimal — generous whitespace, clear hierarchy, subtle interactions only
- SCSS partials must include `@use 'variables' as *;` at the top
- PHP templates use `get_template_part()` to include partials
- Field groups are registered via `acf_add_local_field_group()` in PHP
- ACF local JSON saves to `wp-content/themes/framework/acf-json/`

## Instructions

The user will describe a component. From that description:

### Step 1 — Plan
List the component name, the fields you'll create (name, type, any options), and where the field group will be assigned (page, post, options page, etc.). Ask the user to confirm or adjust before generating any code.

### Step 2 — Generate ACF field group registration
- Use `acf_add_local_field_group()` with a well-structured array
- Field keys must follow the pattern `field_framework_{component}_{field}` (snake_case)
- Group key must follow the pattern `group_framework_{component}`
- Use clear, human-readable labels
- Set sensible defaults for `return_format`, `preview_size`, `library`, etc.
- Assign to the correct location (page template, post type, etc.) based on the user's description

### Step 3 — Generate PHP template partial
- File goes in `template-parts/{component}.php`
- Retrieve fields with `get_field()` at the top, assigned to variables
- Always check fields exist before rendering (`if ( $variable ) :`)
- Use Bootstrap 5 grid and utility classes for layout — no inline styles
- Follow clean/minimal design: clear heading hierarchy, generous spacing via Bootstrap utilities
- Wrap the component in a `<section>` with a BEM class: `class="c-{component}"`
- Use semantic HTML elements throughout

### Step 4 — Generate SCSS partial
- File goes in `assets/scss/_{component}.scss`
- First line (after comment header): `@use 'variables' as *;`
- Only include styles that Bootstrap cannot handle
- Comment any non-obvious rules
- Follow BEM: `.c-{component}` as the block, `__element` and `--modifier` as needed

### Step 5 — Wire up
- Show the `@use '{component}';` line to add to `style.scss`
- Show where to add `get_template_part( 'template-parts/{component}' )` in the relevant template
- If ACF fields are in a separate `inc/acf-fields.php`, show the `require_once` for `functions.php`

## Output Format

Present each file clearly labelled with its full path, ready to copy or create. After all files, show a short summary checklist of what was generated and any manual steps needed (e.g. assigning the field group in ACF UI, flushing rewrite rules).

## User Request

$ARGUMENTS
