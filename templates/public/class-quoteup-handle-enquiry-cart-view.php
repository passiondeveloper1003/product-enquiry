<?php

namespace Templates\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handle the view of approval and Rejection of Quote
 *
 * @static $instance object of class
 */

class QuoteupHandleEnquiryCartView
{
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
     * Action to display quote cart view.
     */
    protected function __construct()
    {
        add_action('quoteup_enquiry_cart_content', array($this, 'enquiryCartView'));
        add_action('wp_footer', array($this, 'loadMiniCart'));
        add_action('wp_ajax_quoteup_get_cart', array($this, 'miniCartItemsHtml'));
        add_action('wp_ajax_nopriv_quoteup_get_cart', array($this, 'miniCartItemsHtml'));
    }

    /**
     * This function is used to enqueue scripts
     */
    public function enqueueScripts()
    {
        global $quoteup;
        $form_data = quoteupSettings();
        $prodCount = (int) $quoteup->wcCartSession->get('wdm_product_count');

        if (quoteupIsMPEEnabled($form_data) && $prodCount > 0) {
            // When MPE is enabled.
            wp_enqueue_script('quoteup-quote-cart', QUOTEUP_PLUGIN_URL.'/js/public/quote-cart.js', array('jquery', 'jquery-ui-draggable'), QUOTEUP_VERSION, true);
            wp_enqueue_script('quoteup-cart-responsive', QUOTEUP_PLUGIN_URL.'/js/public/responsive-table.js', array('jquery', 'jquery-ui-draggable'), QUOTEUP_VERSION, true);

            $redirect_url = $quoteup->displayQuoteButton->getRedirectUrl($form_data);

            $data = getLocalizationDataForJs($redirect_url);

            wp_localize_script('quoteup-quote-cart', 'wdm_data', $data);

            // wp_enqueue_script('phone_validate', QUOTEUP_PLUGIN_URL.'/js/public/phone-format.js', array('jquery'), false, true);

            // Enqueue custom form phone field required styles and scripts.
            quoteupEnqueuePhoneFieldsScripts();
            wp_enqueue_style('quoteup_responsive', QUOTEUP_PLUGIN_URL.'/css/public/responsive-style.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/public/responsive-style.css'));

            // Enqueue style for Enquiry Cart page when Astra theme is active.
            if (defined('ASTRA_THEME_VERSION')) {
                wp_enqueue_style('quoteup-astra-enquiry-cart-css', QUOTEUP_PLUGIN_URL.'/css/astra/enquiry-cart.css', array(), filemtime(QUOTEUP_PLUGIN_DIR.'/css/astra/enquiry-cart.css'));
            }

            if (!quoteupIsCustomFormEnabled($form_data) && isset($form_data['enable_google_captcha']) && $form_data['enable_google_captcha'] == 1) {
                // If default form and captcha is enabled.
                if (quoteupIsCaptchaVersion3($form_data)) {
                    $siteKey        = isset($form_data['google_site_key']) ? $form_data['google_site_key'] : false;
                    $lang_query_var = quoteupIsWpmlActive() ? '&hl='.ICL_LANGUAGE_CODE : '';

                    // If reCaptcha version 3.
                    wp_register_script('quoteup-google-captcha', 'https://www.google.com/recaptcha/api.js?render='.$siteKey.$lang_query_var, array(), QUOTEUP_VERSION, true);
                    wp_enqueue_script('quoteup-google-captcha');
                    wp_localize_script(
                        'quoteup-google-captcha',
                        'quoteup_captcha_data',
                        array(
                        'captcha_version'   => 'v3',
                        'site_key'          =>  $siteKey,
                        )
                    );
                } else {
                    $lang_query_var = quoteupIsWpmlActive() ? '?hl='.ICL_LANGUAGE_CODE : '';

                    // If reCaptcha version 2.
                    wp_register_script('quoteup-google-captcha', 'https://www.google.com/recaptcha/api.js'.$lang_query_var, array(), QUOTEUP_VERSION);
                    wp_enqueue_script('quoteup-google-captcha');
                }
            }
        }
    }

    /**
     * This function is used to get css settings
     *
     * @param  [array] $form_data [settings stored in database]
     * @return [int]            [1 if manual css is selected]
     */
    public function getCssSettings($form_data)
    {
        $manual_css = '';
        if (isset($form_data[ 'button_CSS' ]) && $form_data[ 'button_CSS' ] == 'manual_css') {
            $manual_css = 1;
        }

        return $manual_css;
    }

    /**
     * This function is used to get image URL
     *
     * @param  Array $product product details in cart session
     * @return String          Image URL
     */
    public function getImageURL($product)
    {
        $img_content = '';
        if (isset($product[ 'variation_id' ]) && $product[ 'variation_id' ] != '') {
            $img_content = wp_get_attachment_url(get_post_thumbnail_id($product[ 'variation_id' ]));
        }
        if (!$img_content || $img_content == '') {
            $img_content = wp_get_attachment_url(get_post_thumbnail_id($product[ 'id' ]));
        }
        if (!$img_content || $img_content == '') {
            $img_content = WC()->plugin_url().'/assets/images/placeholder.png';
        }

        return $img_content;
    }

    /**
     * This function is used to get total price to be displayed
     * Returns '-' if enable price is disabled
     *
     * @param  String $current_status 'yes' if price is enabled
     * @param  Array  $product        Product details in cart page
     * @return String                 price to be displayed
     */
    public function getTotalPrice($current_status, $product)
    {
        if ($current_status == 'yes') {
            $totalPrice = $product['price'];
            if (empty($totalPrice) || $totalPrice == null) {
                $totalPrice = 0;
            }
            $totalPrice = wc_price($totalPrice * $product['quantity']);
        } else {
            $totalPrice = '-';
        }

        return $totalPrice;
    }

    /**
     * This function is used to get price to be displayed.
     * Returns '-' if enable price is disabled
     *
     * @param  String $current_status 'yes' if price is enabled
     * @param  Array  $product        Product details in cart page
     * @return String                 price to be displayed
     */
    public function getPrice($product, $ignoreEnablePrice = false, $html = true)
    {
        $current_status = quoteupShouldHidePrice($product[ 'id' ]) ? '' : 'yes';
        if ($current_status == 'yes' || $ignoreEnablePrice) {
            $price = $product['price'];
            if (empty($price) || $price == null) {
                $price = 0;
            }
            if ($html) {
                $price = wc_price($price);
            }
        } else {
            $price = '-';
        }

        return $price;
    }

    public function getEnquiryCartTotal($ignoreEnablePrice = false, $html = true)
    {
        global $quoteup;
        $products = $quoteup->wcCartSession->get('wdm_product_info');
        if (empty($products)) {
            return wc_price(0);
        }
        $total = 0;
        $priceHidden = false;
        foreach ($products as $product) {
            $price = $quoteup->quoteupEnquiryCart->getPrice($product, $ignoreEnablePrice, false);
            if ($price == '-') {
                $priceHidden = true;
                continue;
            } else {
                $total += floatval($price)*floatval($product['quantity']);
            }
        }
        if ($priceHidden) {
            return wc_price($total).'+';
        }
        return wc_price($total);
    }

    /**
     * This function is used to get placehoder for remarks textarea
     *
     * @param  Array $form_data Settings stored in database
     * @return String            Placeholder to be displayed
     */
    public function getPlaceholder($form_data)
    {
        $placeholder = quoteupGetRemarksFieldPlaceholderEnqCart($form_data);
        return $placeholder;
    }

    /**
     * This function is used to display cart
     */
    public function enquiryCartView()
    {
        $this->enqueueScripts();

        $shopPageUrl = get_permalink(get_option('woocommerce_shop_page_id'));
        $default_vals = array('after_add_cart' => 1,
            'button_CSS' => 0,
            'pos_radio' => 0,
            'show_powered_by_link' => 0,
            'enable_send_mail_copy' => 0,
            'enable_telephone_no_txtbox' => 0,
            'only_if_out_of_stock' => 0,
            'dialog_product_color' => '#3079ED',
            'dialog_text_color' => '#000000',
            'dialog_color' => '#F7F7F7',
        );
        $form_data = get_option('wdm_form_data', $default_vals);
        $url = admin_url();

        $args = array(
            'shopPageUrl' => $shopPageUrl,
            'form_data' => $form_data,
            'url' => $url,
            );

        $args = apply_filters('quoteup_enquiry_mail_args', $args);
        
        //This loads the template for Enquiry Cart
        quoteupGetPublicTemplatePart('enquiry-cart/enquiry-cart', '', $args);
    }

    public function loadMiniCart()
    {
        $form_data = quoteupSettings();
        if (!isset($form_data['enable_disable_mpe']) || 1 != $form_data[ 'enable_disable_mpe' ]|| !isset($form_data['enable_disable_mini_cart']) || 1 != $form_data['enable_disable_mini_cart']) {
            return;
        }

        $scriptDebug  = apply_filters('quotuep_load_minified_mini_cart_scripts', true);
        $loadMinified = '.min';
        if (!$scriptDebug) {
            $loadMinified = '';
        }

        wp_enqueue_style('quoteup-mini-cart-css', QUOTEUP_PLUGIN_URL.'/css/public/mini-cart'.$loadMinified.'.css');

        /*
           If current page is enquiry cart page, return from the method.
           Don't generate the HTML for mini-cart and don't enqueue the JS
           script for mini-cart.
        */
        if (quoteupIsEnquiryCartPage()) {
            return;
        }

        wp_register_script('quoteup-mini-cart-js', QUOTEUP_PLUGIN_URL.'/js/public/mini-cart'.$loadMinified.'.js', array(), QUOTEUP_VERSION, true);
        wp_enqueue_script('quoteup-mini-cart-js');

        $data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
        );

        wp_localize_script('quoteup-mini-cart-js', 'mini_cart', $data);

        $args = array(
            'form_data' => $form_data,
        );
        //This loads the template for Enquiry Cart
        quoteupGetPublicTemplatePart('mini-cart/mini-cart', '', $args);
    }

    /**
     * Callback function to 'quoteup_get_cart' Ajax request.
     * Outputs mini-cart HTML as Ajax response.
     *
     * @since 6.4.0
     */
    public function miniCartItemsHtml()
    {
        global $quoteup;
        $cart = array(
            'html' => 0,
            'count' => 0,
            'total' => 0,
        );

        ob_start();
        $items = $quoteup->wcCartSession->get('wdm_product_info');
        $args = array(
            'items' => $items,
        );
        if (!empty($items)) {
            quoteupGetPublicTemplatePart('mini-cart/mini-cart-items', '', $args);
        }
        $output = ob_get_contents();
        ob_end_clean();

        $cart['html']        = $output;
        $cart['count']       = $quoteup->wcCartSession->get('wdm_product_count');
        $cart['total']       = $this->getEnquiryCartTotal();
        $cart['totalString'] = $this->getMiniCartTotalText($cart['total']);
        $cart                = apply_filters('quoteup_mini_cart_items_html', $cart, $items);

        echo json_encode($cart);
        wp_die();
    }

    /**
     * Return the 'Total' text shown on the mini cart.
     *
     * @param string  WooCommerce Price HTML containing total price.
     *
     * @return string  Return a 'Total' text showing the total price.
     */
    public function getMiniCartTotalText($totalHTML)
    {
        $totalText = sprintf(__('Total : %s', QUOTEUP_TEXT_DOMAIN), $totalHTML);
        $totalText = apply_filters('quoteup_mini_cart_total_text', $totalText, $totalHTML);
        return $totalText;
    }
}

$this->quoteupEnquiryCart = QuoteupHandleEnquiryCartView::getInstance();
