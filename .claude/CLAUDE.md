# Framework Theme — Project Rules

## Bootstrap 5

Bootstrap 5 CSS is enqueued globally. When building or editing PHP template files:

- **Always use Bootstrap utility and component classes** in markup before reaching for custom SCSS
- **Layout** — use Bootstrap grid (`container`, `row`, `col-*`) and flex utilities (`d-flex`, `justify-content-*`, `align-items-*`, `gap-*`) instead of custom layout CSS
- **Page-edge alignment** — always use a `container` to align content to the page grid; never use `px-*` classes to fake horizontal page padding. Raw `px-*` is only appropriate for internal component padding (e.g. inside a card or list item), never for aligning content to the page edge
- **Spacing** — use Bootstrap spacing utilities (`mt-*`, `mb-*`, `px-*`, `py-*`, etc.) for internal component spacing; use `container` for horizontal page alignment
- **Typography** — use Bootstrap type utilities (`fs-*`, `fw-*`, `text-*`, `lead`, etc.) instead of custom font rules
- **Only write custom SCSS** when Bootstrap genuinely cannot cover the requirement — document why with a comment in the partial

## Design Standards

The site aesthetic is **clean and minimal**. Every design decision should reduce visual noise, not add to it.

### General Principles
- Favour whitespace — generous padding and margins make layouts feel considered, not empty
- Let content breathe; avoid cramming elements together
- One strong focal point per section — don't compete for attention
- Flat, simple UI over decorative or skeuomorphic styles
- When in doubt, remove rather than add

### Typography
- Establish a clear hierarchy: one dominant heading size, a comfortable body size, a smaller meta/label size
- Line lengths should stay readable — roughly 60–75 characters for body text
- No more than 2 typefaces per page; define them as SCSS variables once a font is chosen
- Avoid all-caps for long strings of text; use it sparingly for labels or eyebrows only
- Use `min()` for large display/hero font sizes so they scale on mobile — e.g. `font-size: min(10vw, 100px)` — never use a fixed `px` value alone for hero or display headings

### Color
- No hardcoded color values anywhere — always define in `_variables.scss` first
- High contrast between text and background; never sacrifice readability for aesthetics
- Use a limited palette — a neutral base, one primary accent, and functional colors (error, success) is enough
- Avoid pure black (`#000`) for body text; a dark grey reads more naturally on screen

### Spacing
- Use the spacing scale defined in `_variables.scss` consistently — never arbitrary `px` values
- Sections should have generous vertical padding so the page doesn't feel dense
- Align elements to a consistent grid; asymmetry should be intentional, not accidental

### Animation & Interaction
- Animations must be subtle — they should enhance, never distract
- Prefer short durations (150–300ms) with `ease-out` or `ease-in-out` easing
- Appropriate uses: fade-ins on scroll, smooth hover state transitions, gentle transforms (translate, opacity)
- Never animate multiple competing elements simultaneously
- Always include `prefers-reduced-motion` media query for any non-trivial animation
- No auto-playing carousels, looping videos, or animations that the user cannot stop

### Imagery & Media
- Images should be purposeful — decorative images must genuinely improve the layout
- Use aspect-ratio utilities to keep images consistent; never let images distort
- Prefer full-bleed or contained images with clear intent — avoid awkward half-width crops

### Accessibility
- Semantic HTML first — use the correct element before adding ARIA roles
- All interactive elements must have visible focus states
- Colour contrast must meet WCAG AA minimum (4.5:1 for body text, 3:1 for large text)
- Never remove `outline` without replacing it with an equally visible focus indicator

## Theme Structure

- Custom styles live in `wp-content/themes/framework/assets/scss/`
- Compiled output: `assets/css/main.css` (dev) and `assets/css/main.min.css` (prod)
- Run `npm run watch` in the theme directory during development
- Run `npm run build` to produce minified production assets

## This Site

The marketing site for **Framework**, the field service app (the Laravel app in `/Applications/MAMP/htdocs/framework`).

- **Brand comes from the app** — keep `_variables.scss` in step with the app's `public/css/industry.css`: navy text `#122e50`, accent navy `#294867`, orange call to action `#ffa405` with navy text on it, cream `#f6f1e9`, card white `#fffdf9`. Headings in Barlow Condensed, body in Barlow. Square corners.
- **Logos** are copied from the app into `assets/img/` (wordmark, reversed wordmark, horizontal logo).
- **Pages are built from page builder sections** (ACF flexible content `sections`, registered in `inc/acf-fields.php`); each layout renders `template-parts/blocks/{layout}.php` with a matching `_block-*.scss`. A page without sections shows its editor content (legal pages).
- **The app's URL lives in Site settings → App URL.** Link to the app only through `framework_app_url()` / `framework_signup_url()` — never hard-code it. "Log in" is the app's front door; "Get started" is its `/register` page.
- **Product claims must match what the app actually does.** Don't invent features, pricing, integrations or customer numbers.
