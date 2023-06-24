<?php

namespace Includes\Frontend\Compatible;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (! class_exists('QuoteupCSP')) {

    /**
     * Class to alter the product prices on the frontend.
     *
     * @since 6.4.1
     */
    class QuoteupCSP
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
         * Add the callback functions for actions and filters.
         */
        public function __construct()
        {
            add_filter('quoteup_alter_product_price', array($this, 'getCSPPrice'), 10, 4);
            add_filter('csp_filter_cart_discount_limits', array($this, 'removeCartDiscountPrice'), 20);
        }

        /**
         * Return the CSP price.
         * 
         * Callback for 'quoteup_alter_product_price' filter.
         *
         * @param float  $price      Product price.
         * @param int    $product_id Product ID.
         * @param int    $quantity   Product quantity.
         * @param string $user_email User email.
         *
         * @return float    Return the CSP price.
         */
        public function getCSPPrice($price, $product_id, $quantity, $user_email)
        {
            $user_id = 0;

            if (!empty($user_email)) {
                $user_data = get_user_by('email', $user_email);
                if(!empty($user_data)) {
                    $user_id = $user_data->ID;
                }
            } else {
                $user_id = get_current_user_id();
            }

            if (!empty($user_id)) {
                $price = apply_filters('wdm_get_csp_for_product_qty', $price, $product_id, $quantity, $user_id);
            }

            return $price;
        }

        /**
         * Don't apply the CSP cart discount price on the WC cart.
         *
         * Check whether the PEP Quote Session (quotationProducts) is currently
         * active (i.e. if the user has been redirected to the checkout page after
         * clicking on the 'Approve Quote' button). If yes, don't apply the cart
         * discount price on the WC cart.
         *
         * @param array $rules CSP rules for cart discount functionality.
         *
         * @return array   Return an empty array if PEP Quote Session is currently
         *                  active, otherwise return the $rules as it is.
         */
        public function removeCartDiscountPrice($rules)
        {
            global $quoteup;
            $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');

            if (!empty($quotationProduct)) {
                $rules = array();
            }

            return $rules;
        }
    }

    QuoteupCSP::getInstance();
}
