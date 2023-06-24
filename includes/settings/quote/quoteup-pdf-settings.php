<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * It displays PDF settings on general settings page. It shows following fields
 * - Company Name
 * - Company Email
 * - Company Address
 * - Logo for PDF.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function pdfSettingsSection($form_data)
{
    if (!isset($form_data[ 'company_name' ])) {
        $form_data[ 'company_name' ] = get_bloginfo();
    }

    if (!isset($form_data[ 'company_logo' ])) {
        $form_data[ 'company_logo' ] = '';
    }

    if (!isset($form_data[ 'company_address' ])) {
        $form_data[ 'company_address' ] = '';
    }

    if (!isset($form_data[ 'company_email' ])) {
        $form_data[ 'company_email' ] = get_option('admin_email');
    }
    ?>
    <fieldset>
        <?php
         $helptip = __('These settings will add details to the generated quote when the Quotation System is enabled.', QUOTEUP_TEXT_DOMAIN);
        $tip = \quoteupHelpTip($helptip, true);
        echo '<legend>'.__('PDF Settings ', QUOTEUP_TEXT_DOMAIN).$tip.'</legend>';
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="enable-disable-pdf"> <?php _e('Enable PDF', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div help-doc-link-checkbox'>
                <?php
                $helptip = __('You can enable/disable PDF.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>          
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_disable_quote_pdf' ]) ? $form_data[ 'enable_disable_quote_pdf' ] : 0);
                ?> id="enable-disable-pdf" /> 
                <input type="hidden" name="wdm_form_data[enable_disable_quote_pdf]" value="<?php echo isset($form_data[ 'enable_disable_quote_pdf' ]) && $form_data[ 'enable_disable_quote_pdf' ] == 1 ? $form_data[ 'enable_disable_quote_pdf' ] : 0 ?>" />
            </div>
            <div class="help-doc-link">
                <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-tips-and-tricks/can-i-create-a-new-design-for-the-quote-pdf-that-is-being-generated/'); ?>" target="_blank"><i><?php echo esc_html(__('Help', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
            </div>
            <div class='clear'></div>
        </div>

        <div class="fd toggle-pdf">
            <div class='left_div'>
                <label for="wdm-company-name"> <?php esc_html_e('Company Name', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div'>
                <input type="text" class="wdm_wpi_text input-without-tip" name="wdm_form_data[company_name]" id="wdm-company-name" value="<?php echo $form_data[ 'company_name' ]; ?>">
            </div>
            <div class='clear'></div>
        </div >

        <div class="fd toggle-pdf">
            <div class='left_div'>
                <label for="wdm-company-email"> <?php esc_html_e('Company Email', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div'>
                <input class="wdm_wpi_text input-without-tip" type="text" name="wdm_form_data[company_email]" id="wdm-company-email" value="<?php echo $form_data[ 'company_email' ] ?>">
            </div>
            <div class='clear'></div>
        </div >

        <div class="fd toggle-pdf">
            <div class='left_div'>
                <label for="wdm-company-add"> <?php esc_html_e('Company Address', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div'>
                <textarea class="wdm_wpi_text input-without-tip" name="wdm_form_data[company_address]" id="wdm-company-add" rows="5"><?php echo $form_data[ 'company_address' ] ?></textarea>
            </div>
            <div class='clear'></div>
        </div >

        <div class="fd toggle-pdf">
            <div class='left_div'>
                <label for="quoteup-pdf-email-footer-text"><?php esc_html_e('Quote PDF and Email footer text', QUOTEUP_TEXT_DOMAIN) ?></label>
            </div>
            <div class='right_div'>
                <textarea class="wdm_wpi_text input-without-tip" name="wdm_form_data[quote_pdf_email_footer_text]" id="quoteup-pdf-email-footer-text" rows="5"><?php echo empty($form_data['quote_pdf_email_footer_text']) ? '' : esc_textarea($form_data['quote_pdf_email_footer_text']); ?></textarea>
            </div>
            <div class='clear'></div>
        </div >
    </fieldset>
    <?php
}

pdfSettingsSection($form_data);
