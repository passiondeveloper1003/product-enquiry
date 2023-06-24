<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display the styling options section in settings
 * @param array $form_data QuoteUp Settings
 */
function stylingOptionsSection($form_data)
{
    $css_opt = '';
    if (isset($form_data[ 'button_CSS' ])) {
        $css_opt = $form_data[ 'button_CSS' ];
    }
    ?>
    <fieldset>
        <?php echo '<legend>'.__('Styling Options ', QUOTEUP_TEXT_DOMAIN).'</legend>';
        ?>
        <div class='fd'>
            <div class='left_div'>
                <label for="wdm-custom-css">
                    <?php _e('Add Custom CSS', QUOTEUP_TEXT_DOMAIN) ?>
                </label>
            </div>
            <div class='right_div'>
                <?php
                $user_custom_css = '';
                if (isset($form_data[ 'user_custom_css' ])) {
                    $user_custom_css = $form_data[ 'user_custom_css' ];
                }
                ?>
                <textarea name="wdm_form_data[user_custom_css]" id="wdm-custom-css" rows="10" cols="54"><?php echo $user_custom_css ?></textarea>
            </div>
        </div>
        <?php
            quoteupShowDisableBootstrapSetting($form_data);
            quoteupShowCustomStylingSetting($form_data, $css_opt);
        ?>
    </fieldset>  

    <div name="Other_Settings" id="Other_Settings"

            <?php if ($css_opt == 'theme_css' || $css_opt == '') {
    ?> style="display:none"  <?php
}
    ?>>
                <?php
                otherSettingsFieldset($form_data);
                ?>
    </div>
    <?php
    quoteupShowEnqCartIconStyling($form_data);
}

/**
 * This function is used to show 'Custom Styling' setting in 'Display' tab.
 *
 * @param [array] $form_data [Settings stored previously in database]
 * @param [string] $css_opt  ['Custom Styling' setting value]
 */
function quoteupShowCustomStylingSetting($form_data, $css_opt)
{
    ?>
    <div class='fd'>
        <div class='left_div'>
            <label for="theme-css">
                <?php _e(' Custom Styling ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox" name="wdm_form_data[button_CSS]" value="theme_css"  id="theme-css" <?php if ($css_opt == 'theme_css' || $css_opt == '') {
    ?> checked <?php
}
        ?> />
            <label for="theme-css"><em><?php echo __('Use Activated Theme CSS', QUOTEUP_TEXT_DOMAIN); ?></em></label><br>

            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox" name="wdm_form_data[button_CSS]" value="manual_css"  id="manual-css" <?php if ($css_opt == 'manual_css') {
    ?> checked <?php
}
        ?>/>

            <label for="manual-css"><em><?php echo __('Manually specify color settings', QUOTEUP_TEXT_DOMAIN); ?></em></label>
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * This function is used to show 'Disable Bootstrap' setting.
 *
 * @since 6.3.4 
 * @param [array] $form_data [Settings stored previously in database]
 */
function quoteupShowDisableBootstrapSetting($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="disable-bootstrap"> <?php _e('Disable Bootstrap on frontend', QUOTEUP_TEXT_DOMAIN) ?> </label>
        </div>
        <div class='right_div help-doc-link-checkbox'>
            <?php
            $helptip = __('You can disable bootstrap if your theme already has it.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>          
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'disable_bootstrap' ]) ? $form_data[ 'disable_bootstrap' ] : 0); ?> id="disable-bootstrap" /> 
            <input type="hidden" name="wdm_form_data[disable_bootstrap]" value="<?php echo isset($form_data[ 'disable_bootstrap' ]) && $form_data[ 'disable_bootstrap' ] == 1 ? $form_data[ 'disable_bootstrap' ] : 0 ?>" />
        </div>
        <div class="help-doc-link">
            <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-user-guide/settings-display-tab/#disable-bootstrap-on-frontend'); ?>" target="_blank"><i><?php echo esc_html(__('Help', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
 * This function is used for manually specified display settings.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function otherSettingsFieldset($form_data)
{
    ?>
    <fieldset>
        <?php echo '<legend>'.__('Specify Color Settings ', QUOTEUP_TEXT_DOMAIN).'</legend>';
    ?>
        <p style="margin-top: -0.1%;margin-bottom: 2%;"><?php _e('Button color settings will be applied to Enquiry or Quote Request button and \'Send\' button on Enquiry and Quote Request Form.', QUOTEUP_TEXT_DOMAIN) ?></p>
        <?php
        gradientStart($form_data);
        gradientEnd($form_data);
        buttonTextColor($form_data);
        buttonBorderColor($form_data);
        dialogBackgroundColor($form_data);
        productNameColor($form_data);
        dialogTextColor($form_data);
    ?>
    </fieldset>
    <?php
}

/**
 * This function is used to show Enquiry Cart Icon related settings.
 * Enquiry Cart Icon is shown only when Multi-Product Enquiry mode is enabled.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function quoteupShowEnqCartIconStyling($form_data)
{
    ?>
    <fieldset class="quote_cart">
        <?php echo '<legend>'.__('Enquiry Cart Icon Styling', QUOTEUP_TEXT_DOMAIN).'</legend>';
            quoteupShowEnqCartIconPositionSetting($form_data);
            quoteupShowSpecifyIconColorCheckbox($form_data);
            quoteupShowIconColorsSettings($form_data);
            ?>
    </fieldset>
    <?php
}

/**
 * Button background color gradient start.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function gradientStart($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="color">
                <?php _e('Button Background Color: Gradient Start', QUOTEUP_TEXT_DOMAIN); ?>

            </label>
        </div>
        <div class='right_div'>
            <input type="text" value="<?php echo empty($form_data[ 'start_color' ]) ? '#ebe9eb' : $form_data[ 'start_color' ];
    ?>" class="wdm-button-color-field" data-default-color="#ebe9eb" name="wdm_form_data[start_color]"/>   
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Button background color gradient End.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function gradientEnd($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="color">
    <?php _e('  Button Background Color: Gradient End ', QUOTEUP_TEXT_DOMAIN) ?>

            </label>
        </div>
        <div class='right_div'>

            <input type="text" value="<?php echo empty($form_data[ 'end_color' ]) ? '#ebe9eb' : $form_data[ 'end_color' ];
    ?>" class="wdm-button-color-field" data-default-color="#ebe9eb" name="wdm_form_data[end_color]"/>   

        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Button Text color.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function buttonTextColor($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="color">
    <?php _e(' Button Text Color ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="text" id="button_text_color" name="wdm_form_data[button_text_color]" value="<?php echo empty($form_data[ 'button_text_color' ]) ? '#515151' : $form_data[ 'button_text_color' ];
    ?>" data-default-color="#515151"/>

        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Button border color.
 *
 * @param [type] $form_data [description]
 */
function buttonBorderColor($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="color">
    <?php _e(' Button Border Color ', QUOTEUP_TEXT_DOMAIN) ?>

            </label>
        </div>
        <div class='right_div'>
            <input type="text" id="button_border_color" name="wdm_form_data[button_border_color]" value="<?php echo empty($form_data[ 'button_border_color' ]) ? '#ebe9eb' : $form_data[ 'button_border_color' ];
    ?>" data-default-color="#ebe9eb"/>      
        </div>
        <div class="clear"></div>
    </div>
                    <?php
}

/**
 * Dialog Background color.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function dialogBackgroundColor($form_data)
{
    ?>
    <div class="fd" >
<div class='left_div'>
<label for="color">
    <?php _e(' Dialog Background Color ', QUOTEUP_TEXT_DOMAIN) ?>

            </label>
        </div>
        <div class='right_div'>

            <input type="text" id="dialog_color" name="wdm_form_data[dialog_color]" value="<?php echo empty($form_data[ 'dialog_color' ]) ? '#ffffff' : $form_data[ 'dialog_color' ];
    ?>" data-default-color="#ffffff"/>
</div>
<div class="clear"></div>
    </div>
    <?php
}

/**
 * Product Name color.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function productNameColor($form_data)
{
    ?>
    <div class="fd">
<div class='left_div'>
<label for="color">
    <?php _e('  Product Name Color ', QUOTEUP_TEXT_DOMAIN) ?>

            </label>        
        </div>
        <div class='right_div'>

            <input type="text" id="dialog_product_color" name="wdm_form_data[dialog_product_color]" value="<?php echo empty($form_data[ 'dialog_product_color' ]) ? '#999' : $form_data[ 'dialog_product_color' ];
    ?>" data-default-color="#999" />        
<div id="product_text_picker">          
</div>
</div>
<div class="clear"></div>
    </div>
    <?php
}

/**
 * Dialog Text color.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function dialogTextColor($form_data)
{
    ?>
    <div class="fd">
<div class='left_div'>
<label for="color">
    <?php _e('  Dialog Text Color ', QUOTEUP_TEXT_DOMAIN) ?>

            </label>
        </div>
        <div class='right_div'>
            <input type="text" id="dialog_text_color" name="wdm_form_data[dialog_text_color]" value="<?php echo empty($form_data[ 'dialog_text_color' ]) ? '#333' : $form_data[ 'dialog_text_color' ];
    ?>" data-default-color="#333"/>     
<div id="dialog_text_picker">
</div>
</div>
<div class="clear"></div>
    </div>
    <?php
}

/**
 * Show the Enquiry Cart Icon Position setting.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 */
function quoteupShowEnqCartIconPositionSetting($form_data)
{
    $iconPos = '';
    if (isset($form_data['ec_icon_pos'])) {
        $iconPos = $form_data['ec_icon_pos'];
    }

    ?>
    <div class='fd'>
        <div class='left_div'>
            <label for="icon-top-right">
                <?php _e('Icon Position', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <!-- Top - Right Position Setting -->
            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox" name="wdm_form_data[ec_icon_pos]" value="icon_top_right"  id="icon-top-right" <?php if ($iconPos == 'icon_top_right' || $iconPos == '') {
    ?> checked <?php
}
    ?>/>
            <label for="icon-top-right"><em><?php echo __('Top - Right Position', QUOTEUP_TEXT_DOMAIN); ?></em></label><br>

            <!-- Top - Left Position Setting -->
            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox" name="wdm_form_data[ec_icon_pos]" value="icon_top_left" id="icon-top-left" <?php if ($iconPos == 'icon_top_left') {
    ?> checked <?php
}
    ?> />
            <label for="icon-top-left"><em><?php echo __('Top - Left Position', QUOTEUP_TEXT_DOMAIN); ?></em></label><br>

            <!-- Bottom - Left Position -->
            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox" name="wdm_form_data[ec_icon_pos]" value="icon_bottom_left"  id="icon-bottom-left" <?php if ($iconPos == 'icon_bottom_left') {
    ?> checked <?php
}
    ?>/>
            <label for="icon-bottom-left"><em><?php echo __('Bottom - Left Position', QUOTEUP_TEXT_DOMAIN); ?></em></label><br>

            <!-- Bottom - Right Position -->
            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox" name="wdm_form_data[ec_icon_pos]" value="icon_bottom_right"  id="icon-bottom-right" <?php if ($iconPos == 'icon_bottom_right') {
    ?> checked <?php
}
    ?>/>
            <label for="icon-bottom-right"><em><?php echo __('Bottom - Right Position', QUOTEUP_TEXT_DOMAIN); ?></em></label><br>    

        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Render 'Specify Icon Color' checkbox.
 *
 * @param [array] $form_data [Settings stored previously in database] 
 */
function quoteupShowSpecifyIconColorCheckbox($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="enable-manual-ec-icon-color"> <?php _e('Specify Icon Color', QUOTEUP_TEXT_DOMAIN); ?> </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Enable this setting if you want to change the default color for Enquiry Cart Icon.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
    ?>          
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_manual_ec_icon_color' ]) ? $form_data[ 'enable_manual_ec_icon_color' ] : 0);
    ?> id="enable-manual-ec-icon-color" /> 
            <input type="hidden" name="wdm_form_data[enable_manual_ec_icon_color]" value="<?php echo isset($form_data[ 'enable_manual_ec_icon_color' ]) && $form_data[ 'enable_manual_ec_icon_color' ] == 1 ? $form_data[ 'enable_manual_ec_icon_color' ] : 0 ?>" />
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
 * Show various color settings to manually specify the color for Enquiry Cart Icon.
 *
 * @param [array] $form_data [Settings stored previously in database] 
 *
 */
function quoteupShowIconColorsSettings($form_data)
{
    // Render Icon Background-color setting.
    quoteupRenderColorSetting($form_data, 'ec_icon_bg_color', '#6D6D6D', __('Icon Background Color', QUOTEUP_TEXT_DOMAIN), 'manual-ec-icon-color-depd');

    // Render Icon Border color Setting.
    quoteupRenderColorSetting($form_data, 'ec_icon_border_color', '#6D6D6D', __('Icon Border Color', QUOTEUP_TEXT_DOMAIN), 'manual-ec-icon-color-depd');

    // Render Icon color Setting.
    quoteupRenderColorSetting($form_data, 'ec_icon_color', '#fff', __('Icon Color', QUOTEUP_TEXT_DOMAIN), 'manual-ec-icon-color-depd');

    // Render total number of products background color Setting.
    quoteupRenderColorSetting($form_data, 'ec_icon_number_bg_color', '#fff', __('Icon Product Count Background Color', QUOTEUP_TEXT_DOMAIN), 'manual-ec-icon-color-depd');

    // Render total number of products border color Setting.
    quoteupRenderColorSetting($form_data, 'ec_icon_number_border_color', '#fff', __('Icon Product Count Border Color', QUOTEUP_TEXT_DOMAIN), 'manual-ec-icon-color-depd');

    // Render total number of products color Setting.
    quoteupRenderColorSetting($form_data, 'ec_icon_number_color', '#6D6D6D', __('Icon Product Count Color', QUOTEUP_TEXT_DOMAIN), 'manual-ec-icon-color-depd');
}

/**
 * Render Color setting.
 *
 * @param array $form_data Settings stored previously in database.
 * @param string $key String containing the key by which particular color
 *                    setting will be saved in the database.
 * @param string $default Default color value.
 * @param string $labelFor String containing the setting label.
 * @param string $classForSettingField Class for setting field.
 */
function quoteupRenderColorSetting($form_data, $key, $default, $settingLabel, $classForSettingField)
{
    ?>
    <div class="fd <?php echo $classForSettingField; ?>">
        <div class='left_div'>
            <label for="<?php echo $key; ?>">
                <?php echo $settingLabel; ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="text" value="<?php echo empty($form_data[$key]) ? $default : $form_data[$key];
    ?>" class="wdm-button-color-field" data-default-color="<?php echo $default; ?>" name="wdm_form_data[<?php echo $key; ?>]"/>   
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

stylingOptionsSection($form_data);
