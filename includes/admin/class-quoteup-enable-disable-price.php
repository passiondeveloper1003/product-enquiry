<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('QuoteUpEnableDisablePrice')) {
/**
 * Enable Price for products.
 * Add enable price for individual product in backend.
*/
    class QuoteUpEnableDisablePrice
    {
         /**
        * Action to enable Price
        * Action to add enable price for individual product in backend.
        */
        public function __construct()
        {
            add_action('woocommerce_process_product_meta', array($this, 'woocommerceSaveMetaFields'), 10, 1);
            add_action('post_submitbox_misc_actions', array($this, 'priceForIndividualProduct'));
        }

        /*
         * Process _enable_add_to_cart attribute of individual post and save it in database
         * @param int $post_id Post id 
         */
        public function woocommerceSaveMetaFields($post_id)
        {
            if (!isset($_POST[ '_enable_price' ]) || empty($_POST[ '_enable_price' ])) {
                update_post_meta($post_id, '_enable_price', '');
            } else {
                update_post_meta($post_id, '_enable_price', 'yes');
            }
        }

        /*
         * Show 'Show Price' meta box on individual Product screen
         * In every Product page in the side bar shows the check box of add to 
         */
        public function priceForIndividualProduct()
        {
            global $post, $current_screen;

            if ('product' != $post->post_type) {
                return;
            }

            //Check if new product or existing product
            if ($current_screen->action == 'add') {
                //If new product, set the _enable_price to 'yes'
                $current_status = 'yes';
            } else {
                $current_status = ('yes' == get_post_meta($post->ID, '_enable_price', true)) ? 'yes' : '';
            }

            /**
             * Filter to modify the current price visibility status on
             * product edit page.
             * 
             * @since 6.5.1
             *
             * @param  string  $current_status  Current status.
             * @param  string  $action  Current screen status.
             */
            $current_status = apply_filters('quoteup_price_visibility_status_product_edit_page', $current_status, $current_screen->action);

            // Class to show/ hide the '_enable_price' meta field.
            $classAttr = quoteupIsHidePriceSettingEnabled() ? 'quoteup_hide': '';
            ?>
            <div class = "misc-pub-section <?php echo esc_attr($classAttr); ?>" id = "wdm_enable_price">
                <div class="wdm_div_left product_enquiry_disable_text">

            <?php _e('Show Price', QUOTEUP_TEXT_DOMAIN);
            ?> 
                </div>
                <div class="wdm_div_right">
                    <span>
                        <input type="checkbox" name="_enable_price" value="yes" <?php checked('yes', $current_status, true);
            ?>>
                    </span>
                </div>
                <div class="clear"></div>
            </div>
            <?php
        }
    }
}

$enableDisablePrice = new QuoteUpEnableDisablePrice();
