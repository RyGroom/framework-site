<?php
/**
 * Template Name: Ads landing page
 *
 * The Google Ads landing page (brief: ~/Downloads/handoff_landing). A whole
 * document of its own: no site header, nav or footer and none of the site's
 * CSS/JS (Bootstrap etc.), so one way out and a fast first paint.
 *
 * - ?for=landscaping|lawn|hardscape|contractors picks the eyebrow, H1, title
 *   and description here on the server, so nothing flashes. Anything else is
 *   the general version.
 * - Every button goes to sign-up with ?plan= and the ad's UTM / gclid /
 *   gbraid / wbraid kept. The app remembers the plan and /pricing/ marks it
 *   as their pick. A new customer can't go straight to the store's
 *   checkout: it sends anyone without the app's signed company reference back
 *   to sign-up (inc/woocommerce.php), so sign-up is the real first step.
 * - Prices are typed here and must match the store and /pricing/.
 * - Tracking: begin_checkout and seat_calculator_used go to gtag() when the
 *   Google tag is on (FRAMEWORK_GTAG_ID, see inc/google-tag.php), otherwise
 *   to window.dataLayer.
 */

defined( 'ABSPATH' ) || exit;

const FRAMEWORK_LP_PRICE_INDIVIDUAL = 69;
const FRAMEWORK_LP_PRICE_CREW       = 149;
const FRAMEWORK_LP_CREW_SEATS       = 6;
const FRAMEWORK_LP_EXTRA_SEAT       = 15;
const FRAMEWORK_LP_DEFAULT_SEATS    = 4;

$lp_variants = [
    'general'     => [ 'Software for crew businesses', 'Run your crews from one app', 'Crew Business Software' ],
    'landscaping' => [ 'Landscaping business software', 'Run your landscaping crews from one app', 'Landscaping Business Software' ],
    'lawn'        => [ 'Lawn care business app', 'Schedule, track and bill every lawn from one app', 'Lawn Care Business App' ],
    'hardscape'   => [ 'Hardscape and contractor software', 'Schedule crews and bill jobs from one app', 'Hardscape and Contractor Software' ],
    'contractors' => [ 'Crew scheduling software for contractors', 'Schedule your crews and get paid faster', 'Crew Scheduling Software for Contractors' ],
];
$lp_for = isset( $_GET['for'] ) && is_string( $_GET['for'] ) ? sanitize_key( wp_unslash( $_GET['for'] ) ) : 'general';
if ( ! isset( $lp_variants[ $lp_for ] ) ) {
    $lp_for = 'general';
}
[ $lp_eyebrow, $lp_headline, $lp_title ] = $lp_variants[ $lp_for ];
$lp_description = 'Schedule crews, track hours with GPS and send invoices. First month free.';

// Ad parameters carried to sign-up. Values are length-capped and URL-encoded.
$lp_pass = [];
foreach ( [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id', 'gclid', 'gbraid', 'wbraid' ] as $lp_key ) {
    if ( isset( $_GET[ $lp_key ] ) && is_string( $_GET[ $lp_key ] ) && $_GET[ $lp_key ] !== '' ) {
        $lp_pass[ $lp_key ] = rawurlencode( substr( sanitize_text_field( wp_unslash( $_GET[ $lp_key ] ) ), 0, 500 ) );
    }
}
$lp_cta = static function ( string $plan ) use ( $lp_pass ): string {
    return add_query_arg( array_merge( [ 'plan' => $plan ], $lp_pass ), framework_signup_url() );
};
$lp_default_plan = FRAMEWORK_LP_DEFAULT_SEATS > 1 ? 'crew' : 'individual';

$lp_noindex = defined( 'FRAMEWORK_LANDING_NOINDEX' ) ? (bool) FRAMEWORK_LANDING_NOINDEX : true;
$lp_theme   = get_template_directory_uri();
$lp_img     = $lp_theme . '/assets/img/landing/';
$lp_font    = $lp_theme . '/assets/fonts/';
$lp_url     = get_permalink();

/** A <picture> with AVIF and WebP at 1x/2x widths. $wide: 720/1440 desktop shot, else 390/780 phone shot. */
$lp_picture = static function ( string $name, string $alt, bool $wide, string $sizes, bool $hero = false ) use ( $lp_img ): string {
    [ $w1, $w2, $w, $h ] = $wide ? [ 720, 1440, 1440, 900 ] : [ 390, 780, 390, 844 ];
    $set = static fn ( string $ext ) => esc_attr( "{$lp_img}{$name}.{$ext} {$w1}w, {$lp_img}{$name}@2x.{$ext} {$w2}w" );

    return sprintf(
        '<picture><source type="image/avif" srcset="%1$s" sizes="%3$s"><source type="image/webp" srcset="%2$s" sizes="%3$s"><img class="lp-shot" src="%4$s" alt="%5$s" width="%6$d" height="%7$d" %8$s></picture>',
        $set( 'avif' ),
        $set( 'webp' ),
        esc_attr( $sizes ),
        esc_url( "{$lp_img}{$name}.webp" ),
        esc_attr( $alt ),
        $w,
        $h,
        $hero ? 'fetchpriority="high" decoding="async"' : 'loading="lazy" decoding="async"'
    );
};

/** The amber "Start your free month" button. $plan 'auto' follows the seat calculator. */
$lp_button = static function ( string $location, string $plan, string $class = '', string $label = '' ) use ( $lp_cta, $lp_default_plan ): string {
    $href_plan = $plan === 'auto' ? $lp_default_plan : $plan;

    return sprintf(
        '<a class="lp-cta %1$s" href="%2$s" data-cta="%3$s" data-plan="%4$s">%5$s</a>',
        esc_attr( $class ),
        esc_url( $lp_cta( $href_plan ) ),
        esc_attr( $location ),
        esc_attr( $plan ),
        $label !== '' ? $label : 'Start your free month'
    );
};

$lp_points = [
    [
        [ 'Recurring visits', 'schedule themselves, week after week' ],
        [ 'Route planning', 'orders the day’s stops by drive time, like “17m drive”' ],
        [ 'Weather alerts', 'flag outdoor jobs before rain' ],
    ],
    [
        [ 'Works offline.', 'Clock in, finish jobs and take photos with no signal. It syncs when they’re back in range.' ],
        [ 'GPS clock in and out,', 'with timesheets ready for payroll' ],
        [ 'Before and after photos', 'saved to each job' ],
        [ 'Runs in the phone’s browser.', 'Add it to the home screen, nothing to install.' ],
    ],
    [
        [ 'Estimates that become jobs', 'with one click' ],
        [ 'Online card payments', 'straight to your bank' ],
        [ 'Automatic reminders', 'for overdue invoices' ],
    ],
];

$lp_faqs = [
    [ 'How does the free month work?', 'You pay $0 today. Your first charge is 30 days after you sign up, at your plan’s price. Cancel any time before then and you won’t be charged.' ],
    [ 'Does the crew app work without signal?', 'Yes. Crews can clock in, finish jobs and take photos with no signal. Everything is saved on the phone and syncs when they’re back in range.' ],
    [ 'Do my crews need to download an app?', 'No. Crews sign in from their phone’s web browser and can add it to their home screen. It works on iPhone and Android.' ],
    [ 'What if I add more people later?', 'On the Crew plan, extra logins are $15 a month each. Add or remove them any time and your next bill adjusts.' ],
    [ 'Can I cancel anytime?', 'Yes. There’s no contract. Cancel from Settings and you keep access until the end of the period you’ve paid for.' ],
];

$lp_check = '<svg class="lp-check" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 12.5l5 5L19.5 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$lp_seats      = FRAMEWORK_LP_DEFAULT_SEATS;
$lp_crew_cost  = FRAMEWORK_LP_PRICE_CREW + max( 0, $lp_seats - FRAMEWORK_LP_CREW_SEATS ) * FRAMEWORK_LP_EXTRA_SEAT;
$lp_seat_label = $lp_seats . ( $lp_seats === 1 ? ' person' : ' people' );

$lp_plans = [
    'individual' => [ 'Individual', 'Owner-operators working solo', FRAMEWORK_LP_PRICE_INDIVIDUAL, '1 login', [ 'Scheduling, routes and estimates', 'Invoices and online payments', 'Time clock and timesheets' ] ],
    'crew'       => [ 'Crew', 'Owners with one or more crews', FRAMEWORK_LP_PRICE_CREW, FRAMEWORK_LP_CREW_SEATS . ' logins · $' . FRAMEWORK_LP_EXTRA_SEAT . '/month per extra login', [ 'Everything in Individual', 'Crew phone app for every login', 'Dispatch across multiple crews' ] ],
];

$lp_jsonld = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map( static fn ( $f ) => [
        '@type'          => 'Question',
        'name'           => $f[0],
        'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $f[1] ],
    ], $lp_faqs ),
];
?><!doctype html>
<html lang="en-US">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( $lp_title ); ?> | Framework</title>
<meta name="description" content="<?php echo esc_attr( $lp_description ); ?>">
<?php if ( $lp_noindex ) : ?>
<meta name="robots" content="noindex, follow">
<?php endif; ?>
<link rel="canonical" href="<?php echo esc_url( $lp_url ); ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo esc_attr( $lp_title ); ?> | Framework">
<meta property="og:description" content="<?php echo esc_attr( $lp_description ); ?>">
<meta property="og:url" content="<?php echo esc_url( $lp_url ); ?>">
<meta property="og:image" content="<?php echo esc_url( $lp_img . 'og-hero-office.jpg' ); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="750">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#122e50">
<link rel="icon" href="<?php echo esc_url( $lp_theme . '/assets/img/favicon/favicon.ico' ); ?>" sizes="any">
<link rel="icon" href="<?php echo esc_url( $lp_theme . '/assets/img/favicon/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?php echo esc_url( $lp_theme . '/assets/img/favicon/apple-touch-icon.png' ); ?>">
<link rel="preload" href="<?php echo esc_url( $lp_font . 'barlow-condensed-700-latin.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo esc_url( $lp_font . 'barlow-400-latin.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" as="image" type="image/avif" imagesrcset="<?php echo esc_attr( "{$lp_img}hero-office.avif 720w, {$lp_img}hero-office@2x.avif 1440w" ); ?>" imagesizes="(min-width: 1180px) 520px, (min-width: 920px) 44vw, calc(100vw - 40px)" fetchpriority="high">
<?php echo framework_gtag_snippet(); // phpcs:ignore WordPress.Security.EscapeOutput -- inc/google-tag.php ?>
<style>
@font-face{font-family:"Barlow";font-style:normal;font-weight:400;font-display:swap;src:url(<?php echo esc_url( $lp_font . 'barlow-400-latin.woff2' ); ?>) format("woff2")}
@font-face{font-family:"Barlow";font-style:normal;font-weight:500;font-display:swap;src:url(<?php echo esc_url( $lp_font . 'barlow-500-latin.woff2' ); ?>) format("woff2")}
@font-face{font-family:"Barlow";font-style:normal;font-weight:600;font-display:swap;src:url(<?php echo esc_url( $lp_font . 'barlow-600-latin.woff2' ); ?>) format("woff2")}
@font-face{font-family:"Barlow Condensed";font-style:normal;font-weight:700;font-display:swap;src:url(<?php echo esc_url( $lp_font . 'barlow-condensed-700-latin.woff2' ); ?>) format("woff2")}
:root{--navy:#122e50;--mid:#294867;--slate:#566f8b;--amber:#ffa405;--amber-hover:#ffb631;--cream:#f6f1e9;--card:#fffdf9;--frame:#0b1a2c;--on-navy:#f6f1e9;--on-navy-2:#c6d2de;--line:rgba(18,46,80,.18);--line-soft:rgba(18,46,80,.14);--cond:"Barlow Condensed","Arial Narrow",system-ui,sans-serif;--pad:24px}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%;scroll-behavior:smooth}
@media (prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
body{margin:0;background:var(--cream);color:var(--navy);font:400 16px/1.5 "Barlow",system-ui,-apple-system,"Segoe UI",sans-serif;-webkit-font-smoothing:antialiased}
img{max-width:100%}
a{color:var(--navy);text-decoration:underline;text-decoration-color:rgba(18,46,80,.3);text-underline-offset:3px}
a:hover{text-decoration-color:var(--navy)}
a:focus-visible,button:focus-visible,input:focus-visible{outline:2px solid var(--mid);outline-offset:2px}
.lp-navy a:focus-visible,.lp-navy button:focus-visible{outline-color:var(--amber)}
.lp-wrap{max-width:calc(1180px + 2 * var(--pad));margin:0 auto;padding-left:max(var(--pad),env(safe-area-inset-left));padding-right:max(var(--pad),env(safe-area-inset-right))}
.lp-navy{background:var(--navy);color:var(--on-navy)}
.lp-caps{font-family:var(--cond);font-weight:700;text-transform:uppercase;letter-spacing:.06em}
.lp-h2{margin:0;font:700 clamp(32px,3.6vw,44px)/1.05 var(--cond);text-transform:uppercase;letter-spacing:.01em;text-wrap:balance}
.lp-sub{margin:12px 0 0;font-size:18px;color:var(--mid);max-width:640px}
.lp-num{font-variant-numeric:tabular-nums}

.lp-cta{display:inline-flex;align-items:center;justify-content:center;min-height:54px;padding:0 28px;background:var(--amber);color:var(--navy);font-weight:600;font-size:18px;text-decoration:none;white-space:nowrap;transition:background-color .15s}
.lp-cta:hover{background:var(--amber-hover)}
.lp-cta--ghost{background:transparent;box-shadow:inset 0 0 0 1px rgba(18,46,80,.3)}
.lp-cta--ghost:hover{background:rgba(18,46,80,.05)}

.lp-header .lp-wrap{display:flex;align-items:center;gap:16px;padding-top:14px;padding-bottom:14px}
.lp-logo{display:block;height:20px;width:auto;margin-right:auto}
.lp-header .lp-cta{min-height:44px;padding:0 18px;font-size:15px}
.lp-short{display:none}

.lp-hero{overflow:hidden}
.lp-hero .lp-wrap{display:grid;gap:40px;align-items:center;padding-top:48px}
.lp-hero__text{padding-bottom:48px}
.lp-eyebrow{font-size:16px;font-weight:600;color:var(--amber)}
.lp-hero h1{margin:10px 0 0;font:700 clamp(40px,5.4vw,62px)/1 var(--cond);text-transform:uppercase;letter-spacing:.01em;text-wrap:balance}
.lp-lede{margin:18px 0 0;font-size:19px;line-height:1.5;color:var(--on-navy-2);max-width:540px;text-wrap:pretty}
.lp-hero__actions{display:flex;flex-wrap:wrap;align-items:center;gap:14px 22px;margin-top:28px}
.lp-hero__actions a:not(.lp-cta){color:var(--on-navy);font-size:16px;text-decoration-color:rgba(246,241,233,.4);display:inline-flex;align-items:center;min-height:44px}
.lp-fine{margin:14px 0 0;font-size:15px;color:var(--on-navy-2)}
.lp-hero__visual{position:relative;padding:0 70px 40px 0;align-self:end}
.lp-browser{background:var(--frame);box-shadow:0 24px 60px rgba(0,0,0,.35)}
.lp-browser__bar{display:flex;align-items:center;gap:6px;height:22px;padding:0 10px}
.lp-browser__bar i{display:block;width:8px;height:8px;background:rgba(246,241,233,.25)}
.lp-shot{display:block;width:100%;height:auto}
.lp-phone{background:var(--frame);padding:7px;box-shadow:0 18px 40px rgba(0,0,0,.35)}
.lp-phone__screen{overflow:hidden;aspect-ratio:390/760}
.lp-hero .lp-phone{position:absolute;box-sizing:content-box;right:0;bottom:-10px;width:34%;min-width:150px;max-width:200px;box-shadow:0 24px 50px rgba(0,0,0,.45)}
.lp-hero .lp-phone__screen{aspect-ratio:390/700}

.lp-trades{background:var(--card);border-bottom:1px solid var(--line-soft)}
.lp-trades .lp-wrap{padding-top:28px;padding-bottom:30px}
.lp-trades__label{font-size:13px;color:var(--mid);margin:0 0 14px}
.lp-trades ul{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(max(150px,22%),1fr));gap:14px 24px}
.lp-trades li{display:flex;align-items:center;gap:10px;font-size:17px;font-weight:600}
.lp-trades li::before{content:"";flex:none;width:8px;height:8px;background:var(--amber)}

.lp-features{padding-top:72px;padding-bottom:24px}
.lp-features h2{max-width:760px}
.lp-rows{display:flex;flex-direction:column;gap:64px;margin-top:48px}
.lp-row{display:grid;gap:32px 40px;align-items:center}
.lp-tag{display:flex;align-items:center;gap:12px;font-size:16px;font-weight:600;color:var(--mid)}
.lp-tag span{flex:none;display:flex;align-items:center;justify-content:center;width:36px;height:36px;background:var(--navy);color:var(--on-navy);font:700 18px/1 var(--cond)}
.lp-row h3{margin:14px 0 0;font:700 34px/1.05 var(--cond);text-wrap:balance}
.lp-row p{margin:12px 0 0;font-size:17px;line-height:1.55;color:var(--mid);text-wrap:pretty}
.lp-points{list-style:none;margin:6px 0 0;padding:0}
.lp-points li{display:flex;gap:10px;font-size:16px;line-height:1.4;margin-top:10px}
.lp-points strong{font-weight:600}
.lp-check{flex:none;margin-top:1px;color:var(--navy)}
.lp-panel{background:var(--navy);padding:28px}
.lp-panel .lp-browser{box-shadow:0 18px 40px rgba(0,0,0,.3)}
.lp-panel .lp-browser__bar{height:18px;padding:0 9px}
.lp-panel .lp-browser__bar i{width:7px;height:7px}
.lp-phones{display:flex;justify-content:center;align-items:flex-start;gap:22px;padding:32px 24px}
.lp-phones .lp-phone:first-child{flex:0 1 230px}
.lp-phones .lp-phone:last-child{flex:0 1 200px;margin-top:48px}

.lp-pricing{padding-top:72px;padding-bottom:72px;scroll-margin-top:12px}
.lp-calc{margin-top:28px;border:1px solid var(--line);background:var(--card);padding:20px 22px;display:flex;flex-wrap:wrap;align-items:center;gap:16px 28px}
.lp-calc label{font-size:17px;font-weight:600}
.lp-calc output{flex:none;min-width:110px;font-size:17px;font-weight:600}
.lp-calc__line{flex:none;font-size:17px;color:var(--mid)}
.lp-range{flex:1;min-width:200px;height:44px;margin:0;background:transparent;-webkit-appearance:none;appearance:none;cursor:pointer}
.lp-range::-webkit-slider-runnable-track{height:6px;background:linear-gradient(var(--navy),var(--navy)) 0/var(--fill,16%) 100% no-repeat,rgba(18,46,80,.16)}
.lp-range::-moz-range-track{height:6px;background:rgba(18,46,80,.16)}
.lp-range::-moz-range-progress{height:6px;background:var(--navy)}
.lp-range::-webkit-slider-thumb{-webkit-appearance:none;width:22px;height:22px;margin-top:-8px;background:var(--navy);border:3px solid var(--card);box-shadow:0 0 0 1px var(--navy)}
.lp-range::-moz-range-thumb{width:16px;height:16px;border-radius:0;background:var(--navy);border:3px solid var(--card);box-shadow:0 0 0 1px var(--navy)}
.lp-range:focus-visible{outline-offset:4px}
.lp-plans{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));gap:20px;margin-top:20px}
.lp-plan{position:relative;border:1px solid var(--line);background:var(--card);padding:26px 26px 24px;display:flex;flex-direction:column}
.lp-plan.is-pick{border:2px solid var(--navy);padding:25px 25px 23px}
.lp-plan__fit{position:absolute;top:-13px;left:24px;background:var(--amber);color:var(--navy);font-size:13px;font-weight:600;padding:4px 10px}
.lp-plan:not(.is-pick) .lp-plan__fit{display:none}
.lp-plan__name{margin:0;font-size:20px;font-weight:600}
.lp-plan__who{font-size:15px;color:var(--slate);margin-top:2px}
.lp-plan__price{display:flex;align-items:baseline;gap:6px;margin-top:16px;font-size:16px;color:var(--mid)}
.lp-plan__price strong{font:700 56px/1 var(--cond);color:var(--navy)}
.lp-plan__extra{font-size:15px;color:var(--mid);margin-top:6px}
.lp-plan ul{list-style:none;padding:0;margin:20px 0 24px;display:flex;flex-direction:column;gap:9px;flex:1}
.lp-plan li{display:flex;gap:10px;font-size:16px}
.lp-plan .lp-cta{width:100%;min-height:52px;font-size:17px}
.lp-plan__first{font-size:14px;color:var(--slate);margin:10px 0 0;text-align:center}
.lp-pricing__note{margin:20px 0 0;font-size:16px;color:var(--mid)}

.lp-faq{max-width:calc(820px + 2 * var(--pad));padding-top:8px;padding-bottom:72px}
.lp-faq h2{font-size:clamp(30px,3.2vw,38px);margin-bottom:18px}
.lp-faq__list{border-top:1px solid var(--line)}
.lp-faq__item{border-bottom:1px solid var(--line)}
.lp-faq__item h3{margin:0;font:inherit}
.lp-faq button{width:100%;min-height:44px;display:flex;align-items:center;gap:16px;padding:18px 4px;border:0;background:transparent;font:600 17px/1.35 "Barlow",system-ui,sans-serif;color:var(--navy);text-align:left;cursor:pointer}
.lp-faq button:hover{background:rgba(18,46,80,.03)}
.lp-faq button span{flex:1}
.lp-faq button svg{flex:none;transition:transform .15s}
.lp-faq button[aria-expanded="true"] svg{transform:rotate(180deg)}
.lp-faq__answer p{margin:0;padding:0 40px 20px 4px;font-size:17px;line-height:1.55;color:var(--mid);text-wrap:pretty}

.lp-final .lp-wrap{display:flex;flex-wrap:wrap;align-items:center;gap:24px 40px;padding-top:60px;padding-bottom:60px}
.lp-final__text{flex:1;min-width:280px}
.lp-final p{margin:10px 0 0;font-size:18px;color:var(--on-navy-2)}
.lp-final .lp-cta{flex:none;min-height:56px;padding:0 30px}

.lp-footer .lp-wrap{display:flex;flex-wrap:wrap;gap:8px 24px;padding-top:24px;padding-bottom:24px;font-size:14px;color:var(--slate)}
.lp-footer a{color:var(--slate);display:inline-flex;align-items:center;min-height:24px}

.lp-sticky{display:none}

@media (min-width:920px){
  .lp-hero .lp-wrap{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media (min-width:900px){
  .lp-row{grid-template-columns:repeat(2,minmax(0,1fr))}
  .lp-row--flip .lp-row__text{order:2}
}
@media (max-width:640px){
  :root{--pad:20px}
  .lp-hero .lp-wrap{padding-top:36px;gap:28px}
  .lp-hero__text{padding-bottom:0}
  .lp-hero__actions{flex-direction:column;align-items:stretch}
  .lp-hero__actions a:not(.lp-cta){justify-content:center}
  .lp-cta{width:100%}
  .lp-header .lp-cta{width:auto}
  .lp-hero__visual{padding:0 26px 34px 0}
  .lp-hero .lp-phone{width:30%;min-width:0;padding:5px}
  .lp-features{padding-top:48px;padding-bottom:8px}
  .lp-rows{gap:48px;margin-top:32px}
  .lp-row h3{font-size:30px}
  .lp-panel{padding:16px}
  .lp-phones{padding:20px 16px;gap:14px}
  .lp-phones .lp-phone{flex:0 0 45%!important}
  .lp-phones .lp-phone:last-child{margin-top:36px}
  .lp-pricing{padding-top:48px;padding-bottom:48px}
  .lp-calc{padding:18px;gap:10px 16px}
  .lp-calc label{flex:1 0 100%}
  .lp-range{flex:1 0 100%;min-width:0}
  .lp-range::-webkit-slider-thumb{width:30px;height:30px;margin-top:-12px}
  .lp-range::-moz-range-thumb{width:24px;height:24px}
  .lp-plan{padding:24px 20px 22px}
  .lp-plan.is-pick{padding:23px 19px 21px}
  .lp-faq{padding-bottom:48px}
  .lp-faq__answer p{padding-right:4px}
  .lp-final .lp-wrap{padding-top:48px;padding-bottom:48px}
  .lp-final__text{min-width:0}
  .lp-sticky{display:block;position:fixed;left:0;right:0;bottom:0;z-index:20;background:var(--navy);padding:10px max(16px,env(safe-area-inset-right)) calc(10px + env(safe-area-inset-bottom)) max(16px,env(safe-area-inset-left));box-shadow:0 -8px 24px rgba(0,0,0,.18);transform:translateY(110%);visibility:hidden;transition:transform .2s,visibility 0s .2s}
  .lp-sticky.is-on{transform:none;visibility:visible;transition:transform .2s}
  .lp-sticky .lp-cta{min-height:52px;font-size:17px}
}
@media (max-width:399px){
  .lp-header .lp-long{display:none}
  .lp-header .lp-short{display:inline}
}
@media (prefers-reduced-motion:reduce){.lp-sticky,.lp-sticky.is-on,.lp-faq button svg,.lp-cta{transition:none}}
</style>
<noscript><style>.lp-faq__answer[hidden]{display:block}</style></noscript>
<script type="application/ld+json"><?php echo wp_json_encode( $lp_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
</head>
<body>

<header class="lp-header lp-navy">
    <div class="lp-wrap">
        <img class="lp-logo" src="<?php echo esc_url( $lp_theme . '/assets/img/framework-wordmark-reverse.svg' ); ?>" alt="Framework" width="168" height="20">
        <?php echo $lp_button( 'header', 'auto', '', '<span class="lp-long">Start your free month</span><span class="lp-short">Start free</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built and escaped above ?>
    </div>
</header>

<main>
    <section class="lp-hero lp-navy">
        <div class="lp-wrap">
            <div class="lp-hero__text">
                <p class="lp-eyebrow" style="margin:0"><?php echo esc_html( $lp_eyebrow ); ?></p>
                <h1><?php echo esc_html( $lp_headline ); ?></h1>
                <p class="lp-lede">Schedule jobs, send crews their day, track hours with GPS, and send invoices that clients can pay online. One app for the office and the crew.</p>
                <div class="lp-hero__actions">
                    <?php echo $lp_button( 'hero', 'auto', '', '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    <a href="#pricing">See pricing</a>
                </div>
                <p class="lp-fine">$0 today · from $<?php echo (int) FRAMEWORK_LP_PRICE_INDIVIDUAL; ?>/month after · cancel anytime</p>
            </div>

            <div class="lp-hero__visual">
                <div class="lp-browser">
                    <div class="lp-browser__bar" aria-hidden="true"><i></i><i></i><i></i></div>
                    <?php echo $lp_picture( 'hero-office', 'Framework office view: today’s jobs across three crews, with the clock-in card and crew filters', true, '(min-width: 1180px) 520px, (min-width: 920px) 44vw, calc(100vw - 66px)', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <div class="lp-phone">
                    <div class="lp-phone__screen">
                        <?php echo $lp_picture( 'hero-phone', 'Crew phone app: My schedule, showing the next start time and the next two weeks', false, '(min-width: 640px) 200px, 30vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="lp-trades" aria-labelledby="lp-trades-label">
        <div class="lp-wrap">
            <h2 class="lp-trades__label lp-caps" id="lp-trades-label">Built for</h2>
            <ul>
                <?php foreach ( [ 'Landscaping', 'Hardscaping', 'Tree care', 'Irrigation', 'Snow removal', 'Fencing & decks', 'Pressure washing', 'And more' ] as $lp_trade ) : ?>
                    <li><?php echo esc_html( $lp_trade ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="lp-features lp-wrap">
        <h2 class="lp-h2">From the schedule to getting paid, without the group texts</h2>
        <p class="lp-sub">Three things every crew business does every day, in one place.</p>

        <div class="lp-rows">
            <?php
            $lp_rows = [
                [ 'Scheduling, dispatch and routes', 'Plan the week in minutes', 'Put jobs on crews and days once. Recurring visits schedule themselves, and Smart Schedule fills a crew’s open day. Each crew’s stops are put in driving order, with the drive time between them.' ],
                [ 'Crew phone app and GPS time clock', 'A crew app that works with no signal', 'Crews see their day on their phone: the jobs, the addresses, the notes and the photos you need. Out past the last bar of signal, it keeps working.' ],
                [ 'Estimates, invoices and online payments', 'Get paid without chasing', 'Send an estimate, turn it into a job when it’s approved, and invoice the moment the work is done. Clients pay online by card, and late reminders go out on their own.' ],
            ];
            foreach ( $lp_rows as $lp_i => [ $lp_label, $lp_h3, $lp_p ] ) :
                ?>
                <div class="lp-row<?php echo $lp_i === 1 ? ' lp-row--flip' : ''; ?>">
                    <div class="lp-row__text">
                        <div class="lp-tag"><span aria-hidden="true"><?php echo (int) $lp_i + 1; ?></span><?php echo esc_html( $lp_label ); ?></div>
                        <h3><?php echo esc_html( $lp_h3 ); ?></h3>
                        <p><?php echo esc_html( $lp_p ); ?></p>
                        <ul class="lp-points">
                            <?php foreach ( $lp_points[ $lp_i ] as [ $lp_b, $lp_t ] ) : ?>
                                <li><?php echo $lp_check; // phpcs:ignore WordPress.Security.EscapeOutput ?><span><strong><?php echo esc_html( $lp_b ); ?></strong> <?php echo esc_html( $lp_t ); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php if ( $lp_i === 1 ) : ?>
                        <div class="lp-panel lp-phones">
                            <div class="lp-phone"><div class="lp-phone__screen"><?php echo $lp_picture( 'crew-offline', 'Crew app with no signal: an Offline notice says actions are saved on this phone', false, '(min-width: 640px) 230px, 42vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
                            <div class="lp-phone"><div class="lp-phone__screen"><?php echo $lp_picture( 'crew-clock', 'Clock page: on the clock for 5:13, stamped at the shop with GPS', false, '(min-width: 640px) 200px, 42vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
                        </div>
                    <?php else : ?>
                        <div class="lp-panel">
                            <div class="lp-browser">
                                <div class="lp-browser__bar" aria-hidden="true"><i></i><i></i><i></i></div>
                                <?php
                                echo $lp_i === 0 // phpcs:ignore WordPress.Security.EscapeOutput
                                    ? $lp_picture( 'route', 'Route page: the crew’s stops on a map, in driving order, with the drive time to each stop', true, '(min-width: 1180px) 514px, (min-width: 900px) 44vw, calc(100vw - 72px)' )
                                    : $lp_picture( 'invoices', 'Invoices and estimates list with status filters, issue dates, due dates and balances', true, '(min-width: 1180px) 514px, (min-width: 900px) 44vw, calc(100vw - 72px)' );
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php /* Customer quotes go here, above pricing, once there are real ones (2–3). No placeholders. */ ?>

    <section class="lp-pricing lp-wrap" id="pricing" aria-labelledby="lp-pricing-title">
        <h2 class="lp-h2" id="lp-pricing-title">Simple pricing. First month free.</h2>
        <p class="lp-sub">Every feature on both plans. You only pay for logins.</p>

        <div class="lp-calc">
            <label for="lp-seats">How many people need a login?</label>
            <input class="lp-range" id="lp-seats" type="range" min="1" max="20" step="1" value="<?php echo (int) $lp_seats; ?>" aria-describedby="lp-calc-line" style="--fill:<?php echo round( ( $lp_seats - 1 ) / 19 * 100, 2 ); ?>%">
            <output class="lp-num" for="lp-seats" id="lp-seats-out"><?php echo esc_html( $lp_seat_label ); ?></output>
            <span class="lp-calc__line lp-num" id="lp-calc-line" aria-live="polite">Crew: $<?php echo (int) $lp_crew_cost; ?>/month, up to <?php echo (int) FRAMEWORK_LP_CREW_SEATS; ?> logins</span>
        </div>

        <div class="lp-plans">
            <?php foreach ( $lp_plans as $lp_key => [ $lp_name, $lp_who, $lp_price, $lp_extra, $lp_list ] ) :
                $lp_pick = $lp_key === $lp_default_plan;
                ?>
                <div class="lp-plan<?php echo $lp_pick ? ' is-pick' : ''; ?>" data-plan-card="<?php echo esc_attr( $lp_key ); ?>">
                    <span class="lp-plan__fit" data-fit>Best fit for <?php echo esc_html( $lp_seat_label ); ?></span>
                    <h3 class="lp-plan__name"><?php echo esc_html( $lp_name ); ?></h3>
                    <div class="lp-plan__who"><?php echo esc_html( $lp_who ); ?></div>
                    <div class="lp-plan__price"><strong class="lp-num">$<?php echo (int) $lp_price; ?></strong>/month</div>
                    <div class="lp-plan__extra"><?php echo esc_html( $lp_extra ); ?></div>
                    <ul>
                        <?php foreach ( $lp_list as $lp_item ) : ?>
                            <li><?php echo $lp_check; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $lp_item ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php echo $lp_button( 'pricing_' . $lp_key, $lp_key, $lp_pick ? '' : 'lp-cta--ghost', '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    <p class="lp-plan__first lp-num" data-first>$0 today · then $<?php echo (int) ( $lp_key === 'crew' ? $lp_crew_cost : $lp_price ); ?>/month</p>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="lp-pricing__note">First month free on both plans. Cancel anytime.</p>
    </section>

    <section class="lp-faq lp-wrap" aria-labelledby="lp-faq-title">
        <h2 class="lp-h2" id="lp-faq-title">Questions</h2>
        <div class="lp-faq__list">
            <?php foreach ( $lp_faqs as $lp_i => [ $lp_q, $lp_a ] ) : ?>
                <div class="lp-faq__item">
                    <h3>
                        <button type="button" aria-expanded="<?php echo $lp_i === 0 ? 'true' : 'false'; ?>" aria-controls="lp-faq-<?php echo (int) $lp_i; ?>" id="lp-faq-q-<?php echo (int) $lp_i; ?>">
                            <span><?php echo esc_html( $lp_q ); ?></span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="#294867" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </h3>
                    <div class="lp-faq__answer" id="lp-faq-<?php echo (int) $lp_i; ?>" role="region" aria-labelledby="lp-faq-q-<?php echo (int) $lp_i; ?>"<?php echo $lp_i === 0 ? '' : ' hidden'; ?>>
                        <p><?php echo esc_html( $lp_a ); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="lp-final lp-navy" id="lp-final">
        <div class="lp-wrap">
            <div class="lp-final__text">
                <h2 class="lp-h2">Run next week from Framework</h2>
                <p>Set up your crews and first jobs in an afternoon. $0 for the first month.</p>
            </div>
            <?php echo $lp_button( 'final', 'auto', '', '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
    </section>
</main>

<footer class="lp-footer">
    <div class="lp-wrap">
        <span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Framework</span>
        <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy</a>
        <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a>
    </div>
</footer>

<div class="lp-sticky lp-navy" id="lp-sticky" aria-hidden="true">
    <?php echo $lp_button( 'sticky', 'auto', '', '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

<script>
(function () {
  var PRICE_IND = <?php echo (int) FRAMEWORK_LP_PRICE_INDIVIDUAL; ?>, PRICE_CREW = <?php echo (int) FRAMEWORK_LP_PRICE_CREW; ?>,
      CREW_SEATS = <?php echo (int) FRAMEWORK_LP_CREW_SEATS; ?>, EXTRA = <?php echo (int) FRAMEWORK_LP_EXTRA_SEAT; ?>;
  window.dataLayer = window.dataLayer || [];
  function track(name, params) {
    if (typeof window.gtag === 'function') { window.gtag('event', name, params); }
    else { var e = { event: name }; for (var k in params) e[k] = params[k]; window.dataLayer.push(e); }
  }
  function setPlan(a, plan) {
    var u = new URL(a.href, location.href); u.searchParams.set('plan', plan); a.href = u.toString();
  }

  // Every "Start your free month" button.
  document.querySelectorAll('[data-cta]').forEach(function (a) {
    a.addEventListener('click', function () {
      var plan = new URL(a.href, location.href).searchParams.get('plan');
      track('begin_checkout', { plan: plan, button_location: a.getAttribute('data-cta') });
    });
  });

  // Seat calculator.
  var range = document.getElementById('lp-seats'), out = document.getElementById('lp-seats-out'),
      line = document.getElementById('lp-calc-line'), used = false;
  function update() {
    var n = parseInt(range.value, 10) || 1, crew = n > 1, extra = Math.max(0, n - CREW_SEATS),
        cost = PRICE_CREW + extra * EXTRA, label = n + (n === 1 ? ' person' : ' people');
    range.style.setProperty('--fill', ((n - 1) / 19 * 100) + '%');
    out.textContent = label;
    line.textContent = !crew ? 'Individual: $' + PRICE_IND + '/month'
      : extra ? 'Crew: $' + PRICE_CREW + ' + ' + extra + ' × $' + EXTRA + ' = $' + cost + '/month'
      : 'Crew: $' + PRICE_CREW + '/month, up to ' + CREW_SEATS + ' logins';
    document.querySelectorAll('[data-plan-card]').forEach(function (card) {
      var key = card.getAttribute('data-plan-card'), pick = (key === 'crew') === crew;
      card.classList.toggle('is-pick', pick);
      card.querySelector('[data-fit]').textContent = 'Best fit for ' + label;
      card.querySelector('[data-cta]').classList.toggle('lp-cta--ghost', !pick);
      card.querySelector('[data-first]').textContent = '$0 today · then $' + (key === 'crew' ? cost : PRICE_IND) + '/month'
        + (key === 'crew' && extra ? ' for ' + n + ' logins' : '');
    });
    document.querySelectorAll('[data-plan="auto"]').forEach(function (a) { setPlan(a, crew ? 'crew' : 'individual'); });
  }
  range.addEventListener('input', function () {
    if (!used) { used = true; track('seat_calculator_used', { seats: parseInt(range.value, 10) }); }
    update();
  });
  update();

  // FAQ: one answer open at a time.
  var buttons = document.querySelectorAll('.lp-faq button');
  buttons.forEach(function (b) {
    b.addEventListener('click', function () {
      var open = b.getAttribute('aria-expanded') !== 'true';
      buttons.forEach(function (o) {
        var on = o === b && open;
        o.setAttribute('aria-expanded', on ? 'true' : 'false');
        document.getElementById(o.getAttribute('aria-controls')).hidden = !on;
      });
    });
  });

  // Phone sticky bar: after the hero button scrolls away, until the final call to action.
  var sticky = document.getElementById('lp-sticky'), heroBtn = document.querySelector('[data-cta="hero"]'),
      final = document.getElementById('lp-final');
  if (sticky && 'IntersectionObserver' in window) {
    var heroGone = false, finalSeen = false;
    var show = function () {
      var on = heroGone && !finalSeen;
      sticky.classList.toggle('is-on', on);
      sticky.setAttribute('aria-hidden', on ? 'false' : 'true');
      sticky.querySelector('a').tabIndex = on ? 0 : -1;
    };
    new IntersectionObserver(function (es) {
      es.forEach(function (e) { heroGone = !e.isIntersecting && e.boundingClientRect.top < 0; }); show();
    }).observe(heroBtn);
    new IntersectionObserver(function (es) {
      es.forEach(function (e) { finalSeen = e.isIntersecting || e.boundingClientRect.top < 0; }); show();
    }).observe(final);
    show();
  }
})();
</script>
</body>
</html>
