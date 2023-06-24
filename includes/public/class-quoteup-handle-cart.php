<?php

namespace Includes\Frontend;

$variationIdIndexes = array();
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Handles the cart activities on frontend part.
 * $originalProductId Original Product Id.
* @static instance Object of class
 */
class QuoteupHandleCart
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    private $originalProductId;

    /**
     * Returns the *Singleton* instance of this class.
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
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     *
     * Used to add various action and filter
     * Action for unset session when some new order is there.
     * Action for adding products to cart and redirecting to checkout page.
     * Action  to prevent WPML to change cart contents.
     * Action to unset session when order is placed or user clicks end session.
     * Action to load scripts and localize data for ajax.
     * Action for updating price before checkout.
     * Action to retain main product id before translation
     * Action to translate product id.
     * Action to to remove sold individual check if quotation session is in progress.
     */
    protected function __construct()
    {
        add_action('woocommerce_thankyou', array($this, 'unsetSession'), 10, 1);
        add_action('wp', array($this, 'redirectToCheckoutPage'), 1);
        add_action('init', array($this, 'removeAddToCart'), 15);
        add_action('init', array($this, 'unhookWPMLHooks'), 20);
        add_action('wp_ajax_clearsession', array($this, 'unsetSession'));
        add_action('wp_ajax_nopriv_clearsession', array($this, 'unsetSession'));
        add_action('woocommerce_before_calculate_totals', array($this, 'addPrice'));
        add_filter('translate_object_id', array($this, 'getIdBeforeTranslation'), 1, 2);
        add_filter('translate_object_id', array($this, 'translateProductId'), 9999, 2);
        add_filter('woocommerce_is_sold_individually', array($this, 'removeSoldIndividually'), 10, 1);
        add_filter('woocommerce_is_purchasable', array($this, 'checkIsProductPurchasAble'), 80, 2);
        // Filters for variations.
        add_filter('woocommerce_variation_is_visible', array($this, 'isVariationVisible'), 80, 4);
        add_filter('woocommerce_variation_is_purchasable', array($this, 'checkIsProductPurchasAble'), 80, 2);
    }

    /**
     * This function is used to remove sold individual check if quotation session
     * is in progress.
     *
     * @return bool false if Product is in quotation.
     */
    public function removeSoldIndividually($return)
    {
        global $quoteup;
        $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
        if ($quotationProduct) {
            return false;
        }

        return $return;
    }

    /**
     * This function is used to update price before checkout
     * @param object $cart_object Cart object
     */
    public function addPrice($cart_object)
    {
        global $quoteup;
        $quotationProducts = $quoteup->wcCartSession->get('quotationProducts');
        foreach ($cart_object->get_cart() as $key => $value) {
            if (!$quotationProducts) {
                continue;
            }

            foreach ($quotationProducts as $row) {
                $variations = unserialize($row['variation']);
                if (!empty($variations)) {
                    $newVariation = $this->getFormattedVariation($variations);
                    
                    if ($value['product_id'] == $row['product_id'] && $value['variation_id'] == $row['variation_id'] && $value['variation'] === $newVariation) {
                        $value['data']->set_price($row['newprice']);
                    }
                } elseif ($value['product_id'] == $row['product_id']) {
                    $value['data']->set_price($row['newprice']);
                }
            }
            unset($key);
        }
    }

    public function getFormattedVariation($variations)
    {
        $newVariation = array();
        foreach ($variations as $attributeName => $attributeValue) {
            $newVariation['attribute_'.trim($attributeName)] = trim($attributeValue);
        }

        return $newVariation;
    }

    /**
     * Used to enqueue script
     * To localize script for using ajax url and cart page url in js file.
     */
    public function loadScript()
    {
        wp_enqueue_script('quoteup-end-approval-script', QUOTEUP_PLUGIN_URL.'/js/public/end-approval-quote-session.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/public/end-approval-quote-session.js'), true);
        $url = get_permalink(get_option('woocommerce_cart_page_id'));
        wp_localize_script(
            'quoteup-end-approval-script',
            'quote_data',
            array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'URL' => $url,
            )
        );
    }

    /**
     * This function is used to remove add to cart button from all products.
     *
     * Add custom store notice when our session is started
     */
    public function removeAddToCart()
    {
        global $quoteup;
        $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
        if ($quotationProduct) {
            $this->loadScript();
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

            // For compatibility with Astra free theme
            add_filter('astra_woo_shop_product_structure', array($this, 'removeAddToCartAstraTheme'));

            add_action('wp_footer', array($this, 'customStoreNotice'));
        }
    }

    /**
     * Remove Add to Cart button.
     * 
     * Remove Add to Cart for Astra free theme on the shop page.
     * Callback for filter 'astra_woo_shop_product_structure'.
     * 
     * @since 6.5.0
     */
    public function removeAddToCartAstraTheme($shopStructure)
    {
        // Remove 'add_cart' value from the array 'shopStructure'.
        if (false !== ($key = array_search('add_cart', $shopStructure))) {
            unset($shopStructure[$key]);
        }

        return $shopStructure;
    }

    /**
     * Prevent WooCommerce Multilingual Plugin from changing the cart content.
     */
    public function unhookWPMLHooks()
    {
        global $quoteup;
        $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
        if ($quotationProduct) {
            quoteupRemoveClassAction('woocommerce_before_calculate_totals', 'WCML_Cart', 'woocommerce_calculate_totals', 100);
            quoteupRemoveClassAction('woocommerce_get_cart_item_from_session', 'WCML_Cart', 'translate_cart_contents', 10);
            quoteupRemoveClassAction('woocommerce_cart_loaded_from_session', 'WCML_Cart', 'translate_cart_subtotal', 10);
            quoteupRemoveClassAction('woocommerce_before_checkout_process', 'WCML_Cart', 'wcml_refresh_cart_total', 10);
        }
    }

    /**
     * Adds products mentioned in the Enquiry in the user's cart.
     * Checks for the Products in quote are in stock or not.
     * Checks if they are already in user's cart and update quantities likewise.
     * Set the cart session and add the Products in tha cart.
     * @param int    $enquiryId    Enquiry id of the Enquiry
     * @param string $enquiryEmail Email id of a user who enquired
     * @param string $enquiryHash  Hash of the enquiry
     */
    public function addProductsToCart($enquiryId)
    {
        global $wpdb, $quoteup;

        $table_name = getQuotationProductsTable();
        $sql = "SELECT ID,product_id, quantity, newprice, enquiry_id,variation_id,variation FROM $table_name WHERE enquiry_id=$enquiryId";
        $result = $wpdb->get_results($sql, ARRAY_A);
        $replace_order = new \WC_Cart();
        $replace_order->empty_cart(true);

        foreach ($result as $k => $v) {
            $product_id[$k] = $v['product_id'];
            $quantity[$k] = $v['quantity'];
            $variation_id[$k] = $v['variation_id'];
            $variationDetails[$k] = $v['variation'];
        }

        $quoteup->wcCartSession->set('quotationProducts', $result);
        $this->cartStockManagementCheck($product_id, $quantity, $variation_id, $variationDetails);
        foreach ((array) $result as $row) {
            $variations = unserialize($row['variation']);
            $newVariation = array();
            if ($variations != '') {
                foreach ($variations as $attributeName => $attributeValue) {
                    $newVariation['attribute_'.trim($attributeName)] = trim($attributeValue);
                }
            }
            if ('product_variation' == get_post_type($row['variation_id'])) {
                $variationProduct = wc_get_product($row['variation_id']);
                $variations = $variationProduct->get_variation_attributes();
                $replace_order->add_to_cart($row['product_id'], $row['quantity'], $row['variation_id'], $newVariation);
            } else {
                $replace_order->add_to_cart($row['product_id'], $row['quantity']);
            }
        }
    }

    /**
     * This function is used to check Stock avaiblity before adding quotation products in cart.
     * Stock checked for the Products in cart , backorders are not allowed case.
     * Check for the Product in Quote already present in cart , if yes check the stock and update the quantities of such products accordingly.
     * @param [array] $product_ids      [Array of product ids]
     * @param [array] $quantities       [Array of quantities]
     * @param [array] $variation_ids    [Array of variation ids]
     * @param [array] $variationDetails [Array of variation]
     */
    public function cartStockManagementCheck($product_ids, $quantities, $variation_ids, $variationDetails)
    {
        global $quoteup_enough_stock_variation_details;
        $size = sizeof($product_ids);
        for ($i = 0; $i < $size; ++$i) {
            $product_id = absint($product_ids[$i]);
            $variation_id = absint($variation_ids[$i]);
            $quantity = $quantities[$i];
            $variationDetail = $variationDetails[$i];

            if (empty($product_id) || empty($quantity)) {
                continue;
            }

        // Get the product
            $product_data = wc_get_product($variation_id ? $variation_id : $product_id);

        // Sanity check
            $postStatus = '';
            if ($product_data) {
                if (version_compare(WC_VERSION, '2.7', '<')) {
                    $postStatus = $product_data->post->post_status;
                } else {
                    $postStatus = $product_data->get_status();
                }
            }
            if ($quantity <= 0 || !$product_data || 'trash' === $postStatus) {
                $quoteup_enough_stock_variation_details = setEnoughStockFalse($product_id, $variation_id, $variationDetail);
                return;
            }

            // Stock check - only check if we're managing stock and backorders are not allowed
            $this->stockCheckIfManagingStock($product_data, $product_id, $variation_id, $quantity, $variationDetail);

            // Stock check - this time accounting for whats already in-cart
            $this->stockCheckForCartItems($product_data, $product_ids, $quantities, $variation_ids, $product_id, $variation_id, $variationDetail);
        }
    }

    /**
    * Sets the enough stock variable to false if product is not in stock and is not purchasable.
    * This is when the backorders are not allowed.
    * @param array $product_data Product details
    * @param int $product_id Product Id
    * @param int $variation_id Variation Id if Variable Product
    * @param int $quantity Quantity of Product
    * @param array $variationDetail Details of variations in Variable Product
    */
    public function stockCheckIfManagingStock($product_data, $product_id, $variation_id, $quantity, $variationDetail)
    {
        global $quoteup_enough_stock_variation_details;
        
        // Check product is_purchasable
        if (!$product_data->is_purchasable()) {
            $quoteup_enough_stock_variation_details = setEnoughStockFalse($product_id, $variation_id, $variationDetail);
        }

        if (!$product_data->is_in_stock()) {
            $quoteup_enough_stock_variation_details = setEnoughStockFalse($product_id, $variation_id, $variationDetail);
        }

        if (!$product_data->has_enough_stock($quantity)) {
            $quoteup_enough_stock_variation_details = setEnoughStockFalse($product_id, $variation_id, $variationDetail);
        }
    }

    /**
    * Get the stock and quantity details for the Products in Quote cart.
    * @param array $product_data Product details of current product check
    * @param array $product_ids Product Ids of Quote Cart Products
    * @param array $variation_ids Variation Ids if Variable Products
    * @param array $quantities Quantities of Product
    * @param int $product_id Product Id
    * @param int $variation_id Variation Id if Variable Product
    * @param array $variationDetail Variation details of Variable Product
    */
    public function stockCheckForCartItems($product_data, $product_ids, $quantities, $variation_ids, $product_id, $variation_id, $variationDetail)
    {
        global $quoteup_enough_stock_variation_details;
        if ($managing_stock = $product_data->managing_stock()) {
            $products_qty_in_cart = getAllCartItemsTotalQuantity($product_ids, $quantities, $variation_ids);

            if ($product_data->is_type('variation') && true === $managing_stock) {
                $check_qty = isset($products_qty_in_cart[ $variation_id ]) ? $products_qty_in_cart[ $variation_id ] : 0;
            } else {
                $check_qty = isset($products_qty_in_cart[ $product_id ]) ? $products_qty_in_cart[ $product_id ] : 0;
            }

            /*
             * Check stock based on all items in the cart.
             */
            if (!$product_data->has_enough_stock($check_qty)) {
                $quoteup_enough_stock_variation_details = setEnoughStockFalse($product_id, $variation_id, $variationDetail);
            }
        }
    }

    /**
     * Redirects user to the checkout page when user is in our session.
     * add the Quote products in cart and then redirect to checkout page.
     * @param string $defaultLink link to checkout page
     */
    public function redirectToCheckoutPage($defaultLink = null)
    {
        if (current_action() == 'wp' && is_page(wc_get_page_id('cart'))) {
            global $quoteup, $woocommerce;

            $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');

            if (isset($quotationProduct[0]['enquiry_id'])) {
                //Add products in the cart if not added already
                if ($woocommerce->cart->get_cart_contents_count() == 0) {
                    $this->addProductsToCart($quotationProduct[0]['enquiry_id']);
                }

                //Prevent Redirect Loop
                if ($woocommerce->cart->get_cart_contents_count() != 0) {
                    //Force Redirect to checkout
                    wp_redirect(wc_get_checkout_url());
                    exit;
                }
            }
        }

        if (!empty($defaultLink) && $defaultLink == 'ManualRedirect') {
            global $quoteup, $woocommerce;
            $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
            if (!empty($quotationProduct)) {
                wp_redirect(wc_get_checkout_url());
                exit;
            }
        }
    }

    /**
     * Unset session once the order is  completed.
     * Updates the Quotation history table with status order placed.
     * unset session if user clicks on end session in custom store notice
     * Clear the cart products after end session.
     * @param [int] $order Order id if the order is complete
     */
    public function unsetSession($order)
    {
        global $quoteup, $quoteupManageHistory;
        if ($order !== '') {
            $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
            if ($quotationProduct) {
                foreach ($quotationProduct as $row) {
                    $enquiry_id = $row['enquiry_id'];
                    break;
                }
                //update History Table
                $quoteupManageHistory->addQuoteHistory($enquiry_id, '-', 'Order Placed');
                //Add Enquiry id in order meta
                update_post_meta($order, 'quoteup_enquiry_id', $enquiry_id);
                //Add order note
                $orderObject = wc_get_order($order);
                $orderObject->add_order_note('This order is related to Enquiry Id #'.$enquiry_id);

                \Includes\QuoteupOrderQuoteMapping::updateOrderIDOfQuote($enquiry_id, $order);
            }
        }
        remove_action('wp_footer', array($this, 'customStoreNotice'));
        $quoteup->wcCartSession->unsetSession();
        /**
         * Remove the quoteup cookie 'quoteup_wp_session' from the browser.
         */
        do_action('quoteup_remove_enq_session');
        //When 'Click Here' button inside customer notice is clicked, Ajax is fired and hence die after execution
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && $_POST['action'] == 'clearsession') {
            if (WC()->cart->get_cart_contents_count() !== 0) {
                $replace_order = new \WC_Cart();
                $replace_order->empty_cart(true);
            }
            die();
        }
    }

    /**
     * Custom Store notice Displayed once Session is started, can't add Products to cart.
     */
    public function customStoreNotice()
    {
        $notice = __(' Your Quotation Session has started. Hence, you cannot add any more products. To end the session <input type="button" id="endsession" title="Ending Session will clear the current cart." value="Click Here">', QUOTEUP_TEXT_DOMAIN);

        echo '<p style="text-align:center;" class="demo_store woocommerce-store-notice">'.$notice.'</p>';
    }

    /**
     * We need to retain the Main product id as the Quote is generated for that product.
     * Below function will store the value of original product id in temporary variable and on the last call of translate_object_id filter we'll grab value from temporary variable and return that.
     * @param int $productId Product id
     * @param string $postType Type of post
     */
    public function getIdBeforeTranslation($productId, $postType)
    {
        if (is_admin()) {
            return $productId;
        }

        global $quoteup;
        $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
        if ($quotationProduct && ($postType == 'product' || $postType == 'product_variation')) {
                $this->originalProductId = $productId;
        }

        return $productId;
    }

    /**
     *   Below function sets the product id of original product discarding the translated product id.
    * @param int $productId Product id
     * @param string $postType Type of post
     * @return int $productId Product Id of the Products in cart.
     */
    public function translateProductId($productId, $postType)
    {
        if (is_admin()) {
            return $productId;
        }

        global $quoteup;
        $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
        if ($quotationProduct && ($postType == 'product' || $postType == 'product_variation') && $this->originalProductId !=0) {
                $productId = $this->originalProductId;
        }

        return $productId;
    }

    /**
     * This function checks whether the product is purchaseable.
     * The function performs the check in the same way as WooCommerce
     * does, but with only difference that this function returns
     * true even if the product price is empty ('').
     * This function provides the result only when quoteup session is running.
     *
     * Note: This function should not be used in any other cases to check
     * whether product is puchaseable.
     *
     * @hooked 'woocommerce_is_purchasable'.
     * @hooked 'woocommerce_variation_is_purchasable'.
     *
     * @param bool      $isPurchaseable  Is product purchaseable.
     * @param object    $product         Product object.
     *
     * @return bool Returns true if product is purchaseable, false
     *              otherwise.
     */
    public function checkIsProductPurchasAble($isPurchaseable, $product)
    {
        
        global $quoteup;
        
        $quotationProduct = $quoteup->wcCartSession->get('quotationProducts');
        
        if (!empty($quotationProduct) && $product->exists() && 'publish' === $product->get_status()) {
            $isPurchaseable = true;
        }

        return apply_filters('quoteup_is_product_purchaseable_while_session', $isPurchaseable, $product);
    }

    /**
     * Check whether the variation should be visible.
     * This function performs the same check as WooCommerce method
     * 'variation_is_visible' does, but with difference that the
     * it doesn't check for the price. It means, the function will
     * return true even if there is no price for a particular variation.
     *
     * @hooked 'woocommerce_variation_is_visible'
     *
     * @param bool      $visible        The variation is visible or not.
     * @param int       $variationId    Variation ID.
     * @param int       $parentId   Parent ID.
     * @param object    $variation  WC_Product_Variation object.
     * @return bool     Returns true if the variation status is publish (doesn't
     *                  check for price), false otherwise.
     */
    public function isVariationVisible($visible, $variationId, $parentId, $variation)
    {
        global $quoteup;
        $quotationProducts = $quoteup->wcCartSession->get('quotationProducts');

        if (!empty($quotationProducts) && 'publish' === get_post_status($variation->get_id())) {
            $visible = true;
        }

        return apply_filters('quoteup_is_variation_visible_while_session', $visible, $variationId, $parentId, $variation);
    }
}

$this->wcCart = QuoteupHandleCart::getInstance();
