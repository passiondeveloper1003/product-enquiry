<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display the form options section in settings
 * @param array $form_data Settings of QuoteUp
 */
function formOptionsSection2($form_data)
{
    $css_opt = '';
    if (isset($form_data[ 'enquiry_form' ])) {
        $css_opt = $form_data[ 'enquiry_form' ];
    } ?>
    <fieldset>
        <?php echo '<legend>'.__('Form Options ', QUOTEUP_TEXT_DOMAIN).'</legend>'; ?>
        <div class='fd'>
            <div class='left_div'>
                <label for="default">
                    <?php _e('Enquiry Form', QUOTEUP_TEXT_DOMAIN) ?>
                </label>
            </div>
            <div class='right_div'>
                <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" name="wdm_form_data[enquiry_form]" value="default" id="default"
                <?php
                if ('' == $css_opt || 'default' == $css_opt) {
                    ?>
                    checked
                    <?php
                }
                ?>
                />
                <label for="default"><em><?php echo __('  Default Form ', QUOTEUP_TEXT_DOMAIN); ?></em></label>
                <br>
                <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" name="wdm_form_data[enquiry_form]" value="custom"  id="custom"
                <?php
                if ('custom' == $css_opt) {
                    ?>
                    checked
                    <?php
                }
                ?>
                />
                <label for="custom"><em><?php echo __(' Custom Form', QUOTEUP_TEXT_DOMAIN); ?></em></label>
            </div>
            <div class="clear"></div>
        </div>
    </fieldset>  

    <div name="default_Settings" id="default_Settings"
    <?php
    if ($css_opt == 'custom') {
        ?>
    style="display:none"
        <?php
    }
    ?>
    >
    <?php
        displayDefaultFormSettings($form_data); ?>
    </div>

    <div name="custom_Settings" id="custom_Settings"
    <?php
    if ($css_opt == 'default' || $css_opt == '') {
        ?>
        style="display:none"
        <?php
    }
    ?>
    >
        <?php
        displayCustomFormSettings($form_data); ?>
    </div>
    <?php
    quoteupDisplayCaptchaSettings($form_data);
}

/**
* This function is to fetch the default form settings.
 * @param [array] $form_data [Settings stored previously in database]
 */

function displayDefaultFormSettings($form_data)
{
    ?>
    <fieldset >
        <?php
        echo '<legend>'.__('Default Form Settings ', QUOTEUP_TEXT_DOMAIN).'</legend>';
        sendMeCopy($form_data);
        dateField($form_data);
        dateFieldMandatory($form_data);
        dateFieldLabel($form_data);
        telephoneNumber($form_data);
        telephoneNumberMandatory($form_data);
        attachField($form_data);
        attachFieldMandatory($form_data);
        attachFieldLabel($form_data);
        enableGoogleCaptcha($form_data); ?>
    </fieldset>
    <?php
}

/**
* This function is to fetch the custom form settings.
* Fetches the posts type of form and make them in a dropdown menu.
* To provide user to chose from the available forms.
* Provide a link to add new custom form.
 * @param [array] $form_data [Settings stored previously in database]
 */
function displayCustomFormSettings($form_data)
{
    ?>
    <fieldset >
    <?php echo '<legend>'.__('Custom Form Settings ', QUOTEUP_TEXT_DOMAIN).'</legend>'; ?>
        <div class="fd">
            <div class='left_div'>
                <label for="custom-form-id"> <?php _e('Custom Form', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div'>
                <?php
                $linkNewForm=admin_url('post-new.php?post_type=form');
                $post_type = 'form';
                $selected = isset($form_data['custom_formId'])? $form_data['custom_formId'] : 0;
                $posts = get_posts(array('post_type'=> $post_type, 'post_status'=> 'publish', 'suppress_filters' => false, 'posts_per_page'=>-1));
                echo '<select class="input-without-tip" name="wdm_form_data[custom_formId]" id="custom-form-id">';
                echo '<option value = "" >'. __('Please select form', 'quoteup') .'</option>';
                foreach ($posts as $post) {
                    echo '<option value="', $post->ID, '"', $selected == $post->ID ? ' selected="selected"' : '', '>', $post->post_title, '</option>';
                }
                echo '</select>'; ?>
                <a class="new_page_link" href="<?php echo $linkNewForm; ?>"><?php _e('Add New Form', QUOTEUP_TEXT_DOMAIN); ?></a>
            </div>
            <div class='clear'></div>
        </div >
        <div class="fd">
            <div class='left_div'>
                <label for="enable_custom_send_mail_copy">
                    <?php _e(" Display 'Send me a copy' ", QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('This will display \'Send me a copy\' checkbox on Enquiry or Quote request form.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_custom_send_mail_copy' ]) ? $form_data[ 'enable_custom_send_mail_copy' ] : 0); ?> id="enable_custom_send_mail_copy" />
                <input type="hidden" name="wdm_form_data[enable_custom_send_mail_copy]" value="<?php echo isset($form_data[ 'enable_custom_send_mail_copy' ]) && $form_data[ 'enable_custom_send_mail_copy' ] == 1 ? $form_data[ 'enable_custom_send_mail_copy' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
    </fieldset>
    <?php
}

/**
 * This funcion is used to fetch the captcha related settings.
 *
 * @param array $form_data Settings stored previously in database]
 */
function quoteupDisplayCaptchaSettings($form_data)
{
    /**
     * Use the filter to enqueue the settings before Google Captcha settings.
     *
     * @since 6.5.0
     *
     * @param  array  $form_data  Quoteup settings.
     */
    do_action('quoteup_before_gcaptcha_settings', $form_data);
    ?>
    <fieldset >
        <legend>
            <?php echo __('Captcha Pre-Requisite', QUOTEUP_TEXT_DOMAIN); ?>
            <div class="help-doc-link legend-wrapper">
                <a class="help-doc-link-legend-wrapper" href="<?php echo esc_url('https://wisdmlabs.com/docs/?s=captcha+pep&ht-kb-search=1'); ?>" target="_blank"><i><?php echo esc_html(__('Help', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
            </div>
        </legend>
        <?php
        googleSiteKey($form_data);
        googleSecretKey($form_data);
        quoteupIsCaptchaVersion3($form_data);
        googleCaptchaErrorMsg($form_data);
        ?>
    </fieldset>
    <?php
}

/**
 * This is used to show checkbox for send me a copy on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function sendMeCopy($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="enable_send_mail_copy">
                <?php _e(" Display 'Send me a copy' ", QUOTEUP_TEXT_DOMAIN) ?>
            </label>

        </div>
        <div class='right_div'>
            <?php
            $helptip = __('This will display \'Send me a copy\' checkbox on Enquiry or Quote request form.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_send_mail_copy' ]) ? $form_data[ 'enable_send_mail_copy' ] : 0); ?> id="enable_send_mail_copy" />
            <input type="hidden" name="wdm_form_data[enable_send_mail_copy]" value="<?php echo isset($form_data[ 'enable_send_mail_copy' ]) && $form_data[ 'enable_send_mail_copy' ] == 1 ? $form_data[ 'enable_send_mail_copy' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * This is used to show checkbox for 'Date Field' on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function dateField($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="enable_date_field">
                <?php _e(" Display 'Date Field' ", QUOTEUP_TEXT_DOMAIN) ?>
            </label>

        </div>
        <div class='right_div'>
            <?php
            $helptip = __('This will display \'Date Field\' on Enquiry or Quote request form.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_date_field' ]) ? $form_data[ 'enable_date_field' ] : 0); ?> id="enable_date_field" />
            <input type="hidden" name="wdm_form_data[enable_date_field]" value="<?php echo isset($form_data[ 'enable_date_field' ]) && $form_data[ 'enable_date_field' ] == 1 ? $form_data[ 'enable_date_field' ] : 0 ?>" />

        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * This is used to show Date Field label on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function dateFieldLabel($form_data)
{
    ?>
    <div class="fd toggle-date">
        <div class='left_div'>
            <label for="date_field_label">
                <?php _e(' Date Field Label ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for Date Field.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[date_field_label]"
                   value="<?php echo empty($form_data[ 'date_field_label' ]) ? _e('Date', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'date_field_label' ]; ?>" id="date_field_label"  />
        </div>
        <div class="clear"></div>
    </div>


    <?php
}

/**
 * This is used to show checkbox for date field mandatory on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function dateFieldMandatory($form_data)
{
    $display = ''; ?>
    <div class="fd toggle-date" <?php echo $display; ?>>
        <div class='left_div'>
            <label for="make_date_mandatory">
                <?php _e(' Make Date Field Mandatory', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" value="1" <?php checked(1, isset($form_data[ 'make_date_mandatory' ]) ? $form_data[ 'make_date_mandatory' ] : 0); ?> id="make_date_mandatory" />
            <input type="hidden" name="wdm_form_data[make_date_mandatory]" value="<?php echo isset($form_data[ 'make_date_mandatory' ]) && $form_data[ 'make_date_mandatory' ] == 1 ? $form_data[ 'make_date_mandatory' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * This is used to show checkbox for Telephone number on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function telephoneNumber($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="enable_telephone_no_txtbox">
                <?php _e(' Display Telephone Number Field', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Display Telephone number field on Enquiry and Quote Request form.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_telephone_no_txtbox' ]) ? $form_data[ 'enable_telephone_no_txtbox' ] : 0); ?> id="enable_telephone_no_txtbox" />
            <input type="hidden" name="wdm_form_data[enable_telephone_no_txtbox]" value="<?php echo isset($form_data[ 'enable_telephone_no_txtbox' ]) && $form_data[ 'enable_telephone_no_txtbox' ] == 1 ? $form_data[ 'enable_telephone_no_txtbox' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * This is used to show checkbox for telephone number mandatory on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function telephoneNumberMandatory($form_data)
{
    $display = '';
    if (!isset($form_data[ 'enable_telephone_no_txtbox' ]) || (isset($form_data[ 'enable_telephone_no_txtbox' ]) && $form_data[ 'enable_telephone_no_txtbox' ] == 0)) {
        $display = "style='display:none'";
    } ?>
    <div class="fd toggle" <?php echo $display; ?>>
        <div class='left_div'>
            <label for="make_phone_mandatory">
                <?php _e(' Make Telephone Number Field Mandatory', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" value="1" <?php checked(1, isset($form_data[ 'make_phone_mandatory' ]) ? $form_data[ 'make_phone_mandatory' ] : 0); ?> id="make_phone_mandatory" />
            <input type="hidden" name="wdm_form_data[make_phone_mandatory]" value="<?php echo isset($form_data[ 'make_phone_mandatory' ]) && $form_data[ 'make_phone_mandatory' ] == 1 ? $form_data[ 'make_phone_mandatory' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>

    <?php
}
/**
 * This is used to show checkbox for attach field on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function attachField($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="enable_attach_field">
                <?php _e(' Display Attach Field', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Display Attach field on Enquiry and Quote Request form.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_attach_field' ]) ? $form_data[ 'enable_attach_field' ] : 0); ?> id="enable_attach_field" />
            <input type="hidden" name="wdm_form_data[enable_attach_field]" value="<?php echo isset($form_data[ 'enable_attach_field' ]) && $form_data[ 'enable_attach_field' ] == 1 ? $form_data[ 'enable_attach_field' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}
/**
 * This is used to show checkbox for attach field mandatory on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function attachFieldMandatory($form_data)
{
    $display = ''; ?>
    <div class="fd toggle-attach" <?php echo $display; ?>>
        <div class='left_div'>
            <label for="make_attach_mandatory">
                <?php _e(' Make Attach Field Mandatory', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" value="1" <?php checked(1, isset($form_data[ 'make_attach_mandatory' ]) ? $form_data[ 'make_attach_mandatory' ] : 0); ?> id="make_attach_mandatory" />
            <input type="hidden" name="wdm_form_data[make_attach_mandatory]" value="<?php echo isset($form_data[ 'make_attach_mandatory' ]) && $form_data[ 'make_attach_mandatory' ] == 1 ? $form_data[ 'make_attach_mandatory' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>

    <?php
}
/**
 * This is used to display text box for custom label for attach field on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function attachFieldLabel($form_data)
{
    ?>
    <div class="fd toggle-attach">
        <div class='left_div'>
            <label for="attach_field_label">
                <?php _e(' Attach Field Label ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for Attach Field.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[attach_field_label]"
                   value="<?php echo empty($form_data[ 'attach_field_label' ]) ? _e('Attach File', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'attach_field_label' ]; ?>" id="attach_field_label"  />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}
/**
 * This is used to show checkbox for enabling google captcha on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function enableGoogleCaptcha($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="enable_google_captcha">
                <?php _e(' Enable google captcha', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Enable google captcha on Enquiry and Quote Request form.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_google_captcha' ]) ? $form_data[ 'enable_google_captcha' ] : 0); ?> id="enable_google_captcha" />
            <input type="hidden" name="wdm_form_data[enable_google_captcha]" value="<?php echo isset($form_data[ 'enable_google_captcha' ]) && $form_data[ 'enable_google_captcha' ] == 1 ? $form_data[ 'enable_google_captcha' ] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * This is used to show input box for google site key if captcha enabled on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function googleSiteKey($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="google_site_key">
                <?php _e(' Google Site Key ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Site key after ragistration for google captcha.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[google_site_key]"
                   value="<?php echo empty($form_data[ 'google_site_key' ]) ? '' : $form_data[ 'google_site_key' ]; ?>" id="google_site_key"  />

            <a class="new_page_link" target="_blank" href="https://www.google.com/recaptcha/"> <?php _e('Create Recaptcha Key', QUOTEUP_TEXT_DOMAIN); ?> </a>
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * This is used to show input box for google secret key if captcha enabled on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function googleSecretKey($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="google_secret_key">
                <?php _e(' Google Secret Key ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Secret key after ragistration for google captcha.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[google_secret_key]"
                   value="<?php echo empty($form_data[ 'google_secret_key' ]) ? '' : $form_data[ 'google_secret_key' ]; ?>" id="google_secret_key"  />
        </div>
        <div class="clear"></div>
    </div>


    <?php
}

/**
 * This is used to show checkbox for determining google captcha version.
 *
 * @param array $form_data Settings stored previously in database
 */
function quoteupIsCaptchaVersion3($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="is_captcha_version_3">
                <?php _e('Google Captcha version 3 ?', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip        = __('Select the checkbox if the site key and secret key correspond to google captcha version 3. Otherwise, keys will be treated as Captcha version 2.', QUOTEUP_TEXT_DOMAIN);
            $description    = __('Please make sure site key and secret key belong to Captcha v3.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'is_captcha_version_3' ]) ? $form_data[ 'is_captcha_version_3' ] : 0); ?> id="is_captcha_version_3" />
            <input type="hidden" name="wdm_form_data[is_captcha_version_3]" value="<?php echo isset($form_data[ 'is_captcha_version_3' ]) && $form_data[ 'is_captcha_version_3' ] == 1 ? $form_data[ 'is_captcha_version_3' ] : 0 ?>" />
            <em><?php echo $description; ?></em>
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * This is used to show the input box for Google captcha error message
 * which is shown when captcha is not verified while making an enquiry.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function googleCaptchaErrorMsg($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="google_captcha_err_msg">
                <?php _e('Google Captcha Error Message', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Error message to show when Google captcha is not verified. Default message: \'Captcha could not be verified.\'', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true); ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[google_captcha_err_msg]"
                   value="<?php echo empty($form_data[ 'google_captcha_err_msg' ]) ? '' : $form_data[ 'google_captcha_err_msg' ]; ?>" id="google_captcha_err_msg"  />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

formOptionsSection2($form_data);
