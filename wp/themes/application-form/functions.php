<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'APPLICATION_FORM_VERSION', '1.0.3' );
define( 'APPLICATION_FORM_DIR', get_template_directory() );
define( 'APPLICATION_FORM_URI', get_template_directory_uri() );

function application_form_setup() {
    load_theme_textdomain( 'application-form', APPLICATION_FORM_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'automatic-feed-links' );
}
add_action( 'after_setup_theme', 'application_form_setup' );

function application_form_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
        );
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }

    return $urls;
}
add_filter( 'wp_resource_hints', 'application_form_resource_hints', 10, 2 );

function application_form_scripts() {
    wp_enqueue_style(
        'application-form-font',
        'https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'application-form-style',
        APPLICATION_FORM_URI . '/assets/css/form.css',
        array( 'application-form-font' ),
        APPLICATION_FORM_VERSION
    );

    wp_add_inline_style(
        'application-form-style',
        ':root{--application-select-icon:url("' . esc_url( APPLICATION_FORM_URI . '/assets/images/arrow-down.svg' ) . '");}'
    );

    wp_enqueue_script(
        'application-form-script',
        APPLICATION_FORM_URI . '/assets/js/form.js',
        array(),
        APPLICATION_FORM_VERSION,
        true
    );

    wp_localize_script(
        'application-form-script',
        'applicationFormData',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'action'  => 'afs_submit_application_form',
            'nonce'   => wp_create_nonce( 'afs_submit_application_form' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'application_form_scripts' );
