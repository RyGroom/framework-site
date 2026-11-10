<?php
/**
 * Template Name: Text messages (SMS)
 *
 * The public page Twilio and carrier reviewers check for A2P 10DLC: a
 * working copy of the request form (the real embed code of the app's
 * "Framework Demo Co.", stored on this page by tools/sms-optin-2026-11.php),
 * how a business records consent itself, and what clients receive.
 *
 * The opt-in and confirmation wording is copied from the app's
 * App\Support\SmsConsent; keep the two the same.
 */
get_header();

$framework_embed = (string) get_post_meta( get_the_ID(), 'framework_sms_demo_embed', true );
$framework_img   = get_template_directory_uri() . '/assets/img/sms/';
$framework_terms = home_url( '/terms/#sms' );
$framework_priv  = home_url( '/privacy/#text-messages' );
?>
<article class="c-content c-sms">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="c-content__inner">
                    <h1 class="c-content__title">Text messages</h1>
                    <div class="c-content__body">
                        <p>Businesses that use Framework embed this request form on their websites. Clients who tick the box agree to receive service texts from that business, sent through Framework.</p>
                        <p>Below is a working copy of the form for our demo company, Framework Demo Co. The text message box is unticked to start with and is optional: the form sends without it.</p>

                        <div class="c-sms__form">
                            <?php if ( $framework_embed !== '' ) : ?>
                                <?php echo $framework_embed; // phpcs:ignore WordPress.Security.EscapeOutput -- the app's own embed code, saved by an administrator tool ?>
                            <?php else : ?>
                                <p class="c-sms__missing">The demo form isn't connected yet. Run tools/sms-optin-2026-11.php with the embed code from <code>php artisan app:sms-demo-company</code>.</p>
                            <?php endif; ?>
                        </div>

                        <h2 id="consent">Consent recorded by the business</h2>
                        <p>A business can also mark a client as agreeing to texts in Framework, but only after the client agrees by phone or in person. The setting is off for every client until someone turns it on.</p>
                        <figure class="c-sms__shot">
                            <img src="<?php echo esc_url( $framework_img . 'consent-switch-off.png' ); ?>" width="840" height="257" alt="A client's settings in Framework. &quot;Agreed to text messages&quot; is off, with the note: Turn on only after they agree, by phone or in person." loading="lazy">
                            <figcaption>On each client's page, &quot;Agreed to text messages&quot; is off until the business turns it on.</figcaption>
                        </figure>
                        <figure class="c-sms__shot c-sms__shot--narrow">
                            <img src="<?php echo esc_url( $framework_img . 'consent-confirm-prompt.png' ); ?>" width="520" height="337" alt="A prompt titled &quot;Did they agree to texts?&quot; asking staff to confirm the client said yes by phone or in person, with Cancel and They agreed buttons." loading="lazy">
                            <figcaption>Turning it on asks staff to confirm the client said yes. Nothing is recorded if they cancel.</figcaption>
                        </figure>
                        <figure class="c-sms__shot">
                            <img src="<?php echo esc_url( $framework_img . 'consent-recorded.png' ); ?>" width="840" height="277" alt="The same setting turned on: Since Oct 14, 2026, recorded by the office (Morgan Hale)." loading="lazy">
                            <figcaption>Framework records when and how consent was given: on the request form, or by which staff member.</figcaption>
                        </figure>
                        <p>Changing a client's phone number removes their consent until they agree again for the new number. A business can't turn texts back on for anyone who replied STOP.</p>

                        <h2 id="messages">What clients receive</h2>
                        <p>Only messages about their appointments with that business: visit reminders, schedule changes, and on-my-way or running-late updates. No marketing. Message frequency varies. Msg &amp; data rates may apply. Texts only go out between 8am and 8pm in the business's time zone, and only to US numbers.</p>
                        <p>Each business texts from its own phone number, and every text starts with the business's name and &quot;via Framework&quot;. When consent is recorded, the client gets one confirmation:</p>
                        <blockquote class="c-sms__text">Framework Demo Co. via Framework: You're signed up for appointment texts. Msg frequency varies. Msg &amp; data rates may apply. Reply HELP for help, STOP to opt out.</blockquote>
                        <p>Examples of the texts that follow:</p>
                        <blockquote class="c-sms__text">Framework Demo Co. via Framework: Reminder: your Lawn mowing is scheduled for tomorrow, 9:00am–11:00am. Reply STOP to opt out.</blockquote>
                        <blockquote class="c-sms__text">Framework Demo Co. via Framework: We need to move your Lawn mowing planned for Wednesday, November 11. We'll be in touch with a new time. Reply STOP to opt out.</blockquote>
                        <blockquote class="c-sms__text">Framework Demo Co. via Framework: We're on our way for your Lawn mowing (arrival window 9:00am–11:00am). Reply STOP to opt out.</blockquote>
                        <blockquote class="c-sms__text">Framework Demo Co. via Framework: We're running a little behind today. We're still coming for your Lawn mowing, just later than planned. Reply STOP to opt out.</blockquote>

                        <h2 id="help-stop">Help and opting out</h2>
                        <ul>
                            <li>Reply <strong>HELP</strong> for help. You get an automatic reply, and you can also email <a href="mailto:support@theframework.app">support@theframework.app</a>.</li>
                            <li>Reply <strong>STOP</strong> to opt out. You get one reply confirming you're unsubscribed. Each business texts from its own number, so STOP only stops texts from that business; other businesses that use Framework aren't affected.</li>
                            <li>Reply <strong>START</strong> to get texts again.</li>
                        </ul>
                        <p>Read the full <a href="<?php echo esc_url( $framework_terms ); ?>">Text Messaging Terms</a> and the text messages section of our <a href="<?php echo esc_url( $framework_priv ); ?>">Privacy Policy</a>. Carriers are not liable for delayed or undelivered messages.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>
<?php
get_footer();
