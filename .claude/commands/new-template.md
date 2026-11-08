# New Page Template Generator

Generate a new WordPress page template file for the Framework theme.

## Project Context

- Theme slug: `framework`
- Theme directory: `wp-content/themes/framework/`
- Editor: Classic Editor
- CSS framework: Bootstrap 5 — use Bootstrap classes first, custom SCSS only when necessary
- Design: Clean and minimal — generous whitespace, clear hierarchy, subtle interactions only
- Template parts live in `template-parts/` and are included via `get_template_part()`
- ACF fields are retrieved with `get_field()`

## Instructions

The user will describe the page template they need. From that description:

### Step 1 — Plan
State the template filename, the WordPress template header name, and a brief outline of the sections/structure you'll generate. Ask the user to confirm before generating code.

### Step 2 — Generate the template file

Follow this structure:

```php
<?php
/**
 * Template Name: {Human Readable Name}
 *
 * @package framework
 */

get_header();
?>

<main id="primary" class="site-main">
    <!-- sections here -->
</main>

<?php
get_footer();
```

- Wrap each distinct section in `<section class="c-{section-name} py-5">`
- Use Bootstrap `container`, `row`, `col-*` for all layout
- Use Bootstrap spacing utilities (`py-*`, `px-*`, `mb-*`, etc.) — no inline styles
- Use semantic HTML throughout (`<section>`, `<article>`, `<header>`, `<nav>`, `<aside>`, `<footer>`)
- Include placeholder comments where ACF fields or dynamic content would be pulled in
- Keep markup clean and minimal — no unnecessary wrapper divs

### Step 3 — Suggest template parts
If any section of the template would be reused across other templates, flag it and suggest running `/new-partial` to extract it.

### Step 4 — Wire up
- Confirm the file goes in `wp-content/themes/framework/` (root level for page templates)
- Remind the user to assign the template to a page in the WordPress admin under Page Attributes → Template

## Output Format

Present the full file with its path clearly labelled, ready to create. Follow with any suggestions for extracting reusable parts.

## User Request

$ARGUMENTS
