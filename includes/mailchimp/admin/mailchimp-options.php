<?php
/**
 * Render MailChimp settings
 *
 * Render MailChimp settings to show the subscription checkbox on the enquiry form.
 *
 * @since 6.5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('quoteup_before_gcaptcha_settings', 'quoteupRenderMailChimpSettings');

/**
 * Render MailChimp settings/ options.
 *
 * @since 6.5.0
 *
 * @return  void
 */
function quoteupRenderMailChimpSettings($form_data)
{
    ?>
    <fieldset>
        <legend>
        <?php esc_html_e('MailChimp options', QUOTEUP_TEXT_DOMAIN); ?>
        </legend>
        <?php
        quoteupRenderMailChimpCBSetting($form_data);
        quoteupRenderMailChimpCBText($form_data);
        ?>
    </fieldset>
    <?php
}

/**
 * Render MailChimp checkbox setting.
 *
 * @since 6.5.0
 *
 * @return  void
 */
function quoteupRenderMailChimpCBSetting($form_data)
{
    ?>
    <!-- Render MailChimp subscription checkbox setting -->
    <div class="fd">
        <div class='left_div'>
            <label for="enable_mc_subs_cb">
            <?php _e('Show Subscription Checkbox', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
        <?php
        $helptip = __('Show Mailchimp subscription checkbox on the enquiry form', QUOTEUP_TEXT_DOMAIN);
        echo \quoteupHelpTip($helptip, true);
        ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data['enable_mc_subs_cb']) ? $form_data['enable_mc_subs_cb' ] : 0); ?> id="enable_mc_subs_cb" /> 
            <input type="hidden" name="wdm_form_data[enable_mc_subs_cb]" value="<?php echo isset($form_data['enable_mc_subs_cb']) && 1 == $form_data['enable_mc_subs_cb'] ? $form_data['enable_mc_subs_cb'] : 0; ?>" />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Render MailChimp checkbox label/ text setting.
 *
 * @since 6.5.0
 *
 * @return  void
 */
function quoteupRenderMailChimpCBText($form_data)
{
    if (!isset($form_data[ 'mc_subs_text' ])) {
        $form_data['mc_subs_text'] = quoteupGetDefaultMailChimpCBLabel();
    }
    ?>
    <!-- Render MailChimp subscription checkbox text -->
    <div class="fd enable_mc_subs_cb_depd">
        <div class='left_div'>
            <label for="mc_subs_text">
            <?php _e('Subscription Checkbox Label', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Text for MailChimp subscription checkbox on enquiry form', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[mc_subs_text]" value="<?php echo esc_attr($form_data['mc_subs_text']); ?>" id="mc_subs_text"  />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}
