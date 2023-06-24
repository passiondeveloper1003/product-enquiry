<?php

namespace Templates\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handle the view of approval and Rejection of Quote
  * @static $instance object of class
 */

class QuoteupHandleQuoteApprovalRejectionView
{
    public $isProductDeleted = false;

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

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
     * Action to enqueue scripts.
     * Action that handles rejection approval view
     */
    protected function __construct()
    {
        add_action('quoteup_approval_rejection_content', array($this, 'approvalRejectionView'));
    }

    /**
     * This function is used to enqueue scripts
     */
    public function enqueueScripts()
    {
        wp_enqueue_style('quoteup_responsive', QUOTEUP_PLUGIN_URL.'/css/public/responsive-style.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/public/responsive-style.css'));
        wp_enqueue_style('quoteup-quote-table', QUOTEUP_PLUGIN_URL.'/css/public/quote-table-on-approval-rejection-page.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/public/quote-table-on-approval-rejection-page.css'));
        wp_enqueue_script('quoteup-frontend-script', QUOTEUP_PLUGIN_URL.'/js/public/reject-quote-button-animation.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/public/reject-quote-button-animation.js'), true);
        wp_enqueue_script('quoteup-cart-responsive', QUOTEUP_PLUGIN_URL.'/js/public/responsive-table.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR.'/js/public/responsive-table.js'), true);
        $form_data = quoteupSettings();

        quoteupRenderCustomCSS('quoteup_responsive');
    }

    /**
     * This function is used to get custom css
     * @param  Array $form_data Settings stored in database
     * @return String            Css stored in database
     */
    public function getCustomCSS($form_data)
    {
        $customCSS = '';
        if (isset($form_data[ 'button_CSS' ]) && $form_data[ 'button_CSS' ] == 'manual_css') {
            $customCSS = getManualCSS($form_data);
        }

        return $customCSS;
    }

    /**
     * Display view of Approval/Rejection functionality on the front-end i.e, Mail.
     * Gets the order id of the Enquiry.
     * Checks if session for Cart is still set or not , if not display error message.
     * Checks if out of stock or not
     */
    public function approvalRejectionView()
    {
        $result = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote(0);
        $expiredText = apply_filters('quoteup_quote_expired_text', __('The quotation has expired.', QUOTEUP_TEXT_DOMAIN));

        $args = array(
            'result' => $result,
            'expiredText' => $expiredText,
        );

        do_action('quoteup_approval_rejection_validation', $args);

        if (!isset($GLOBALS['approvalRejectionValid']) || $GLOBALS['approvalRejectionValid']) {
            $this->enqueueScripts();
            do_action('quoteup_approval_rejection_view', $args);
        }
    }

    /**
     * This function is used to check if product is available
     * @param  Array $quoteProduct Quote Product details
     * @return boolean              true if product is available
     */
    public function getProductStatus($quoteProduct)
    {
        if ($quoteProduct['variation_id'] != 0) {
            $productAvailable = isProductAvailable($quoteProduct[ 'variation_id' ]);
        } else {
            $productAvailable = isProductAvailable($quoteProduct[ 'product_id' ]);
        }

        return $productAvailable;
    }

    /**
     * This function is used to get SKU of product
     * @param  object  $product      Object of product
     * @param  integer $variation_id Variation ID
     * @return String                SKU of the product
     */
    public function getSku($product, $variation_id = 0)
    {
        $sku = '';
        if ($product->is_type('variable')) {
            $_variableProduct = wc_get_product($variation_id);
            $sku = $_variableProduct->get_sku();
            if (empty($sku)) {
                $sku = $product->get_sku();
            }
        } else {
            $sku = $product->get_sku();
        }

        return $sku;
    }
}


$this->quoteupViewQuotationApprovalRejection = QuoteupHandleQuoteApprovalRejectionView::getInstance();
