<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Render the settings page content
function trustifi_mailer_options_page() {
    ?>
    <div class="wrap">
        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/images/logo.png'); ?>" alt="Trustifi Logo" style="max-width: 100%; height: 50px;" />
        <hr>
        <h1>Trustifi SMTP Mailer Settings</h1>
		<p>
			<strong>Note:</strong> To obtain your Trustifi SMTP credentials, please log into your Trustifi account at <a href="https://app.trustifi.com" target="_blank" rel="noopener noreferrer"><strong>app.trustifi.com</strong></a>.
		</p>
		<p>
			After logging in, add and verify your domain, then navigate to the <strong>Outbound Management</strong> section. From there, go to the <strong>Plan Settings</strong> page under the <strong>Email Flow Integration</strong> tab to find your SMTP credentials.
		</p>
        <?php
        // Display status messages
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="updated"><p>Settings saved successfully!</p></div>';
        }
        if (isset($_GET['test_email_sent'])) {
            if ($_GET['test_email_sent'] === 'true') {
                echo '<div class="updated"><p>Test email sent successfully!</p></div>';
            } elseif ($_GET['test_email_sent'] === 'false') {
                echo '<div class="error"><p>Failed to send test email. Please check your settings.</p></div>';
            }
        }
        if (isset($_GET['error'])) {
            $error_message = sanitize_text_field(wp_unslash($_GET['error']));
            echo '<div class="error"><p>Error: ' . esc_html($error_message) . '</p></div>';
        }
        ?>
        <!-- Settings Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php
            // Add nonce for security
            wp_nonce_field('trustifi_mailer_save_settings', 'trustifi_mailer_nonce');
            ?>
            <input type="hidden" name="action" value="save_settings" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Trustifi Username</th>
                    <td><input type="text" name="trustifi_mailer_smtp_username" placeholder="Enter username" style="width: 300px;" value="<?php echo esc_attr(get_option('trustifi_mailer_smtp_username')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Trustifi Password</th>
                    <td><input type="password" name="trustifi_mailer_smtp_password" placeholder="Enter password" style="width: 300px;" value="<?php echo esc_attr(get_option('trustifi_mailer_smtp_password')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Display Name</th>
                    <td><input type="text" name="trustifi_mailer_from_name" placeholder="Enter display name" style="width: 300px;" value="<?php echo esc_attr(get_option('trustifi_mailer_from_name')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Email Address</th>
                    <td><input type="email" name="trustifi_mailer_from_email" placeholder="Enter email address" style="width: 300px;" value="<?php echo esc_attr(get_option('trustifi_mailer_from_email')); ?>" /></td>
                </tr>
				<tr valign="top" style="display: none; visibility: hidden;">
					<th scope="row">Additional Encryption</th>
					<td>
                        <input type="checkbox" name="trustifi_mailer_enable_encryption" value="1" <?php checked(get_option('trustifi_mailer_enable_encryption'), 1); ?> />
                        <label for="trustifi_mailer_enable_encryption">Enable additional encryption on email content.</label>
					</td>
				</tr>
                <tr valign="top">
                    <td colspan="2" style="padding: 0; padding-top: 10px;">
                        <input type="submit" name="submit_action" value="Save Settings" class="button button-primary" />
                        <?php if (get_option('trustifi_mailer_smtp_username') && get_option('trustifi_mailer_smtp_password') && get_option('trustifi_mailer_from_name') && get_option('trustifi_mailer_from_email')): ?>
                            <!-- Test Email Button -->
                            <?php wp_nonce_field('trustifi_mailer_send_test_email', 'trustifi_mailer_test_email_nonce'); ?>
                            <input type="submit" name="submit_action" value="Send Test Email" class="button button-secondary" style="margin-left: 10px;" />
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

// Register settings and fields
add_action('admin_init', 'trustifi_mailer_settings_init');

function trustifi_mailer_settings_init() {
    register_setting('trustifi_mailer_options_group', 'trustifi_mailer_smtp_username', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));

    register_setting('trustifi_mailer_options_group', 'trustifi_mailer_smtp_password', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));

    register_setting('trustifi_mailer_options_group', 'trustifi_mailer_from_email', array(
        'sanitize_callback' => 'sanitize_email'
    ));

    register_setting('trustifi_mailer_options_group', 'trustifi_mailer_from_name', array(
        'sanitize_callback' => 'sanitize_text_field'
    ));

    register_setting('trustifi_mailer_options_group', 'trustifi_mailer_enable_encryption', array(
        'sanitize_callback' => 'trustifi_mailer_sanitize_checkbox'
    ));
}

function trustifi_mailer_sanitize_checkbox($input) {
    return $input === '1' ? '1' : '0';
}

add_action('admin_post_save_settings', 'trustifi_mailer_handle_form');

function trustifi_mailer_handle_form() {
    // Check nonce for security
    if (isset($_POST['trustifi_mailer_nonce'])) {

        $nonce = sanitize_text_field(wp_unslash($_POST['trustifi_mailer_nonce']));

        if (wp_verify_nonce($nonce, 'trustifi_mailer_save_settings')) {
            // Use isset() to check if the $_POST variables exist before using them
            $username = isset($_POST['trustifi_mailer_smtp_username']) ? sanitize_text_field(wp_unslash($_POST['trustifi_mailer_smtp_username'])) : '';
            $password = isset($_POST['trustifi_mailer_smtp_password']) ? sanitize_text_field(wp_unslash($_POST['trustifi_mailer_smtp_password'])) : '';
            $from_email = isset($_POST['trustifi_mailer_from_email']) ? sanitize_email(wp_unslash($_POST['trustifi_mailer_from_email'])) : '';
            $from_name = isset($_POST['trustifi_mailer_from_name']) ? sanitize_text_field(wp_unslash($_POST['trustifi_mailer_from_name'])) : '';
            $enable_encryption = isset($_POST['trustifi_mailer_enable_encryption']) ? '1' : '0';

            // Save settings
            update_option('trustifi_mailer_smtp_username', $username);
            update_option('trustifi_mailer_smtp_password', $password);
            update_option('trustifi_mailer_from_email', $from_email);
            update_option('trustifi_mailer_from_name', $from_name);
            update_option('trustifi_mailer_enable_encryption', $enable_encryption);

            if (isset($_POST['submit_action']) && $_POST['submit_action'] === 'Save Settings') {
                // Redirect with a success message
                $redirect_url = admin_url('admin.php?page=trustifi-smtp-mailer&settings-updated=true');
                wp_redirect($redirect_url);
                exit;
            } elseif (isset($_POST['submit_action']) && $_POST['submit_action'] === 'Send Test Email') {
                // Check nonce for test email
                if (isset($_POST['trustifi_mailer_test_email_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['trustifi_mailer_test_email_nonce'])), 'trustifi_mailer_send_test_email')) {
                    trustifi_send_test_email();
                } else {
                    // Redirect with a success message
                    $redirect_url = admin_url('admin.php?page=trustifi-smtp-mailer&settings-updated=Security%20check%20failed.');
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        } else {
            // Handle nonce verification failure
            $redirect_url = admin_url('admin.php?page=trustifi-smtp-mailer&error=Invalid%20nonce.');
            wp_redirect($redirect_url);
            exit;
        }
    }
}

function trustifi_send_test_email() {
    // Retrieve saved settings
    $from_email = get_option('trustifi_mailer_from_email');
    $from_name = get_option('trustifi_mailer_from_name');

    if (empty($from_email)) {
        $redirect_url = admin_url('admin.php?page=trustifi-smtp-mailer&error=Email%20is%20empty.');
        wp_redirect($redirect_url);
        exit;
    }

    if (empty($from_name)) {
        $redirect_url = admin_url('admin.php?page=trustifi-smtp-mailer&error=Name%20is%20empty.');
        wp_redirect($redirect_url);
        exit;
    }

    $subject = 'Trustifi SMTP Mailer Test From WordPress';
    $message = 'This is a test email from your WordPress to verify that the Trustifi SMTP Mailer plugin is configured correctly.';
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . esc_html($from_name) . ' <' . esc_attr($from_email) . '>'
    );

    $sent = wp_mail($from_email, $subject, $message, $headers);

    // Redirect with a success or failure message
    $redirect_url = admin_url('admin.php?page=trustifi-smtp-mailer&test_email_sent=' . ($sent ? 'true' : 'false'));
    wp_redirect($redirect_url);
    exit;
}