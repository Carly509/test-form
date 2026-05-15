<?php
/**
 * Plugin Name: Application Form Submissions
 * Description: Stores application form submissions in a custom table and exposes a REST endpoint.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Application Form
 * Text Domain: application-form-submissions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AFS_REST_NAMESPACE', 'application-form/v1' );
define( 'AFS_AJAX_ACTION', 'afs_submit_application_form' );

function afs_table_name() {
    global $wpdb;

    return $wpdb->prefix . 'test_submissions';
}

function afs_activate() {
    global $wpdb;

    $table_name      = afs_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        email varchar(190) NOT NULL,
        phone_number varchar(50) DEFAULT '' NOT NULL,
        country varchar(100) DEFAULT '' NOT NULL,
        date_of_birth date DEFAULT NULL,
        agreed_to_terms tinyint(1) NOT NULL DEFAULT 0,
        ip_address varchar(100) DEFAULT '' NOT NULL,
        user_agent text NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY email (email),
        KEY created_at (created_at)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'afs_activate' );

function afs_register_rest_routes() {
    register_rest_route(
        AFS_REST_NAMESPACE,
        '/submissions',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'afs_create_submission',
            'permission_callback' => '__return_true',
        )
    );
}
add_action( 'rest_api_init', 'afs_register_rest_routes' );

function afs_get_string_param( WP_REST_Request $request, $key ) {
    $value = $request->get_param( $key );

    if ( null === $value || ! is_scalar( $value ) ) {
        return '';
    }

    return sanitize_text_field( (string) $value );
}

function afs_get_email_param( WP_REST_Request $request, $key ) {
    $value = $request->get_param( $key );

    if ( null === $value || ! is_scalar( $value ) ) {
        return '';
    }

    return sanitize_email( (string) $value );
}

function afs_get_posted_string( $key ) {
    if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) {
        return '';
    }

    return sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) );
}

function afs_get_posted_email( $key ) {
    if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) {
        return '';
    }

    return sanitize_email( wp_unslash( (string) $_POST[ $key ] ) );
}

function afs_validate_submission( array $data ) {
    $errors = array();

    if ( '' === $data['firstName'] ) {
        $errors['firstName'] = __( 'First name is required', 'application-form-submissions' );
    }

    if ( '' === $data['lastName'] ) {
        $errors['lastName'] = __( 'Last name is required', 'application-form-submissions' );
    }

    if ( '' === $data['email'] ) {
        $errors['email'] = __( 'Email is required', 'application-form-submissions' );
    } elseif ( ! is_email( $data['email'] ) ) {
        $errors['email'] = __( 'Please enter a valid email address', 'application-form-submissions' );
    }

    if ( ! $data['agreedToTerms'] ) {
        $errors['agreedToTerms'] = __( 'You must agree to the terms and conditions', 'application-form-submissions' );
    }

    if ( '' !== $data['dateOfBirth'] ) {
        $date = DateTime::createFromFormat( 'Y-m-d', $data['dateOfBirth'] );

        if ( ! $date || $date->format( 'Y-m-d' ) !== $data['dateOfBirth'] ) {
            $errors['dateOfBirth'] = __( 'Please enter a valid date of birth.', 'application-form-submissions' );
        }
    }

    return $errors;
}

function afs_save_submission( array $data ) {
    global $wpdb;

    $inserted = $wpdb->insert(
        afs_table_name(),
        array(
            'first_name'       => $data['firstName'],
            'last_name'        => $data['lastName'],
            'email'            => $data['email'],
            'phone_number'     => $data['phoneNumber'],
            'country'          => $data['country'],
            'date_of_birth'    => '' === $data['dateOfBirth'] ? null : $data['dateOfBirth'],
            'agreed_to_terms'  => $data['agreedToTerms'] ? 1 : 0,
            'ip_address'       => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
            'user_agent'       => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'created_at'       => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
    );

    if ( false === $inserted ) {
        return new WP_Error(
            'afs_save_failed',
            __( 'Failed to save application.', 'application-form-submissions' ),
            array( 'status' => 500 )
        );
    }

    return (int) $wpdb->insert_id;
}

function afs_create_submission( WP_REST_Request $request ) {
    $nonce = $request->get_header( 'X-WP-Nonce' );

    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
        return new WP_REST_Response(
            array(
                'message' => __( 'Unable to verify this request.', 'application-form-submissions' ),
            ),
            403
        );
    }

    $data = array(
        'firstName'     => afs_get_string_param( $request, 'firstName' ),
        'lastName'      => afs_get_string_param( $request, 'lastName' ),
        'email'         => afs_get_email_param( $request, 'email' ),
        'phoneNumber'   => afs_get_string_param( $request, 'phoneNumber' ),
        'country'       => afs_get_string_param( $request, 'country' ),
        'dateOfBirth'   => afs_get_string_param( $request, 'dateOfBirth' ),
        'agreedToTerms' => rest_sanitize_boolean( $request->get_param( 'agreedToTerms' ) ),
    );
    $errors = afs_validate_submission( $data );

    if ( ! empty( $errors ) ) {
        return new WP_REST_Response(
            array(
                'message' => __( 'Please correct the highlighted fields.', 'application-form-submissions' ),
                'errors'  => $errors,
            ),
            422
        );
    }

    $submission_id = afs_save_submission( $data );

    if ( is_wp_error( $submission_id ) ) {
        return new WP_REST_Response(
            array(
                'message' => $submission_id->get_error_message(),
            ),
            $submission_id->get_error_data()['status']
        );
    }

    return new WP_REST_Response(
        array(
            'id'      => $submission_id,
            'message' => __( 'Application submitted successfully.', 'application-form-submissions' ),
        ),
        201
    );
}

function afs_handle_ajax_submission() {
    check_ajax_referer( 'afs_submit_application_form', 'nonce' );

    $data = array(
        'firstName'     => afs_get_posted_string( 'firstName' ),
        'lastName'      => afs_get_posted_string( 'lastName' ),
        'email'         => afs_get_posted_email( 'email' ),
        'phoneNumber'   => afs_get_posted_string( 'phoneNumber' ),
        'country'       => afs_get_posted_string( 'country' ),
        'dateOfBirth'   => afs_get_posted_string( 'dateOfBirth' ),
        'agreedToTerms' => ! empty( $_POST['agreedToTerms'] ),
    );
    $errors = afs_validate_submission( $data );

    if ( ! empty( $errors ) ) {
        wp_send_json_error(
            array(
                'message' => __( 'Please correct the highlighted fields.', 'application-form-submissions' ),
                'errors'  => $errors,
            ),
            422
        );
    }

    $submission_id = afs_save_submission( $data );

    if ( is_wp_error( $submission_id ) ) {
        wp_send_json_error(
            array(
                'message' => $submission_id->get_error_message(),
            ),
            $submission_id->get_error_data()['status']
        );
    }

    wp_send_json_success(
        array(
            'id'      => $submission_id,
            'message' => __( 'Application submitted successfully.', 'application-form-submissions' ),
        ),
        201
    );
}
add_action( 'wp_ajax_' . AFS_AJAX_ACTION, 'afs_handle_ajax_submission' );
add_action( 'wp_ajax_nopriv_' . AFS_AJAX_ACTION, 'afs_handle_ajax_submission' );

function afs_admin_menu() {
    add_menu_page(
        __( 'Form Submissions', 'application-form-submissions' ),
        __( 'Form Submissions', 'application-form-submissions' ),
        'manage_options',
        'afs-submissions',
        'afs_render_admin_page',
        'dashicons-feedback',
        26
    );
}
add_action( 'admin_menu', 'afs_admin_menu' );

function afs_render_admin_page() {
    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to view this page.', 'application-form-submissions' ) );
    }

    $table_name = afs_table_name();
    $paged      = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
    $per_page   = 20;
    $offset     = ( $paged - 1 ) * $per_page;
    $total      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
    $entries    = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Form Submissions', 'application-form-submissions' ); ?></h1>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'ID', 'application-form-submissions' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Name', 'application-form-submissions' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Email', 'application-form-submissions' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Phone', 'application-form-submissions' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Country', 'application-form-submissions' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Date of Birth', 'application-form-submissions' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Submitted', 'application-form-submissions' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $entries ) ) : ?>
                    <tr>
                        <td colspan="7"><?php esc_html_e( 'No submissions yet.', 'application-form-submissions' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $entries as $entry ) : ?>
                        <tr>
                            <td><?php echo esc_html( $entry->id ); ?></td>
                            <td><?php echo esc_html( trim( $entry->first_name . ' ' . $entry->last_name ) ); ?></td>
                            <td><a href="mailto:<?php echo esc_attr( $entry->email ); ?>"><?php echo esc_html( $entry->email ); ?></a></td>
                            <td><?php echo esc_html( $entry->phone_number ); ?></td>
                            <td><?php echo esc_html( $entry->country ); ?></td>
                            <td><?php echo esc_html( $entry->date_of_birth ); ?></td>
                            <td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry->created_at ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    echo wp_kses_post(
                        paginate_links(
                            array(
                                'base'      => add_query_arg( 'paged', '%#%' ),
                                'format'    => '',
                                'current'   => $paged,
                                'total'     => $total_pages,
                                'prev_text' => __( '&laquo;', 'application-form-submissions' ),
                                'next_text' => __( '&raquo;', 'application-form-submissions' ),
                            )
                        )
                    );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
