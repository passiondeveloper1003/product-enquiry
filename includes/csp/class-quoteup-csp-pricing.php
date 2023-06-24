<?php

namespace Includes\Admin\Compatible;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (! class_exists('QuoteupCSPPricing')) {

    /**
     * Class to alter the products' old prices on the backend.
     *
     * @since 6.4.1
     */
    class QuoteupCSPPricing
    {
        /**
         * @var Singleton The reference to *Singleton* instance of this class
         */
        private static $instance;

        /**
         * Return the *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            }

            return static::$instance;
        }

        /**
         * Hook functions on filters and actions.
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 9);
            add_action('quoteup_add_csp_price_message', array($this, 'display_csp_price_message'));
        }

        /**
         * Enqueue the scripts and  on the quote edit page.
         *
         * @param string $hook The current admin page.
         *
         * @return void
         */
        public function enqueue_admin_scripts($hook)
        {
            if ('admin_page_quoteup-details-edit' != $hook || !isQuotationModuleEnabled()) {
                return;
            }

            wp_enqueue_script('quoteup-csp-comp-js', QUOTEUP_PLUGIN_URL.'/js/csp/quoteup-csp-quote-creation.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/csp/quoteup-csp-quote-creation.js'), false);
            
            wp_enqueue_style('quoteup-csp-comp-css', QUOTEUP_PLUGIN_URL.'/css/csp/quoteup-csp-quote-creation.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/csp/quoteup-csp-quote-creation.css'));

            $nonce = wp_create_nonce('pep-csp-compat');
            $localizedData = array(
                'nonce' => $nonce
            );
            wp_localize_script('quoteup-csp-comp-js', 'pep_csp_comp', $localizedData);
        }

        /**
         * Display a message to update the original prices of the products.
         *
         * @return void
         */
        function display_csp_price_message()
        {
            $enquiryId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);        
            $orderId   = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote($enquiryId);

            if (!empty($orderId)) {
                return;
            }
            ?>
            <div class="quoteup-csp-price-update-msg">
                <p><?php echo sprintf(__('The prices for the products may be different for this user as CSP plugin is currently active. Click %1$shere%2$s to refresh the prices.', QUOTEUP_TEXT_DOMAIN), '<button type="button" class="button quoteup-csp-price-update-btn" name="csp_update_price">', '</button>'); ?></p>
            </div>
            <?php
        }
    }

    QuoteupCSPPricing::getInstance();
}
