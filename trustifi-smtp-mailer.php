<?php
/*
Plugin Name: Trustifi SMTP Mailer
Plugin URI: https://trustifi.com/
Description: Sends all WordPress emails using Trustifi SMTP Mailer via a secured channel and encrypted traffic.
Author: Trustifi
Author URI: https://trustifi.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Version: 1.0.0
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants for paths
define('TRUSTIFI_MAILER_PATH', plugin_dir_path(__FILE__));
define('TRUSTIFI_MAILER_URL', plugin_dir_url(__FILE__));
define('TRUSTIFI_MAILER_ICON', TRUSTIFI_MAILER_URL . 'assets/images/icon.png');

// Include necessary files
require_once TRUSTIFI_MAILER_PATH . 'includes/settings.php';
require_once TRUSTIFI_MAILER_PATH . 'includes/smtp-setup.php';

// Hook to add the plugin menu
add_action('admin_menu', 'trustifi_mailer_add_menu');

function trustifi_mailer_add_menu() {
    // Add top-level menu page
    add_menu_page(
        'Trustifi SMTP Mailer',            // Page title
        'Trustifi SMTP Mailer',            // Menu title
        'manage_options',                  // Capability required
        'trustifi-smtp-mailer',            // Menu slug
        'trustifi_mailer_options_page',    // Function to display the settings page
        TRUSTIFI_MAILER_ICON,              // Menu icon
        65                                 // Position in the menu
    );
}

// Add a link to the plugin row in the Plugins page
add_filter('plugin_row_meta', 'trustifi_mailer_plugin_row_meta', 10, 2);

function trustifi_mailer_plugin_row_meta($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $links[] = '<a href="https://app.trustifi.com" target="_blank">Application Login</a>';
        $links[] = '<a href="https://trustifi.com" target="_blank">Website</a>';
    }
    return $links;
}

// Add a settings link below the plugin description on the plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'trustifi_mailer_add_settings_link');

function trustifi_mailer_add_settings_link($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=trustifi-smtp-mailer')) . '">Settings</a>';
    array_unshift($links, $settings_link);
	
	$pro_link = '<a href="https://trustifi.com/get-a-quote/" style="color: darkblue; font-weight: bold;" target="_blank" rel="noopener noreferrer">Get Trustifi Pro</a>';
    array_unshift($links, $pro_link);

    return $links;
}

// Register activation hook
register_activation_hook(__FILE__, 'trustifi_mailer_activate');

function trustifi_mailer_activate() {
    add_option('trustifi_mailer_activated', true);
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'trustifi_mailer_deactivate');

function trustifi_mailer_deactivate() {
    delete_option('trustifi_mailer_activated');
}

// Display an admin notice when the plugin is activated
add_action('admin_notices', 'trustifi_mailer_admin_notice');

function trustifi_mailer_admin_notice() {
    // Check if the plugin has been activated
    if (get_option('trustifi_mailer_activated')) {
        // Display the admin notice
        ?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>Trustifi SMTP Mailer Configuration Required:</strong></p>
			<p>Your Trustifi SMTP Mailer plugin has been activated. Before you can start using it, please complete the following steps:</p>
			<ul style="margin-left: 10px;">
				<li><strong>1) Create a Trustifi user account:</strong> Ensure that you have created and verified a Trustifi user account at <a href="https://app.trustifi.com" target="_blank" rel="noopener noreferrer">app.trustifi.com</a>.</li>
				<li><strong>2) Add and verify your domain:</strong> Add the domain you are going to send emails from in Trustifi, and verify to continue.</li>
				<li><strong>3) Obtain your SMTP credentials:</strong> At the "<strong>Outbound Management</strong>" section, then navigate to the "<strong>Plan Settings</strong>" page, under the "<strong>Email Flow Integration</strong>" tab to get your SMTP username and password.</li>
                <li><strong>4) Configure the plugin:</strong> <a href="<?php echo esc_url(admin_url('admin.php?page=trustifi-smtp-mailer')); ?>">Go to the plugin configuration page</a> to enter your SMTP credentials and finalize the setup.</li>
			</ul>
			<p>For more details, visit <a href="https://trustifi.com" target="_blank" rel="noopener noreferrer">trustifi.com</a> or contact our support at support@trustificorp.com.</p>
		</div>
        <?php
        // Remove the option after displaying the notice
        delete_option('trustifi_mailer_activated');
    }
}