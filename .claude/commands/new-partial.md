# New Template Partial Generator

Generate a new reusable PHP template partial for the Framework theme.

## Project Context

- Theme slug: `framework`
- Partials live in `wp-content/themes/framework/template-parts/`
- Included in templates via `get_template_part( 'template-parts/{name}' )`
- Editor: Classic Editor
- CSS framework: Bootstrap 5 — use Bootstrap classes first, custom SCSS only when necessary
- Design: Clean and minimal — generous whitespace, clear hierarchy, subtle interactions only
- ACF fields are retrieved with `get_field()`
- Custom styles use BEM: `.c-{partial-name}` as the block

## Instructions

The user will describe the partial they need. From that description:

### Step 1 — Plan
State the partial filename, its purpose, any ACF fields it will consume, and the broad markup structure. Ask the user to confirm before generating code.

### Step 2 — Generate the PHP partial

- File goes in `template-parts/{name}.php`
- Open with a DocBlock comment describing the partial's purpose
- Retrieve all ACF fields at the top, assigned to clearly named variables
- Always guard fields before rendering: `if ( $variable ) :`
- Wrap the partial in `<section class="c-{name}">` or the most appropriate semantic element
- Use Bootstrap grid and utility classes for all layout and spacing
- Use semantic HTML throughout
- Keep markup minimal — no unnecessary wrapper divs

### Step 3 — Generate SCSS partial stub

- File goes in `assets/scss/_{name}.scss`
- First line after the comment header: `@use 'variables' as *;`
- Only include styles Bootstrap cannot handle
- Use BEM: `.c-{name}` as the block, `__element` and `--modifier` as needed
- Add a comment explaining why each custom rule is necessary

### Step 4 — Wire up

Show the user:
1. The `@use '{name}';` line to add to `assets/scss/style.scss`
2. The `get_template_part( 'template-parts/{name}' )` call to use in templates

## Output Format

Present each file clearly labelled with its full path. Follow with the two wiring snippets the user needs to add manually.

## User Request

$ARGUMENTS
