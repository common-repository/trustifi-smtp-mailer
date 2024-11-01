<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Hook into phpmailer_init to configure SMTP
add_action('phpmailer_init', 'trustifi_mailer_smtp_setup');

function trustifi_mailer_smtp_setup($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.trustifi.com'; // Trustifi SMTP host
    $phpmailer->SMTPAuth   = true; // Use authentication
    $phpmailer->Port       = 587; // Trustifi SMTP port
    $phpmailer->Username   = get_option('trustifi_mailer_smtp_username'); // Get Trustifi SMTP username from settings
    $phpmailer->Password   = get_option('trustifi_mailer_smtp_password'); // Get Trustifi SMTP password from settings
    $phpmailer->SMTPSecure = 'tls'; // Use TLS

    // Set From email and name
    $from_email = get_option('trustifi_mailer_from_email');
    $from_name = get_option('trustifi_mailer_from_name');
    $phpmailer->From       = $from_email;
    $phpmailer->FromName   = $from_name;
}
