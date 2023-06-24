<?php

namespace Includes\Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function miniCartSettingsSection($form_data)
{
    $cartPos = 'right';
    if (isset($form_data['mini_cart_position'])) {
        $cartPos = $form_data['mini_cart_position'];
    }
    ?>
    <fieldset>
        <?php
        echo '<legend>'.__('Mini Cart Settings ', QUOTEUP_TEXT_DOMAIN).'</legend>';

    ?>

        <div class="fd">
            <div class='left_div'>
                <label for="enable-disable-mini-cart"> <?php _e('Enable Mini Cart', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div help-doc-link-checkbox'>
                <?php
                $helptip = __('You can enable/disable Mini Cart.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
        ?>          
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_disable_mini_cart' ]) ? $form_data[ 'enable_disable_mini_cart' ] : 0);
        ?> id="enable-disable-mini-cart" /> 
                <input type="hidden" name="wdm_form_data[enable_disable_mini_cart]" value="<?php echo isset($form_data[ 'enable_disable_mini_cart' ]) && $form_data[ 'enable_disable_mini_cart' ] == 1 ? $form_data[ 'enable_disable_mini_cart' ] : 0 ?>" />
            </div>
            <div class="help-doc-link">
                <a href="<?php echo esc_url('https://wisdmlabs.com/docs/article/wisdm-product-enquiry-pro/pep-features/pep-mini-cart/'); ?>" target="_blank"><i><?php echo esc_html(__('Help', QUOTEUP_TEXT_DOMAIN)); ?></i></a>
            </div>
            <div class='clear'></div>
        </div>

        <div class="fd">
            <div class='left_div'>
                <label for="mini-cart-position"> <?php _e('Cart Position', QUOTEUP_TEXT_DOMAIN) ?> </label>
            </div>
            <div class='right_div'>
                <?php
                $helptip = __('This will specify mini cart position', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <select name="wdm_form_data[mini_cart_position]" id="mini-cart-position">
                    <option value="right" <?php if ($cartPos == 'right') { ?> selected <?php } ?>>Right</option>
                    <option value="left" <?php if ($cartPos == 'left') { ?> selected <?php } ?>>Left</option>
                </select>
            </div>
            <div class='clear'></div>
        </div >
    </fieldset>
    <?php
}
miniCartSettingsSection($form_data);
