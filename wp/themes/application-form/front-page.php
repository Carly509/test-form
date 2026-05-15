<?php
get_header();

$application_form_countries = array(
    'United States',
    'United Kingdom',
    'Canada',
    'Australia',
    'Germany',
    'France',
    'Spain',
    'Italy',
    'Netherlands',
    'Brazil',
    'Mexico',
    'Japan',
    'China',
    'India',
    'Israel',
    'South Africa',
);
?>

<main id="primary" class="application-page">
    <div class="application-shell">
        <h1 class="application-title"><?php esc_html_e( 'Submit your application', 'application-form' ); ?></h1>

        <section class="application-card" aria-labelledby="application-section-title">
            <div class="application-intro">
                <h2 id="application-section-title"><?php esc_html_e( 'Personal Information', 'application-form' ); ?></h2>
                <p><?php esc_html_e( 'Please fill in all mandatory fields', 'application-form' ); ?></p>
            </div>

            <form id="application-form" class="application-form" method="post" novalidate>
                <div class="application-fields">
                    <div class="application-field">
                        <input type="text" name="firstName" id="firstName" placeholder="<?php echo esc_attr_x( '*First Name', 'form placeholder', 'application-form' ); ?>" autocomplete="given-name" aria-describedby="error-firstName">
                        <p class="application-error" id="error-firstName" aria-live="polite"></p>
                    </div>

                    <div class="application-field">
                        <input type="text" name="lastName" id="lastName" placeholder="<?php echo esc_attr_x( '*Last Name', 'form placeholder', 'application-form' ); ?>" autocomplete="family-name" aria-describedby="error-lastName">
                        <p class="application-error" id="error-lastName" aria-live="polite"></p>
                    </div>

                    <div class="application-field">
                        <input type="email" name="email" id="email" placeholder="<?php echo esc_attr_x( '*Email', 'form placeholder', 'application-form' ); ?>" autocomplete="email" aria-describedby="error-email">
                        <p class="application-error" id="error-email" aria-live="polite"></p>
                    </div>

                    <div class="application-field">
                        <input type="tel" name="phoneNumber" id="phoneNumber" placeholder="<?php echo esc_attr_x( 'Phone Number', 'form placeholder', 'application-form' ); ?>" autocomplete="tel">
                    </div>

                    <div class="application-field">
                        <select name="country" id="country" aria-label="<?php echo esc_attr_x( 'Choose Country', 'form label', 'application-form' ); ?>">
                            <option value="" disabled selected><?php esc_html_e( 'Choose Country', 'application-form' ); ?></option>
                            <?php foreach ( $application_form_countries as $application_form_country ) : ?>
                                <option value="<?php echo esc_attr( $application_form_country ); ?>"><?php echo esc_html( $application_form_country ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="application-field application-date-field">
                        <input type="date" name="dateOfBirth" id="dateOfBirth" aria-label="<?php echo esc_attr_x( 'Date of Birth', 'form label', 'application-form' ); ?>">
                        <span><?php esc_html_e( 'Date of Birth', 'application-form' ); ?></span>
                        <img src="<?php echo esc_url( APPLICATION_FORM_URI . '/assets/images/calendar.svg' ); ?>" alt="" width="18" height="18" class="application-date-icon" aria-hidden="true">
                    </div>
                </div>

                <div class="application-lower">
                    <div class="application-actions">
                        <div class="application-divider"></div>

                        <div class="application-terms">
                            <div class="application-checkbox-wrap">
                                <input type="checkbox" name="agreedToTerms" id="agreedToTerms" aria-describedby="error-agreedToTerms">
                                <label for="agreedToTerms" class="application-checkbox">
                                    <img src="<?php echo esc_url( APPLICATION_FORM_URI . '/assets/images/check.svg' ); ?>" alt="" width="16" height="16" aria-hidden="true">
                                </label>
                            </div>
                            <label for="agreedToTerms" class="application-terms-label">
                                <?php esc_html_e( 'I have read and agree to the', 'application-form' ); ?>
                                <a href="#"><?php esc_html_e( 'Terms and Conditions', 'application-form' ); ?></a>
                                <?php esc_html_e( 'and the', 'application-form' ); ?>
                                <a href="#"><?php esc_html_e( 'Privacy Policy', 'application-form' ); ?></a>
                            </label>
                        </div>
                        <p class="application-error application-terms-error" id="error-agreedToTerms" aria-live="polite"></p>
                        <p class="application-status" id="application-status" aria-live="polite"></p>

                        <div class="application-submit-wrap">
                            <button type="submit" class="application-submit">
                                <?php esc_html_e( 'Submit', 'application-form' ); ?>
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="application-illustration">
                        <img src="<?php echo esc_url( APPLICATION_FORM_URI . '/assets/images/form-illustration.png' ); ?>" alt="<?php echo esc_attr_x( 'Application illustration', 'image alt text', 'application-form' ); ?>" width="301" height="280">
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>

<?php
get_footer();
