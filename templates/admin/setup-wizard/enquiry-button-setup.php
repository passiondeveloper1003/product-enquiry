<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<form method="post" action="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>">
    <table class="form-table">
        <tbody>
            <!-- Enquiry or Quote button based on users setting -->
            <tr>
                <th scope="row">
                    <label for="show_enq_btn_to_loggedin_users"><?php esc_html_e('Show Enquiry or Quote button to', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <!-- Logged in Users -->
                    <input type="radio" id="show_enq_btn_to_loggedin_users" name="wdm_form_data[show_enq_btn_to_users]" value="loggedin_users" <?php checked('loggedin_users', $enq_btn_visibility); ?> >
                    <label for ="show_enq_btn_to_loggedin_users"><?php esc_html_e('Logged in Users', QUOTEUP_TEXT_DOMAIN); ?></label><br>
                    <!-- Guest Users -->
                    <input type="radio" id="show_enq_btn_to_guest_users" name="wdm_form_data[show_enq_btn_to_users]" value="guest_users" <?php checked('guest_users', $enq_btn_visibility); ?>>
                    <label for="show_enq_btn_to_guest_users"><?php esc_html_e('Guest Users', QUOTEUP_TEXT_DOMAIN); ?></label><br>
                    <!-- All -->
                    <input type="radio" id="show_enq_btn_to_all_users" name="wdm_form_data[show_enq_btn_to_users]" value="all" <?php checked('all', $enq_btn_visibility); ?>>
                    <label for="show_enq_btn_to_all_users"><?php esc_html_e('All', QUOTEUP_TEXT_DOMAIN); ?></label>
                    <!-- Description -->
                    <p class="description"><?php esc_html_e('Show enquiry or quote button to specific user type only.', QUOTEUP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>

            <!-- Archive page setting -->
            <tr>
                <th scope="row">
                    <label for="show_enquiry_on_shop"><?php esc_html_e('Display Enquiry or Quote button on Archive Page', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[show_enquiry_on_shop]" id="show_enquiry_on_shop" class="switch-input" <?php checked(1, $show_enquiry_on_shop); ?> >
                    <label for="show_enquiry_on_shop" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <!-- <span class="description"><?php //esc_html_e(); ?></span> -->
                </td>
            </tr>

            <!-- Out of Stock setting -->
            <tr>
                <th scope="row">
                    <label for="only_if_out_of_stock"><?php esc_html_e('Display Enquiry or Quote button only when \'Out of Stock\'', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[only_if_out_of_stock]" id="only_if_out_of_stock" class="switch-input" <?php checked(1, $only_if_out_of_stock); ?> >
                    <label for="only_if_out_of_stock" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <!-- <span class="description">Checkbox input type.</span> -->
                </td>
            </tr>
        </tbody>
    </table>
    <p class="wc-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_html_e('Continue', QUOTEUP_TEXT_DOMAIN); ?>" name="save_step" />
        <input type="hidden" name="wisdm_setup_step" value="<?php echo esc_attr($wizard_handler->get_current_step_slug()); ?>" />
        <a href="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', QUOTEUP_TEXT_DOMAIN); ?></a>
        <?php wp_nonce_field('quoteup_enquiry_button_setup', 'quoteup_setup_nonce'); ?>
    </p>
</form>
