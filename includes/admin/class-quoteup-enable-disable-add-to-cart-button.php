<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('QuoteUpEnableDisableAddToCartButton')) {

/**
 * Enable Add to cart for products.
 * Add enable add to cart for individual product in backend.
*/
    class QuoteUpEnableDisableAddToCartButton
    {
         /**
        * Action to enable add to cart
        * Action to add enable add to cart for individual product in backend.
        */
        public function __construct()
        {
            add_action('woocommerce_process_product_meta', array($this, 'saveAddToCartMetaField'), 10, 1);
            add_action('post_submitbox_misc_actions', array($this, 'addToCartForIndividualProduct'));
        }

        /**
         * Process _enable_add_to_cart attribute of individual post and save it in database
         * @param int $post_id Post Id
         */
        public function saveAddToCartMetaField($post_id)
        {
            if (!isset($_POST[ '_enable_add_to_cart' ]) || empty($_POST[ '_enable_add_to_cart' ])) {
                update_post_meta($post_id, '_enable_add_to_cart', '');
            } else {
                update_post_meta($post_id, '_enable_add_to_cart', 'yes');
            }
        }

        /*
         * Show Disable Add_to_cart meta box on individual Product screen based on the meta field value.
         * In every Product page in the side bar shows the check box of add to cart.
         */
        public function addToCartForIndividualProduct()
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
                $current_status = ('yes' == get_post_meta($post->ID, '_enable_add_to_cart', true)) ? 'yes' : '';
            }

            /**
             * Filter to modify the current status of add to cart button on
             * product edit page.
             *
             * @since 6.5.1
             *
             * @param  string  $current_status  Current status.
             * @param  string  $action  Current screen status.
             */
            $current_status = apply_filters('quoteup_add_to_cart_btn_status_product_edit_page', $current_status, $current_screen->action);

            // Class to show/ hide the '_enable_add_to_cart' meta field.
            $classAttr = quoteupIsHidePriceSettingEnabled() || quoteupIsHideAddToCartSettingEnabled() ? 'quoteup_hide': '';
            ?>
            <div class = "misc-pub-section <?php echo esc_attr($classAttr); ?>" id = "wdm_enable_add_to_cart">
                <div class="wdm_div_left product_enquiry_disable_text">
                    <?php _e('Show Add to Cart button', QUOTEUP_TEXT_DOMAIN);
                    ?> 
                </div>
                <div class="wdm_div_right">
                    <span>
                        <input type="checkbox" name="_enable_add_to_cart" value="yes" <?php checked('yes', $current_status, true);
                        ?>>
                    </span>
                </div>
                <div class="clear"></div>
            </div>
            <?php
        }
    }
}

$enableDisableAddToCartButton = new QuoteUpEnableDisableAddToCartButton();
