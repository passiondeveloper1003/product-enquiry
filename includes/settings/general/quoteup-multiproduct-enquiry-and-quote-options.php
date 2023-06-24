<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display Enable Disable Multiproduct Quote Section.
 * @param array $form_data Quoteup settings
 */
function enableDisableMultiproductQuoteSection($form_data)
{
    ?>
    <fieldset>
        <?php
        echo '<legend>'.__('Multiproduct Enquiry & Quote Options', QUOTEUP_TEXT_DOMAIN).'</legend>';
        enableMultiproductQuote($form_data);
        quoteupCartPage($form_data);
        quoteupViewCartButtonLabel($form_data);
        quoteupReturnToShopButtonLabelSetting($form_data);
        quoteupReturnToShopButtonURLSetting($form_data);
        ?>
    </fieldset>
    <?php
}

/**
 * This function is used to display Enable Multiproduct Quote Checkbox
 * @param array $form_data Quoteup settings
 */
function enableMultiproductQuote($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="enable_disable_mpe"> <?php _e('Enable Multiproduct Enquiry and Quote Request', QUOTEUP_TEXT_DOMAIN) ?> </label>
        </div>
        <div class='right_div help-doc-link-checkbox'>
            <?php
            $helptip = __('You can enable/disable multiproduct enquiry or quote. At a time single or multiproduct enquiry or quote will be available.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>          
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_disable_mpe' ]) ? $form_data[ 'enable_disable_mpe' ] : 0); ?> id="enable_disable_mpe" /> 
            <input type="hidden" name="wdm_form_data[enable_disable_mpe]" value="<?php echo isset($form_data[ 'enable_disable_mpe' ]) && $form_data[ 'enable_disable_mpe' ] == 1 ? $form_data[ 'enable_disable_mpe' ] : 0 ?>" />
        </div>
        <div class="help-doc-link">
            <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-features/single-product-enquiry-spe-mode-and-multi-product-enquiry-mpe-mode/'); ?>" target="_blank"><i><?php echo esc_html(__('Help', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
 * This function is used to display Cart Page dropdown
  * @param array $form_data Quoteup settings
 */
function quoteupCartPage($form_data)
{
    if (!isset($form_data[ 'mpe_cart_page' ]) || empty($form_data[ 'mpe_cart_page' ])) {
        $mpe_cart_page_id = '';
    } else {
        $mpe_cart_page_id = $form_data[ 'mpe_cart_page' ];
    }
    ?>
    <div class="fd quote_cart">
        <div class='left_div'>
            <label for="mpe_cart_page"> <?php _e('Enquiry and Quote Cart Page', QUOTEUP_TEXT_DOMAIN) ?> </label>
        </div>
        <div class='right_div'>
            <?php
            $cart_page = get_option('woocommerce_cart_page_id');
            $checkout_page = get_option('woocommerce_checkout_page_id');

            $exclude_tree = wdmCheckExcludeTree($cart_page, $checkout_page);
            $helptip = __('Select Enquiry & Quote cart page. This is a page where Enquiry & Quote Cart is shown.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <?php wp_dropdown_pages(array('name' => 'wdm_form_data[mpe_cart_page]', 'id' => 'mpe_cart_page' ,'selected' => $mpe_cart_page_id, 'show_option_none' => __('Select Page', QUOTEUP_TEXT_DOMAIN), 'exclude_tree' => "$exclude_tree"));
            $admin_path = get_admin_url();
            ?>
            <a class="new_page_link" href="<?php echo $admin_path.'post-new.php?post_type=page'; ?>"> <?php _e('Add New Page', QUOTEUP_TEXT_DOMAIN); ?> </a>
        </div>
        <div class='clear'></div>
    </div >
    <?php
}

/**
 * This is used to show cart button label on settings page.
 *
 * @param [array] $form_data [Settings stored previously in database]
 */
function quoteupViewCartButtonLabel($form_data)
{
    if (!isset($form_data[ 'cart_custom_label' ]) || empty($form_data[ 'cart_custom_label' ])) {
        if (isset($form_data[ 'enable_disable_quote' ]) && $form_data[ 'enable_disable_quote' ] == 0) {
            $form_data[ 'cart_custom_label' ] = __('View Enquiry & Quote Cart', QUOTEUP_TEXT_DOMAIN);
        } else {
            $form_data[ 'cart_custom_label' ] = __('View Enquiry Cart', QUOTEUP_TEXT_DOMAIN);
        }
    }
    ?>

    <div class="fd quote_cart">
        <div class='left_div'>
            <label for="cart_custom_label">
                <?php _e(' View Cart Button Label ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add custom label for Enquiry or Quote cart button.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[cart_custom_label]"
                   value="<?php echo $form_data[ 'cart_custom_label' ]; ?>" id="cart_custom_label"  />
        </div>
        <div class='clear'></div>
    </div>

    <?php
}

/**
 * Display the setting to change the label for the 'Return to Shop'
 * button shown on the enquiry cart page after enquiry form submission.
 *
 * @since 6.5.0
 *
 * @param  array  $form_data  Settings stored previously in database.
 */
function quoteupReturnToShopButtonLabelSetting($formData)
{
    ?>
    <div class="fd quote_cart">
        <div class='left_div'>
            <label for="ret_to_shop_btn_label">
                <?php _e('Return to Shop Button Label', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add label for Return to Shop button shown on the enquiry cart page after enquiry form submission.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="text" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[ret_to_shop_btn_label]" value="<?php esc_attr_e(quoteupReturnToShopBtnLabel($formData)); ?>" id="ret_to_shop_btn_label" />
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
 * Display the setting to change the URL for the 'Return to Shop'
 * button shown on the enquiry cart page after enquiry form submission.
 *
 * @since 6.5.0
 *
 * @param  array  $form_data  Settings stored previously in database.
 */
function quoteupReturnToShopButtonURLSetting($formData)
{
    ?>
    <div class="fd quote_cart">
        <div class='left_div'>
            <label for="ret_to_shop_btn_url"><?php _e('Return to Shop Button Url', QUOTEUP_TEXT_DOMAIN) ?></label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Add URL for Return to Shop button shown on the enquiry cart page after enquiry form submission', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="url" class="wdm_wpi_input wdm_wpi_text" name="wdm_form_data[ret_to_shop_btn_url]" id="ret_to_shop_btn_url" placeholder=<?php echo esc_url(quoteupReturnToShopBtnDefaultURL()); ?> value="<?php echo esc_url(quoteupReturnToShopBtnURL($formData)); ?>" />
        </div>
        <div class='clear'></div>
    </div>
    <?php
}

/**
* Returns the checkout page or cart page or both based on condition.
* @param int $cart_page  woocommerce Cart page id
* @param int $checkout_page  woocommerce checkout page id
* @return string checkout page or cart page.
*/
function wdmCheckExcludeTree($cart_page, $checkout_page)
{
    if (empty($checkout_page) && empty($cart_page)) {
        return "";
    } elseif (!empty($checkout_page) && !empty($cart_page)) {
        return "$checkout_page,$cart_page";
    } else {
        $return = !empty($checkout_page) ? $checkout_page : $cart_page;
        return "$return";
    }
}

enableDisableMultiproductQuoteSection($form_data);
