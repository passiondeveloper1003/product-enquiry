<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Enable Quoteup button
 * Add enable enquiry for individual product in backend.
*/
if (!class_exists('QuoteUpEnableDisableQuoteUpButton')) {
    class QuoteUpEnableDisableQuoteUpButton
    {
        /**
         * Action to enable Quoteup button
         * Action to add enable enquiry for individual product in backend.
         */
        public function __construct()
        {
            add_action('woocommerce_process_product_meta', array($this, 'woocommerceSaveMetaFields'));
            add_action('post_submitbox_misc_actions', array($this, 'enableEnquiryForIndividualProduct'));
        }

        /**
         * Process disable_QuoteUp attribute of individual post and save it in database
         *
         * @param int $post_id Post Id
         */
        public function woocommerceSaveMetaFields($post_id)
        {
            if (!isset($_POST[ '_enable_pep' ]) || empty($_POST[ '_enable_pep' ])) {
                update_post_meta($post_id, '_enable_pep', '');
            } else {
                update_post_meta($post_id, '_enable_pep', 'yes');
            }
        }

        /*
         * Show 'Enable Enquiry Button' meta box on individual Product screen         
         * In every Product page in the side bar shows the check box of add to 
         */
        public function enableEnquiryForIndividualProduct()
        {
            global $post, $current_screen;

            if ('product' != $post->post_type) {
                return;
            }

            $settings = get_option('wdm_form_data');

            if (!$settings || (isset($settings[ 'only_if_out_of_stock' ]) && $settings[ 'only_if_out_of_stock' ])) {
                if (\quoteupIsProductInStock($post->ID)) {
                    $label = __('Enable Enquiry Button when Product goes out of Stock', QUOTEUP_TEXT_DOMAIN);
                } else {
                    $label = __('Enable Enquiry Button', QUOTEUP_TEXT_DOMAIN);
                }
            } else {
                $label = __('Enable Enquiry Button', QUOTEUP_TEXT_DOMAIN);
            }

            //Check if new product or existing product
            if ($current_screen->action == 'add') {
                //If new product, enable Enquiry button by default
                $current_status = 'yes';
            } else {
                $current_status = ('yes' == get_post_meta($post->ID, '_enable_pep', true)) ? 'yes' : '';
            }

            /**
             * Filter to modify the current status of enquiry button on
             * product edit page.
             *
             * @since 6.5.1
             *
             * @param  string  $current_status  Current status.
             * @param  string  $action  Current screen status.
             */
            $current_status = apply_filters('quoteup_enquiry_btn_status_product_edit_page', $current_status, $current_screen->action);

            ?>
            <div class = "misc-pub-section" id = "wdm_enable_pep">
                <div class="wdm_div_left product_enquiry_disable_text">
            <?php echo $label;
            ?> 
                </div>
                <div class="wdm_div_right">
                    <span>
                        <input type="checkbox" name="_enable_pep" value="yes" <?php checked('yes', $current_status, true);
                        ?>>
                    </span>
                </div>
                <div class="clear"></div>
            </div>
            <?php
        }
    }
}

$enableDisableQuoteButton = new QuoteUpEnableDisableQuoteUpButton();
