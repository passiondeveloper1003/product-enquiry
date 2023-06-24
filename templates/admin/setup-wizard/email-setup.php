<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<form method="post" action="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>">
    <table class="form-table">
        <tbody>
            <!-- Recipient Email Addresses setting -->
            <tr>
                <th scope="row">
                    <label for="user_email"><?php esc_html_e('Recipient Email Addresses', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="text" id="user_email" name="wdm_form_data[user_email]" class="location-input" value="<?php echo esc_attr($recipient_email_addresses); ?>">
                    <!-- Description -->
                    <p class="description">
                        <?php esc_html_e('Add multiple email addresses separated by comma.', QUOTEUP_TEXT_DOMAIN); ?>
                        <br>
                        <?php esc_html_e('Recipients will also receive a copy of the enquiry.', QUOTEUP_TEXT_DOMAIN); ?>
                    </p>
                </td>
            </tr>

            <!-- Send Mail to Admin setting -->
            <tr>
                <th scope="row">
                    <label for="send_mail_to_admin"><?php esc_html_e('Send mail to Admin', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[send_mail_to_admin]" id="send_mail_to_admin" class="switch-input" <?php checked(1, $send_mail_to_admin); ?> >
                    <label for="send_mail_to_admin" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <!-- <span class="description"></span> -->
                </td>
            </tr>

            <!-- Send Mail to Product Author setting -->
            <tr>
                <th scope="row">
                    <label for="send_mail_to_author"><?php esc_html_e('Send mail to product author', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[send_mail_to_author]" id="send_mail_to_author" class="switch-input" <?php checked(1, $send_mail_to_author); ?> >
                    <label for="send_mail_to_author" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <!-- <span class="description"></span> -->
                </td>
            </tr>

            <!-- Default Subject setting -->
            <tr>
                <th scope="row"><label for="default_sub"><?php esc_html_e('Default Subject', QUOTEUP_TEXT_DOMAIN); ?></label></th>
                <td>
                    <input type="text" id="default_sub" name="wdm_form_data[default_sub]" class="location-input" value="<?php echo esc_attr($default_sub); ?>">
                    <p class="description"><?php esc_html_e('Default subject line for enquiry email made by the customers.', QUOTEUP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>
        </tbody>
    </table>
    <p class="wc-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_html_e('Continue', QUOTEUP_TEXT_DOMAIN); ?>" name="save_step" />
        <input type="hidden" name="wisdm_setup_step" value="<?php echo esc_attr($wizard_handler->get_current_step_slug()); ?>" />
        <a href="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', QUOTEUP_TEXT_DOMAIN); ?></a>
        <?php wp_nonce_field('quoteup_email_setup', 'quoteup_setup_nonce'); ?>
    </p>
</form>
