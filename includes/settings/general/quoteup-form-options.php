<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display form option fields
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function formOptionsSection($form_data)
{
    ?>
    <fieldset>

        <?php
        echo '<legend>'.__('Form Options', QUOTEUP_TEXT_DOMAIN).'</legend>';

        enquiryButtonLabel($form_data);
        replaceEnquiry($form_data);
        enquiryButtonLocation($form_data);
        enquiryAsLink($form_data);
        displayWisdmlabs($form_data);
        quoteupShowVariationIdSetting($form_data);
        disableNonce($form_data);
        ?>
    </fieldset>
    <?php
}

/**
 * This is used to show Enquiry button label on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function enquiryButtonLabel($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="custom_label">
                <?php _e(' Enquiry Button Label ', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for Enquiry or Quote button.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[custom_label]"
                   value="<?php echo empty($form_data[ 'custom_label' ]) ? _e('Make an Enquiry', QUOTEUP_TEXT_DOMAIN) : $form_data[ 'custom_label' ]; ?>" id="custom_label"  />
        </div>
    </div>


    <?php
}


/**
 * This is used to replace 'enquiry' words on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function replaceEnquiry($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="replace_enquiry">
                <?php _e(' Alternate word for Enquiry ', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Alternate word for Enquiry.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[replace_enquiry]" value="<?php echo empty($form_data[ 'replace_enquiry' ]) ? '' : $form_data[ 'replace_enquiry' ]; ?>" id="replace_enquiry"  />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * This is used to show Enquiry button location on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function enquiryButtonLocation($form_data)
{
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="after_add_cart">
                <?php _e(' Button Location', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            if (isset($form_data[ 'pos_radio' ])) {
                $pos = $form_data[ 'pos_radio' ];
            } else {
                $pos = 'after_add_cart';
            }
            ?>

            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" name="wdm_form_data[pos_radio]" value="after_add_cart" 
            <?php
                if ('after_add_cart' == $pos) {
                    ?> 
                checked 
                    <?php
                }
            ?>
            id="after_add_cart" />
            <label for="after_add_cart"><em><?php echo __('After Add to cart button', QUOTEUP_TEXT_DOMAIN); ?></em></label>
            <br />

            <input type="radio" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" name="wdm_form_data[pos_radio]" value="after_product_summary" 
            <?php
            if ($pos == 'after_product_summary') {
                ?>
                checked
                <?php
            }
            ?>
            id="after_product_summary" />
            <label for="after_product_summary"><em><?php echo __('After single product summary', QUOTEUP_TEXT_DOMAIN); ?></em></label>

            <!-- Help link for Enquiry Button shortcode -->
            <div class="help-doc-link enq-btn-shortcode-link">
                <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-tips-and-tricks/how-to-add-product-enquiry-pro-button-on-shop-and-single-product-page-i-am-using-page-builder/'); ?>" target="_blank"><i><?php echo esc_html(__('Learn how to: Place Enquiry button using shortcode', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
            </div>
            <!-- End of Help link for Enquiry Button shortcode -->
        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * This is used to show checkbox for show enquiry button as a link on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function enquiryAsLink($form_data)
{
    $showButtonAsLink = isset($form_data[ 'show_button_as_link' ]) ? $form_data[ 'show_button_as_link' ] : 0;
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="show_button_as_link">
                <?php _e(' Display Enquiry Button As A Link ', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" value="1" <?php checked(1, $showButtonAsLink); ?> id="show_button_as_link" />
            <input type="hidden" name="wdm_form_data[show_button_as_link]" value="<?php echo isset($form_data['show_button_as_link']) && $form_data['show_button_as_link'] == 1 ? $form_data['show_button_as_link'] : 0; ?>" />

        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * This is used to show checkbox for show footer on form.
 *
 * @param [array] $form_data [Settings stored previously in database]
 *
 * @return [type] [description]
 */
function displayWisdmlabs($form_data)
{
    $displayWisdmlabs = isset($form_data[ 'show_powered_by_link' ]) ? $form_data[ 'show_powered_by_link' ] : 0;
    //Don't show option to Display Powered by WisdmLabs if not checked till now
    if ($displayWisdmlabs != 1) {
        return;
    }
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="link">
                <?php _e(" Display 'Powered by WisdmLabs' ", QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox input-without-tip" value="1" <?php checked(1, $displayWisdmlabs); ?> id="show_powered_by_link" />
            <input type="hidden" name="wdm_form_data[show_powered_by_link]" value="<?php echo isset($form_data['show_powered_by_link']) && $form_data['show_powered_by_link'] == 1 ? $form_data['show_powered_by_link'] : 0; ?>" />

        </div>
        <div class="clear"></div>
    </div>

    <?php
}

/**
 * Displays 'Selector for Variation Id' setting in the 'General' tab.
 *
 * @since 6.3.4
 * @param [array] $form_data [Settings stored previously in database]
 */
function quoteupShowVariationIdSetting($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="variation_id_selector">
                <?php _e('Selector for Variation Id', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div help-doc-link-textbox'>
            <?php
            $helptip = __('Enter the selector for Variation Id if variable products\' attributes are not getting added in an enquiry email or in an enquiry cart. This selector is used only on the variable product page to fetch the variation Id of the variation selected by the user.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[variation_id_selector]" value="<?php echo empty($form_data[ 'variation_id_selector' ]) ? '' : $form_data[ 'variation_id_selector' ]; ?>" id="variation_id_selector"  />
        </div>
        <div class="help-doc-link">
            <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-troubleshooting/pep-is-not-showing-variation-attributes-in-the-enquiry-cart-and-in-the-enquiry-email/'); ?>" target="_blank"><i><?php echo esc_html(__('Help', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

function disableNonce($form_data)
{
    $disableNonce = isset($form_data[ 'deactivate_nonce' ]) ? $form_data[ 'deactivate_nonce' ] : 0;
    ?>

    <div class="fd">
        <div class='left_div'>
            <label for="deactivate_nonce">
                <?php _e('Disable Nonce', QUOTEUP_TEXT_DOMAIN); ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Check this option if your enquiry system does not work properly or displays an “Unauthorised Enquiry” error. Note: In all other cases, we advise you to keep it unchecked.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, $disableNonce); ?> id="deactivate_nonce" />
            <input type="hidden" name="wdm_form_data[deactivate_nonce]" value="<?php echo isset($form_data['deactivate_nonce']) && $form_data['deactivate_nonce'] == 1 ? $form_data['deactivate_nonce'] : 0; ?>" />

        </div>
        <div class="clear"></div>
    </div>
    <?php
}

formOptionsSection($form_data);
