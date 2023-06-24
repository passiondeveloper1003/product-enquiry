<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function is used to display 'Other Options' fields
 * @param   array   $form_data  Settings stored previously in database.
 */
function quoteupOtherOptionsSection($form_data)
{
    ?>
    <fieldset>

        <?php
        echo '<legend>'.__('Other Options', QUOTEUP_TEXT_DOMAIN).'</legend>';

        enableQuantityField($form_data);
        hidePriceOnAllProductsSetting($form_data);
        hideAddToCartOnAllProductsSetting($form_data);
        ?>
    </fieldset>
    <?php
}

/**
 * This is used to show quantity field setting.
 *
 * @param   array   $form_data  Settings stored previously in database.
 */
function enableQuantityField($form_data)
{
    ?>
    <div class="fd">
        <div class='left_div'>
            <label for="enable_qty_field">
                <?php _e('Enable Quantity Field ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helptip = __('Enable quantity field on single product page when \'Add to Cart\' button is disabled for a particular product.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helptip, true);
            ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data['enable_qty_field']) ? $form_data['enable_qty_field'] : 0); ?> id="enable_qty_field" /> 
            <input type="hidden" name="wdm_form_data[enable_qty_field]" value="<?php echo isset($form_data['enable_qty_field']) && $form_data['enable_qty_field'] == 1 ? $form_data['enable_qty_field'] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Render "Hide 'Prices' on All Products" setting.
 *
 * @since 6.5.0
 *
 * @param   array   $formData  Settings stored previously in database.
 */
function hidePriceOnAllProductsSetting($form_data)
{
    ?>
    <div class="fd hide_price_on_all_products_wrapper">
        <div class='left_div'>
            <label for="hide_price_on_all_products">
                <?php _e('Hide \'Price\' on All Products', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helpTip = __('Check/ select the setting if you want to disable/ hide the \'Price\' on all products.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helpTip, true);
            ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data['hide_price_on_all_products']) ? $form_data['hide_price_on_all_products'] : 0); ?> id="hide_price_on_all_products" /> 
            <input type="hidden" name="wdm_form_data[hide_price_on_all_products]" value="<?php echo isset($form_data['hide_price_on_all_products']) && $form_data['hide_price_on_all_products'] == 1 ? $form_data['hide_price_on_all_products'] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Render "Hide 'Add to Cart' on All Products" setting.
 *
 * @since 6.5.0
 *
 * @param   array   $formData  Settings stored previously in database.
 */
function hideAddToCartOnAllProductsSetting($form_data)
{
    ?>
    <!-- This setting is depended on 'Hide Price on All Products' setting-->
    <div class="fd hide_add_to_cart_on_all_products_wrapper">
        <div class='left_div'>
            <label for="hide_add_to_cart_on_all_products">
                <?php _e('Hide \'Add to Cart\' on All Products ', QUOTEUP_TEXT_DOMAIN) ?>
            </label>
        </div>
        <div class='right_div'>
            <?php
            $helpTip = __('Check/ select the setting if you want to disable/ hide the \'Add to Cart\' button on all products.', QUOTEUP_TEXT_DOMAIN);
            echo \quoteupHelpTip($helpTip, true);
            ?>
            <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data['hide_add_to_cart_on_all_products']) ? $form_data['hide_add_to_cart_on_all_products'] : 0); ?> id="hide_add_to_cart_on_all_products" /> 
            <input type="hidden" name="wdm_form_data[hide_add_to_cart_on_all_products]" value="<?php echo isset($form_data['hide_add_to_cart_on_all_products']) && $form_data['hide_add_to_cart_on_all_products'] == 1 ? $form_data['hide_add_to_cart_on_all_products'] : 0 ?>" />
        </div>
        <div class="clear"></div>
    </div>
    <?php
}

quoteupOtherOptionsSection($form_data);
