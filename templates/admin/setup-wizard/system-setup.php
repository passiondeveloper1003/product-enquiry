<?php

if (!defined('ABSPATH')) {
    exit;
}

$enable_mpe_depd_class    = true == $enable_mpe ? '' : 'hide';
$enable_quote_depd_class  = true == $enable_quote ? '' : 'hide';
$comp_details_field_class = $enable_quote && $enable_pdf ? '' : 'hide'; 
?>

<form method="post" action="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>">
    <table class="form-table">
        <tbody>
            <!-- MPE setting -->
            <tr>
                <th scope="row">
                    <label for="enable_disable_mpe"><?php esc_html_e('Enable Multiproduct Enquiry and Quote Request', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[enable_disable_mpe]" id="enable_disable_mpe" class="switch-input" <?php checked(1, $enable_mpe); ?> >
                    <label for="enable_disable_mpe" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <span class="description"><?php esc_html_e('Allow your customers to enquire about multiple products in a single go.', QUOTEUP_TEXT_DOMAIN); ?></span>
                </td>
            </tr>

            <!-- Auto Generate Enquiry Cart Page setting -->
            <tr class="quoteup-enable-disable-mpe-depd <?php echo esc_attr($enable_mpe_depd_class);?>">
                <th scope="row">
                    <label for="gen_enq_cart_page"><?php esc_html_e('Auto Generate Enquiry Cart Page', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="gen_enq_cart_page" id="gen_enq_cart_page" class="switch-input" checked>
                    <label for="gen_enq_cart_page" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <span class="description"><?php esc_html_e('Enabling this will add enquiry cart page.', QUOTEUP_TEXT_DOMAIN) ?></span>
                </td>
            </tr>

            <!-- Enquiry and Quote Cart Page setting -->
            <tr class="quoteup-enable-disable-mpe-depd quoteup-gen-enq-cart-page-depd hide">
                <th scope="row">
                    <label for="mpe_cart_page">
                        <?php esc_html_e('Enquiry and Quote Cart Page', QUOTEUP_TEXT_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <?php
                    wp_dropdown_pages(
                        array(
                            'name'              => 'wdm_form_data[mpe_cart_page]',
                            'id'                => 'mpe_cart_page',
                            'show_option_none'  => __('&mdash; Select &mdash;'),
                            'selected'          => $mpe_cart_page_id,
                            'exclude_tree'      => $exclude_tree
                        )
                    );
                    ?>
                    <!-- <span class="description"></span> -->
                </td>
            </tr>

            <!-- Enable Quotation System setting -->
            <tr>
                <th scope="row">
                    <label for="enable_disable_quote"><?php esc_html_e('Enable Quotation System', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[enable_disable_quote]" id="enable_disable_quote" class="switch-input" <?php checked(1, $enable_quote); ?> >
                    <label for="enable_disable_quote" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <span class="description"><?php esc_html_e('Enable quotation module to generate and send quote to customers.', QUOTEUP_TEXT_DOMAIN); ?></span>
                </td>
            </tr>

            <!-- Enable PDF setting -->
            <tr class="quoteup-enable-disable-quote-depd <?php echo esc_attr($enable_quote_depd_class); ?>">
                <th scope="row">
                    <label for="enable_disable_quote_pdf"><?php esc_html_e('Enable PDF', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wdm_form_data[enable_disable_quote_pdf]" id="enable_disable_quote_pdf" class="switch-input" <?php checked(1, $enable_pdf); ?> >
                    <label for="enable_disable_quote_pdf" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <span class="description"><?php esc_html_e('Send quotation in the PDF format via email.', QUOTEUP_TEXT_DOMAIN); ?></span>
                </td>
            </tr>

            <!-- Company Name setting -->
            <tr class="quoteup-enable-disable-quote-depd quoteup-enable-pdf-depd <?php echo esc_attr($comp_details_field_class); ?>">
                <th scope="row">
                    <label for="company_name"><?php esc_html_e('Company Name', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="text" id="company_name" name="wdm_form_data[company_name]" class="location-input" value="<?php echo esc_attr($company_name); ?>">
                    <!-- Description -->
                    <p class="description"><?php esc_html_e('The company name would be added in the quotation PDF.', QUOTEUP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>

            <!-- Company Email setting -->
            <tr class="quoteup-enable-disable-quote-depd quoteup-enable-pdf-depd <?php echo esc_attr($comp_details_field_class); ?>">
                <th scope="row">
                    <label for="company_email"><?php esc_html_e('Company Email', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="text" id="company_email" name="wdm_form_data[company_email]" class="location-input" value="<?php echo esc_attr($company_email); ?>">
                    <!-- Description -->
                    <p class="description"><?php esc_html_e('The company email would be added in the quotation PDF.', QUOTEUP_TEXT_DOMAIN); ?></p>
                </td>
            </tr>

            <!-- Auto Generate Quote Approval/ Rejection Page setting -->
            <tr class="quoteup-enable-disable-quote-depd <?php echo esc_attr($enable_quote_depd_class); ?>">
                <th scope="row">
                    <label for="gen_quote_app_rej_page"><?php esc_html_e('Auto Generate Quote Approval/ Rejection Page', QUOTEUP_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="gen_quote_app_rej_page" id="gen_quote_app_rej_page" class="switch-input" checked >
                    <label for="gen_quote_app_rej_page" class="switch-label">
                        <span class="toggle--on">On</span>
                        <span class="toggle--off">Off</span>
                    </label>
                    <span class="description"><?php esc_html_e('Enabling this will add quote approval/ rejection page.', QUOTEUP_TEXT_DOMAIN); ?></span>
                </td>
            </tr>

            <!-- Quote Approval/ Rejection Page setting -->
            <tr class="quoteup-enable-disable-quote-depd quoteup-gen-quote-app-rej-page-depd hide">
                <th scope="row">
                    <label for="approval_rejection_page">
                        <?php esc_html_e('Quote Approval/ Rejection Page', QUOTEUP_TEXT_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <?php
                    wp_dropdown_pages(
                        array(
                            'name'              => 'wdm_form_data[approval_rejection_page]',
                            'id'                => 'approval_rejection_page',
                            'show_option_none'  => __('&mdash; Select &mdash;'),
                            'selected'          => $quote_app_rej_page_id,
                        )
                    );
                    ?>
                    <!-- <span class="description"></span> -->
                </td>
            </tr>
        </tbody>
    </table>
    <p class="wc-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_html_e('Continue', QUOTEUP_TEXT_DOMAIN); ?>" name="save_step">
        <input type="hidden" name="wisdm_setup_step" value="<?php echo esc_attr($wizard_handler->get_current_step_slug()); ?>" />
        <a href="<?php echo esc_url($wizard_handler->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'wdm_ld_group'); ?></a>
        <?php wp_nonce_field('quoteup_system_setup', 'quoteup_setup_nonce'); ?>
    </p>
</form>
