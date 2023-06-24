<?php

namespace Includes\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to add extra features in PEP
 * - Adds Create Quotation from Dashboard feature.
 * - shows button for save quotation and send quotation.
 * - Handles everything about the create quotation from dashboard.
  * @static $instance Object of class
  * $enquiry_details array enquiry details
  *  @static $currentLanguage current language of site.
  * @static $quoteupVatiationData array Variation data
 */
class QuoteupCreateDashboardQuotation
{
    protected static $instance = null;
    public $enquiry_details = null;
    private static $currentLanguage = '';
    protected static $quoteupVatiationData = array();

    /**
     * Function to create a singleton instance of class and return the same.
     *
     * @return object -Object of the class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Action for enqueueing scripts and styles and making template for the page.
     * Action for saving Quote in db.
     * Action for sending mail through ajax.
     */
    private function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'), 1, 1);
        add_action('wp_ajax_save_dashboard-quote', array($this, 'saveQuote'));
        add_action('wp_ajax_action_send_dashboard_quote', array('Includes\Admin\SendQuoteMail', 'sendMailAjaxCallback'));
        do_action('quoteup_create_dashboard_custom_field');
        add_filter('woocommerce_variation_is_visible', array($this, 'isVariationVisible'), 20, 4);
    }

    /**
    * Returns the Variations of Product in string format.
    * @param array $variation_data Variation data of Product.
    * @param int $variation_id Variation Id
    */
    public static function getVariations($variation_data, $variation_id)
    {
        if (isset($variation_data) && $variation_data != '') {
            $variation_data = maybe_unserialize($variation_data);
            $variableProduct = wc_get_product($variation_id);
            $product_attributes = $variableProduct->get_attributes();
            return getVariationString($variation_data, $variableProduct, $product_attributes);
        }

        return '';
    }

    /**
     * Check whether the variation should be visible.
     * This function performs the same check as WooCommerce method
     * 'variation_is_visible' does, but with difference that the
     * it doesn't check for the price. It means, the function will
     * return true even if there is no price for a particular variation.
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
        if ('publish' === get_post_status($variation->get_id())) {
            $visible = true;
        }
        return apply_filters('quoteup_is_variation_visible', $visible, $variationId, $parentId, $variation);
    }

    /**
     * This function is used to enqueue scripts.
     * Checks whether scripts and styles are already enqueued or not and enqueue accordingly.
     * Make data array for localizing scripts.
     * Include woocommerce scripts, datepicker scripts and WPML Css.
     * Adds the inline css to hide Product image on create quote dashboard page.
     */
    public function enqueueScripts()
    {
        if (!isset($_GET['page']) || $_GET['page'] != "quoteup-create-quote") {
            return;
        }

        $form_data = quoteupSettings();
        wp_enqueue_style('quoteup-common-css', QUOTEUP_PLUGIN_URL.'/css/common.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/common.css'));
        wp_enqueue_script('quoteup-functions', QUOTEUP_PLUGIN_URL.'/js/admin/functions.js', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/functions.js'));
        wp_enqueue_script('quoteup-encode', QUOTEUP_PLUGIN_URL.'/js/admin/encode-md5.js', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/encode-md5.js'));
        wp_enqueue_script('postbox');

        $this->addInlineCss();

        $upload_dir     = wp_upload_dir();
        $quoteupPDFPath = $upload_dir['baseurl'].'/QuoteUp_PDF/';

        wp_localize_script(
            'quoteup-functions',
            'quote_data',
            array(
                    'decimal_separator' => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals' => wc_get_price_decimals(),
                    'price_format' => get_woocommerce_price_format(),
                    'currency_symbol' => get_woocommerce_currency_symbol(),
                    'path' => $quoteupPDFPath,
                    )
        );

        wp_enqueue_script('bootstrap-modal', QUOTEUP_PLUGIN_URL.'/js/admin/bootstrap-modal.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/bootstrap-modal.js'), true);
        wp_enqueue_style('modal_css1', QUOTEUP_PLUGIN_URL.'/css/wdm-bootstrap.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/wdm-bootstrap.css'));
        wp_enqueue_style('dashboard-quote-creation-css', QUOTEUP_PLUGIN_URL.'/css/admin/dashboard-quote-creation.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/dashboard-quote-creation.css'));
        wp_enqueue_style('quoteup-price-change-css', QUOTEUP_PLUGIN_URL . '/css/admin/price-change.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/price-change.css'));
        wp_enqueue_style('table-responsive-creation-css', QUOTEUP_PLUGIN_URL.'/css/admin/table-responsive.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/table-responsive.css'));

        wp_enqueue_script('dashboard-quote-creation-js', QUOTEUP_PLUGIN_URL.'/js/admin/dashboard-quote-creation.js', array('jquery', 'wp-util'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/dashboard-quote-creation.js'), true);
        $pdfDisplay = isset($form_data['enable_disable_quote_pdf'])?$form_data['enable_disable_quote_pdf']:1;
        $formSelected = $this->getSelectedForm($form_data);

        $data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'fields' => apply_filters('quoteup_get_custom_field', 'fields'),
            'quoteup_no_products' => sprintf(__('%s No Products Selected %s', QUOTEUP_TEXT_DOMAIN), "<tr class='quoteup-no-product'><td colspan='6' style='text-align: center;'>", '</td></tr>'),
            'quantity_not_valid' => __('Enter valid Quantity', QUOTEUP_TEXT_DOMAIN),
            'price_not_valid' => __('Enter valid Price', QUOTEUP_TEXT_DOMAIN),
            'no_products_in_quotation' => __('No Products in Quotation', QUOTEUP_TEXT_DOMAIN),
            'update_quotation_text' => __('Update Quotation', QUOTEUP_TEXT_DOMAIN),
            'same_variation' => __('Same variation of a product cannot be added twice.', QUOTEUP_TEXT_DOMAIN),
            'invalid_variation' => __('Please select a valid variation for', QUOTEUP_TEXT_DOMAIN),
            'PDF' => $pdfDisplay,
            'formSelected' => $formSelected,
            'cf_phone_field_pref_countries' => quoteupReturnPreferredCountryCodes(),
        );

        wp_localize_script('dashboard-quote-creation-js', 'wdm_data', $data);
        
        wp_enqueue_script('quoteup-select2', QUOTEUP_PLUGIN_URL.'/js/admin/quoteup-select2.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/quoteup-select2.js'));
        wp_enqueue_script('quoteup-price-change-manipulation-js', QUOTEUP_PLUGIN_URL.'/js/admin/price-change-manipulation.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/price-change-manipulation.js'));

        wp_enqueue_style('quoteup-select2-css', QUOTEUP_PLUGIN_URL.'/css/admin/quoteup-select2.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/quoteup-select2.css'));
        wp_enqueue_style('woocommerce-admin-css', QUOTEUP_PLUGIN_URL.'/css/admin/woocommerce-admin.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/woocommerce-admin.css'));

        if (is_callable('WC')) {
            if (shouldScriptBeEnqueued('products-selection-js')) {
                wp_enqueue_script('products-selection-js', QUOTEUP_PLUGIN_URL.'/js/admin/products-selection.js', array('jquery'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/products-selection.js'));
                $productsSelectionData = array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                );

                wp_localize_script('products-selection-js', 'productsSelectionData', $productsSelectionData);
            }

            if (shouldScriptBeEnqueued('wc-enhanced-select-extended')) {
                wp_enqueue_script('wc-enhanced-select-extended', QUOTEUP_PLUGIN_URL.'/js/admin/enhanced-select-extended.js', array('jquery', 'quoteup-select2'), filemtime(QUOTEUP_PLUGIN_DIR . '/js/admin/enhanced-select-extended.js'));

                wp_localize_script('wc-enhanced-select-extended', 'wc_enhanced_select_params', array(
                        'i18n_matches_1' => _x('One result is available, press enter to select it.', 'enhanced select', 'quoteup'),
                        'i18n_matches_n' => _x('%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'quoteup'),
                        'i18n_no_matches' => _x('No matches found', 'enhanced select', 'quoteup'),
                        'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'quoteup'),
                        'i18n_input_too_short_1' => _x('Please enter 1 or more characters', 'enhanced select', 'quoteup'),
                        'i18n_input_too_short_n' => _x('Please enter %qty% or more characters', 'enhanced select', 'quoteup'),
                        'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'quoteup'),
                        'i18n_input_too_long_n' => _x('Please delete %qty% characters', 'enhanced select', 'quoteup'),
                        'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'quoteup'),
                        'i18n_selection_too_long_n' => _x('You can only select %qty% items', 'enhanced select', 'quoteup'),
                        'i18n_load_more' => _x('Loading more results', 'enhanced select', 'quoteup'),
                        'i18n_searching' => _x('Searching', 'enhanced select', 'quoteup'),
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'search_products_nonce' => wp_create_nonce('search-products'),
                    ));
            }

            if (shouldStyleBeEnqueued('woocommerce_admin_styles')) {
                wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url().'/assets/css/admin.css', array(), filemtime(QUOTEUP_WC_PLUGIN_DIR . '/assets/css/admin.css'));
            }

            if (shouldStyleBeEnqueued('products-selection-css')) {
                wp_enqueue_style('products-selection-css', QUOTEUP_PLUGIN_URL.'/css/admin/products-selection.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/products-selection.css'));
            }

            global $quoteup;

            $quoteup->quoteDetailsEdit->includeWooCommerceScripts();
        }

        enqueueDatePickerFiles();
        $this->enqueueWpmlCss();

        $args = array();
        quoteupGetAdminTemplatePart('quote-creation', '', $args);

        // Enqueue styles and scripts required to custom form phone field.
        quoteupEnqueuePhoneFieldsScripts(true);
    }

    /**
    * Checks which form is selected by admin
    * If nothing is selected , select default form.
    * @param array $form_data Settings of quoteUp
    */
    private function getSelectedForm($form_data)
    {
        return isset($form_data['enquiry_form'])?$form_data['enquiry_form']:'default';
    }

    /**
    *  Adds inline styling for hiding product image on backend
    */
    private function addInlineCss()
    {
        $cssString = "th.quote-product-image, td.item-content-img {display: none;}";
        if (version_compare(WC_VERSION, '2.6', '>')) {
            wp_add_inline_style('common-css', $cssString);
        }
    }

    /**
    * Enqueues WPML Css.
    */
    private function enqueueWpmlCss()
    {
        if (quoteupIsWpmlActive()) {
            wp_enqueue_style('dashboard-quote-creation-wpml-css', QUOTEUP_PLUGIN_URL.'/css/admin/dashboard-quote-creation-wpml.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/admin/dashboard-quote-creation-wpml.css'));
        }
    }

    /**
     * Displays Language Selector to select the language.
     */
    private function displayLanguageSelector()
    {
        // Check if WPML is active or not
        if (!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE) {
            return;
        }
        ?>
        <label class="quote-language left-label"> <?php _e('Quote Language :', QUOTEUP_TEXT_DOMAIN) ?> </label>
        <?php
        echo '<select class="quoteup-pdf-language wc-language-selector">';
        echo "<option value='null'>Select Language</option>";
        $activeLanguages = icl_get_languages('skip_missing=0&orderby=code');
        foreach ($activeLanguages as $languageCode => $languageInfo) {
            $languageName = $languageInfo['native_name'].'('.$languageInfo['translated_name'].')';
            echo "<option value='{$languageCode}'> {$languageName} </option>'";
        }
        echo '</select>';
    }

    /**
    * This function is used to Search the Products in the Search bar.
    */
    public static function jsonSearchProductsAndVariations()
    {
        //We won't write at the end of this because jsonSearchProducts will automatically do that for us.
        self::jsonSearchProducts('', array('product', 'product_variation'));
    }

    /**
     * Search for products and echo json.
     * It is very similar to WooCommerce's WC_AJAX::jsonSearchProducts. But it supports WPML too.
     * Gets the products which are published having title similar to the searched term and they must not be previously included in the same quote.
     *
     * @param string $term       (default: '')
     * @param string $post_types (default: array('product'))
     */
    private static function jsonSearchProducts($term = '', $post_types = array('product'))
    {
        global $wpdb;
        $post_types = apply_filters('quoteup_json_search_prod_type', $post_types);

        ob_start();

        check_ajax_referer('search-products', 'security');

        self::$currentLanguage = self::getProductsLanguage();

        $term = self::getTerm($term);

        $like_term = '%'.$wpdb->esc_like($term).'%';

        $query = $wpdb->prepare("
            SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
            WHERE posts.post_status = 'publish'
            AND (
                posts.post_title LIKE %s
                or posts.post_content LIKE %s
                OR (
                    postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
                )
            )
        ", $like_term, $like_term, $like_term);

        $query .= " AND posts.post_type IN ('".implode("','", array_map('esc_sql', $post_types))."')";

        $query = apply_filters('quoteup_json_search_products_query', $query);

        $query .= self::getQueryPart();

        $posts = array_unique($wpdb->get_col($query));

        $excludedProducts = self::getExcludedProducts();

        // Get Products of selected language only.
        $posts = self::getProductsOfSelectedLanguage($posts);

        $found_products = array();

        if (!empty($posts)) {
            foreach ($posts as $post) {
                $product = wc_get_product($post);

                if (!current_user_can('read_product', $post) || empty($product)) {
                    continue;
                }

                $product_type = $product->get_type();

                switch ($product_type) {
                    case 'simple':
                        $found_products = self::getSimpleProduct($post, $product, $found_products, $excludedProducts);
                        break;
                    case 'variable':
                        $found_products = self::addAllVariationsInSearchResults($post, $product, $found_products, $excludedProducts);
                        break;
                    case 'variation':
                        if (!$product || ($product->is_type('variation'))) {
                            break;
                        }
                        $found_products = self::getVariationProduct($post, $product, $found_products, $excludedProducts);
                        break;
                    default:
                        break;
                }
            }
        }
        $found_products = apply_filters('quoteup_json_search_found_products', $found_products);
        wp_send_json($found_products);
    }

    /**
    * This function gets the simple product details.
    * @param array $post post having the title similar to searched term
    * @param array $product above posts having post-type product
    * @param array $found_products empty at first
    * @param array $excludedProducts products included previously.
    * @return array $found_products details of simple product having the similar title as searched .
    */
    private static function getSimpleProduct($post, $product, $found_products, $excludedProducts)
    {
        $img_url = '';
        if (!$img_url || $img_url == '') {
            $img_url = wp_get_attachment_url(get_post_thumbnail_id($post));
        }
        if (!$img_url || $img_url == '') {
            $img_url = WC()->plugin_url().'/assets/images/placeholder.png';
        }
        if (!in_array(md5($post), $excludedProducts)) {
            $found_products[ md5($post) ] = array(
                'product_id' => $post,
                'product_type' => 'simple',
                'formatted_name' => rawurldecode($product->get_formatted_name()),
                'price' => $product->get_price(),
                'product_title' => $product->get_title(),
                'url' => admin_url("/post.php?post={$post}&action=edit"),
                'product_image' => $img_url,
                'sku' => $product->get_sku(),
                );
        }
        return $found_products;
    }
    /**
    * This function is to fetch the products with specific variations in the search bar.
    * If variations are not empty fetch the variable product corresponding to variation for search bar.
    * @param array $post post having the title similar to searched term
    * @param array $product above posts having post-type product
    * @param array $found_products empty at first
    * @param array $excludedProducts products included previously.
    * @return array $found_products details of variation product having the similar title as searched .
    */
    private static function getVariationProduct($post, $product, $found_products, $excludedProducts)
    {
        $variation_data = $product->get_variation_attributes();

        $skipVariation = self::getSkipVariationStatus($variation_data);

        if (!empty($skipVariation)) {
            $product->variation_data = $variation_data;
            $found_products = self::addVariationInSearchResults($product, $post, $variation_data, $found_products, $excludedProducts);
        }
        return $found_products;
    }

    /**
    * This function is used to get the excluded products i.e, the products which are already included previously in the quote.
    * @return array $excludedProducts products previously included.
    */
    private static function getExcludedProducts()
    {
        $excludedProducts = array();

        if (!empty($_GET['exclude'])) {
            $excludedProducts = array_filter(array_unique(explode(',', $_GET['exclude'])));
        }
        return $excludedProducts;
    }

    /**
    * Get the products of selected language.
    * @param post $posts Posts
    */
    private static function getProductsOfSelectedLanguage($posts)
    {
        global $wpdb;
        if (!empty(self::$currentLanguage) &&  self::$currentLanguage != 'all' && !empty($posts)) {
            $elements   = implode(', ', $posts);
            $postTypes  = array('post_product_variation', 'post_product');
            $postTypes  = apply_filters('quoteup_json_search_prod_type_wpml', $postTypes);
            $postTypes  = implode("', '", $postTypes);
        
            $query      = "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_id IN ({$elements}) AND language_code = %s AND element_type IN ('{$postTypes}')";
            $posts      = $wpdb->get_col($wpdb->prepare($query, self::$currentLanguage));
        }
        return $posts;
    }

    /**
    * This function is to return the variation those are empty.
    * @param array $variation_data Variation data for variable products
    * @return boolean $skipVariation true if variation is empty.
    */
    private static function getSkipVariationStatus($variation_data)
    {
        $skipVariation = false;
        foreach ($variation_data as $variation) {
            if (empty($variation)) {
                $skipVariation = true;
                break;
            }
        }

        return $skipVariation;
    }

    /**
    * Gets the product language.
    * @return language of Product
    */
    private static function getProductsLanguage()
    {
        if (quoteupIsWpmlActive()) {
            return isset($_GET['language']) ? $_GET['language'] : 'all';
        }
    }

    /**
    * Get the term similar to Product in search bar.
    * @param string $term term in search bar
    */
    private static function getTerm($term)
    {
        if (empty($term)) {
            $term = wc_clean(stripslashes($_GET['term']));
        } else {
            $term = wc_clean($term);
        }

        if (empty($term)) {
            die();
        }

        return $term;
    }

    /**
    * This function gets the conditional query for product selection .
    * If there are any products in exclude i.e, products already included in quote do not fetch them again.
    * If there is any limit specified for the no. of products , include the limit in query.
    * @return string $query conditional query.
    */
    private static function getQueryPart()
    {
        $query = '';
        if (!empty($_GET['exclude'])) {
            $query .= ' AND posts.ID NOT IN ('.implode(',', array_map('intval', explode(',', $_GET['exclude']))).')';
        }

        if (!empty($_GET['limit'])) {
            $query .= ' LIMIT '.intval($_GET['limit']);
        }

        return $query;
    }

    /**
    * This function is to fetch all variations of the variable Product in the search result.
    * It first gets all the attributes of the variable product which is similar to the product which is searched.
    * Then the variations are fetched according to the attributes combinations.
    * It is then checked that if variation with similar attribute combination is present in the variable product list or not.
    * If yes the variable product details for that variation is fetched and put in the search bar.
    * @param array $post Post ID having the title similar to searched term
    * @param array $product product details
    * @param array $found_products product details of the products having similar term as searched.
    * @param array $excluded_products products which are included previously.
    * @return array $found_products products found with the required variations.
    */
    private static function addAllVariationsInSearchResults($post, $product, $found_products, $excluded_products)
    {
        /**
         * Using this filter will change the variable search functionality. If
         * the filter returns true, then the variable product will be listed
         * without its attributes. The admin will have to select the variations
         * from the drop down menu after adding a variable product in enquirt
         * list. Return true, if you have many variations for your variable
         * products.
         *
         * @param  bool   false     Not to use modified search functionality.
         * @param  int    $post     Post ID.
         * @param  object $product  Product object.
         * @param  array  $found_products    Product details having similar
         *                                   term as searched.
         * @param  array  $excluded_products Products which are included
         *                                   previously.
         *
         * @return bool   Return true if need to use modified search
         *                functionality, otherwise return false.
         */
        $altVariableSearch = apply_filters('quoteup_use_alt_variable_search', false, $post, $product, $found_products, $excluded_products);

        if ($altVariableSearch) {
            $img_url = '';
            if (! $img_url || $img_url == '') {
                $img_url = wp_get_attachment_url(get_post_thumbnail_id($post));
            }
            if (! $img_url || $img_url == '') {
                $img_url = WC()->plugin_url() . '/assets/images/placeholder.png';
            }

            if (! in_array(md5($post . time()), $excluded_products)) {
                $found_products[ md5($post . time()) ] = array(
                    'product_id'           => $post,
                    'variation_id'         => '0',
                    'product_type'         => 'variation',
                    'formatted_name'       => rawurldecode($product->get_formatted_name()) . __(' - variable', QUOTEUP_TEXT_DOMAIN),
                    'price'                => $product->get_price(),
                    'product_title'        => $product->get_title(),
                    'url'                  => admin_url("/post.php?post={$post}&action=edit"),
                    'product_image'        => $img_url,
                    'sku'                  => $product->get_sku(),
                    'variation_attributes' => '',
                    'variation_string'     => '',
                );
            }

            return $found_products;
        }

        /**
         * This action is documented in /product-enquiry-pro/includes/class-quoteup-wpml-compatibility.php.
         */
        do_action('quoteup_change_lang', self::$currentLanguage);

        /*
         * Below we will be calling get_variation_attributes function which makes a call to get_attributes.
         * get_variation_attributes uses name of attribute as a key in the returned array. We need to change the
         * behavior and we need to have a 'slug' as a key in the array returned by get_variation_attributes.
         * Therefore, we'll change the array returned by get_attributes. We'll keep name as slug in
         * get_attributes array
         */
        $attributesArray = self::getAttributesArray($product);
                
        if (empty($attributesArray)) {
            return $found_products;
        }
        $setAttributeName = function ($value) {
            return 'attribute_'.$value;
        };

        //Sets attribute_ prefix to all keys of an array
        $attributesArray = array_combine(
            array_map($setAttributeName, array_keys($attributesArray)),
            $attributesArray
        );

        $variationCombinations = self::getArrayCombinations($attributesArray);

        if (!$variationCombinations) {
            /**
             * This action is documented in /product-enquiry-pro/includes/admin/class-quoteup-generate-pdf.php.
             */
            do_action('wdm_after_create_pdf');
            /**
             * This action is documented in /product-enquiry-pro/includes/admin/class-quoteup-create-quotation-from-dashboard.php.
             */
            do_action('quoteup_reset_lang');
            return $found_products;
        }
        
        foreach ($variationCombinations as $singleVariationCombination) {
            $variation_id = self::getVariationId($product, $singleVariationCombination);

            if (!$variation_id) {
                continue;
            }

            $variation_product = wc_get_product($variation_id);
            if (version_compare(WC_VERSION, '3.0.0', '<')) {
                $variation_product->variation_data = $singleVariationCombination;
            } else {
                //Setting Variation Attributes in WC 3.0 and greater
                $variationData = array();
                foreach ($singleVariationCombination as $key => $value) {
                    // Remove attribute prefix which meta gets stored with.
                    if (0 === strpos($key, 'attribute_')) {
                        $key = substr($key, 10);
                    }
                    $variationData[ $key ] = $value;
                }
                $variation_product->set_props(array('attributes' => $variationData));
            }

            $found_products = self::addVariationInSearchResults($variation_product, $variation_id, $singleVariationCombination, $found_products, $excluded_products);
        }

        /**
         * This action is documented in /product-enquiry-pro/includes/admin/class-quoteup-generate-pdf.php.
         */
        do_action('wdm_after_create_pdf');

        /**
         * Use the action hook to reset the language if you used the 'quoteup_change_lang' hook earlier to switch the language.
         *
         */
        do_action('quoteup_reset_lang');
        return $found_products;
    }

    private static function getAttributesArray($product)
    {
        if (version_compare(WC_VERSION, '3.0.0', '<')) {
            add_filter('woocommerce_get_product_attributes', array(__CLASS__, 'modifyProductAttributeNames'), 99, 1);
            $attributesArray = $product->get_variation_attributes();
            remove_filter('woocommerce_get_product_attributes', array(__CLASS__, 'modifyProductAttributeNames'), 99, 1);
        } else {
            add_filter('woocommerce_product_get_attributes', array(__CLASS__, 'modifyProductAttributeNames'), 99, 1);
            $attributesArray = $product->get_attributes();
            remove_filter('woocommerce_product_get_attributes', array(__CLASS__, 'modifyProductAttributeNames'), 99, 1);
        }

        return $attributesArray;
    }

    private static function getVariationId($product, $singleVariationCombination)
    {
        if (version_compare(WC_VERSION, '3.0.0', '<')) {
            $variation_id = $product->get_matching_variation($singleVariationCombination);
        } else {
            $data_store   = \WC_Data_Store::load('product-variable');
            $variation_id =  $data_store->find_matching_product_variation($product, $singleVariationCombination);
        }

        return $variation_id;
    }

    /*
     * Below we will be calling get_variation_attributes function which makes a call to get_attributes.
     * get_variation_attributes uses name of attribute as a key in the returned array. We need to change the
     * behavior and we need to have a 'slug' as a key in the array returned by get_variation_attributes.
     * Therefore, we'll change the array returned by get_attributes. We'll keep name as slug in
     * get_attributes array
     */
    public static function modifyProductAttributeNames($attributes)
    {
        if (is_array($attributes)) {
            if (version_compare(WC_VERSION, '3.0.0', '<')) {
                foreach ($attributes as $attributeSlug => $attributeData) {
                    $attributes[$attributeSlug]['name'] = $attributeSlug;
                    unset($attributeData);
                }
            } else {
                //For 3.0 and greater
                foreach ($attributes as $attributeSlug => $attributeObject) {
                    $attributeData = $attributeObject->get_data();
                    if ($attributeData['is_variation']) {
                        $attributes[$attributeSlug] = $attributeObject->get_slugs();
                    } else {
                        unset($attributes[$attributeSlug]);
                    }
                }
            }
        }

        return $attributes;
    }

    /**
    * This function is used to fetch the details of variable product with corresponding variation.
    * @param object $variation_product variations product details.
    * @param int $variation_id Id of variation to be fetched
    * @param array $variation_data the variation combination of attributes.
    * @param array $found_products products found with similar data as searched
    * @param array $excluded_products product variation which are already included .
    * @return array $found_products variable product details of corresponding variation.
    */
    private static function addVariationInSearchResults($variation_product, $variation_id, $variation_data, $found_products, $excluded_products)
    {
        $return = false;
        if (!is_array($variation_data)) {
            $return = true;
        } else {
            // Return Products if value of  any variation attribute is empty
            foreach ($variation_data as $variation) {
                if (empty($variation)) {
                    $return = true;
                }
            }
        }

        if ($return) {
            return $found_products;
        }

        //Creating md5 hash to check whether current combinations of attributes already exists in the array or not.
        $variation_hash = md5($variation_id.'_'.implode('_', $variation_data));

        if (in_array($variation_hash, $excluded_products)) {
            return $found_products;
        }

        //Check if this variation already exists in the array
        if (!isset($found_products[$variation_hash])) {
            $img_url = self::getImgUrl($variation_id, $variation_product);
            $productID = self::getParentID($variation_product);
                $found_products[$variation_hash] = array(
                'product_id' => $productID,
                'variation_id' => $variation_id,
                'product_type' => 'variation',
                'price' => $variation_product->get_price(),
                'formatted_name' => self::getVariationName($variation_product),
                'variation_attributes' => $variation_data,
                'variation_string' => self::getVariations($variation_data, $variation_id),
                'product_title' => $variation_product->get_title(),
                'url' => admin_url("/post.php?post={$productID}&action=edit"),
                'product_image' => $img_url,
                'sku' => $variation_product->get_sku(),
                );
        }

        return $found_products;
    }

    /**
    * Get the Name of variation Product.
    * @param  object $variationProduct variation product object
    */
    private static function getVariationName($variationProduct)
    {

        if (version_compare(WC_VERSION, '3.0.0', '>=')) {
            return rawurldecode(self::generateVariationName($variationProduct));
        }
            
            return rawurldecode($variationProduct->get_formatted_name());
    }

    /**
     * Generates Variation Title for WC greater than 3.0
     *
     * This is the copy of WooCommerce's WC_Product_Variation_Data_Store_CPT::generate_product_title(). Because
     * direct call to this method is not possible as it is a protected method, we are creating a copy of it in plugin
     *
     * @param  object $product variation product object
     * @return string          title of variation
     */
    private static function generateVariationName($product)
    {
        $include_attribute_names = false;
        $attributes = (array) $product->get_attributes();

        // Determine whether to include attribute names through counting the number of one-word attribute values.
        $one_word_attributes = 0;
        foreach ($attributes as $name => $value) {
            if (false === strpos($value, '-')) {
                ++$one_word_attributes;
            }
            if ($one_word_attributes > 1) {
                $include_attribute_names = true;
                break;
            }
            unset($name);
        }

        $include_attribute_names = apply_filters('woocommerce_product_variation_title_include_attribute_names', $include_attribute_names, $product);
        $title_base_text         = get_post_field('post_title', $product->get_parent_id());
        $title_attributes_text   = wc_get_formatted_variation($product, true, $include_attribute_names);
        $separator               = ! empty($title_attributes_text) ? ' &ndash; ' : '';

        return apply_filters(
            'woocommerce_product_variation_title',
            $title_base_text . $separator . $title_attributes_text,
            $product,
            $title_base_text,
            $title_attributes_text
        );
    }

    /**
    * Get Parent product Id for variation id
    * @param object $variation_product Variation Id
    * @return int $productID  Parent product Id
    */
    private static function getParentID($variation_product)
    {
        if (version_compare(WC_VERSION, '3.0.0', '<')) {
            $productID = $variation_product->parent->id;
        } else {
            $productID = $variation_product->get_parent_id();
        }
        return $productID;
    }

    /**
    * Gets the Image URL of Product.
    * @param int $variation_id Variation Id
    * @param object $variation_product Variation of Product
    */
    private static function getImgUrl($variation_id, $variation_product)
    {
        if (isset($variation_id) && $variation_id != '') {
            $img_url = wp_get_attachment_url(get_post_thumbnail_id($variation_id));
        }
        if (!$img_url || $img_url == '') {
            $img_url = wp_get_attachment_url(get_post_thumbnail_id(self::getParentID($variation_product)));
        }
        if (!$img_url || $img_url == '') {
            $img_url = WC()->plugin_url().'/assets/images/placeholder.png';
        }
    }

    /**
    * This function gets the variations with the combinations of the attributes.
    * @param array $arrays array of the attributes.
    * @return array $result Variation combinations of all the attributes.
    */
    private static function getArrayCombinations($arrays)
    {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }

        return $result;
    }

    /*
     * This function is used to display data on enquiry or quote edit page
     * Adds the required meta boxes and feilds.
     */
    public function createDashboardQuotation()
    {
        global $quoteup_admin_menu;
        ?>
        <div class="wrap">
            <h1>
        <?php
            echo esc_html_e('Create Quotation', QUOTEUP_TEXT_DOMAIN);
        ?>

            </h1>
            <!-- <form name="editQuoteDetailForm" method="post"> -->
                <input type="hidden" name="action" value="editQuoteDetail" />
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">

                        <div id="post-body-content">
                            <!-- <p>Admin Page for Editing Product Enquiry Detail.</p> -->
                            <?php
                            $this->displayLanguageSelector();
                            ?>
                        </div>
                        <div id="postbox-container-2" class="postbox-container">
        <?php
        add_meta_box('quoteProducts', __('Product Details', QUOTEUP_TEXT_DOMAIN), array($this, 'productDetailsSection'), $quoteup_admin_menu, 'normal');

        add_meta_box('saveSendQuote', __('Send Quote To :', QUOTEUP_TEXT_DOMAIN), array($this, 'sendQuote', ), $quoteup_admin_menu, 'normal');
        do_meta_boxes($quoteup_admin_menu, 'normal', '');
        ?>
                        </div>
                    </div>
                </div>
            <!-- </form> -->
        </div>
                <?php
            
        //  Beacon hook will display on create quote page.
        do_action('quoteup_pep_backend_page');
    }

    /**
    * Template for Displaying the Products details section.
    */
    public function productDetailsSection()
    {
        $img = QUOTEUP_PLUGIN_URL.'/images/table_header.png';
        ?>
        <div class="productDetailsSection">
            <table class='admin-quote-table generated_for_desktop' id="Quotation" cellspacing='0'>
                <thead class="wdm-quote-head">
                    <tr>
                        <th class="quote-product-remove"></th>
                        <th class="quote-product-image">
                            <img src= '<?php echo $img; ?>' class='wdm-prod-img wdm-prod-head-img'/>
                        </th>
                        <th class="quote-product-title"> <?php _e('Product', QUOTEUP_TEXT_DOMAIN); ?> </th>
                        <th class="quote-product-variation"> <?php _e('Variations', QUOTEUP_TEXT_DOMAIN); ?> </th>
                        <th class="quote-product-sku"> <?php _e('SKU', QUOTEUP_TEXT_DOMAIN); ?> </th>
                        <th class="quote-product-sale-price"> <?php _e('Price', QUOTEUP_TEXT_DOMAIN); ?> </th>
                        <th class="quote-product-new-price"> <?php _e('New Price', QUOTEUP_TEXT_DOMAIN); ?> </th>
                        <th class="quote-product-qty"> <?php _e('Quantity', QUOTEUP_TEXT_DOMAIN); ?> </th>
                        <th class="quote-product-total"> <?php _e('Total', QUOTEUP_TEXT_DOMAIN); ?> </th>
                    </tr>                                
                </thead>
                <tbody class="wdmpe-detailtbl-content">
                    <tr class="quoteup-no-product">
                        <td colspan="9" style="text-align: center;"><?php _e('No Products Selected', QUOTEUP_TEXT_DOMAIN); ?></td>
                    </tr>                   
                 </tbody>
                    <tfoot>
                        <td colspan="7"></td>
                        <td><?php _e('Total', QUOTEUP_TEXT_DOMAIN); ?></td>
                        <td class="quote-final-total" id="amount_total">
                            <div class="quote-final-og-total no-cost-change"><s><?php echo wc_price('0'); ?></s></div>
                            <div class="quote-final-new-total"><?php echo wc_price('0'); ?></div>
                            <div class="quote-final-total-change-percetange percentage-diff no-cost-change">(0%)</div>
                        
                        
                        </td>
                    </tfoot>
            </table>
            <input type="hidden" name="database-amount" id="database-amount" data-old-value="0" value="0">
            <div>
            <?php
                getProductsSelection();
            ?>
                <div class="quote-expiration-date">
                    <div class='wdm-user-expiration-date'>
                        <input type='hidden' name='expiration_date' class="expiration_date_hidden" value=''>
                            <label class="expiration-date left-label"><?php _e('Expiration Date', QUOTEUP_TEXT_DOMAIN) ?> : </label>
                        <input type='text' value='' class='wdm-input-expiration-date'  readonly  required>
                                    
                    </div>
                </div>
            </div>    

            <div class="quote-related-options">
                <div class ="show-price-option">
                    <!-- Show Old Price in Quotation checkbox -->
                    <div class="quoteup-show-price-wrapper">
                        <input id="show_price" class="wdm-checkbox" type="checkbox" name="show_price" value="1" />
                        <label for="show_price"><?php _e('Show Old Price in Quotation', QUOTEUP_TEXT_DOMAIN) ?></label>
                    </div>

                    <!-- Show Price Difference in Quotation checkbox -->
                    <div class="quoteup-show-price-diff-wrapper">
                        <input id="show_price_diff" class="wdm-checkbox" type="checkbox" name="show_price_diff" value="1" />
                        <label for="show_price_diff"><?php _e('Show Price Difference in Quotation', QUOTEUP_TEXT_DOMAIN) ?></label><br />
                    </div>
                </div>
            </div>
        </div>
            <?php
    }

    /**
     * This function is used to display quote form.
     * All the fields on MPE will be displayed in form.
     * We have one extra attribute in array now to display custom field on quote form.
     * include_in_quote_form
     * if 'include_in_quote_form' is not set then field will be displayed
     * if 'include_in_quote_form' is set to 'no' then field will not be displayed
     * if 'include_in_quote_form' is set to 'yes' then field will be displayed.
     */
    public function sendQuote()
    {
        $form_data = quoteupSettings();
        ?>
        <div class='wdm-enquiry-form'>
            <?php
            if (!isset($form_data['enquiry_form']) || $form_data['enquiry_form'] == 'default' || $form_data['enquiry_form'] == '') {
                $this->getDefaultDashboardForm();
            } else {
                $formID = $form_data['custom_formId'];
                if (isset($formID) && !empty($formID)) {
                    echo do_shortcode("[wisdmform form_id=$formID]");
                }
            }
            ?>
        </div>
        <div class='form_input btn_div clearfix'>
            <input type='button' value='<?php _e('Create Quotation', QUOTEUP_TEXT_DOMAIN) ?>' name='btnSave'  id='btnQuoteSave' class='quote-save button button-primary'>
                <span class='load-send-quote-ajax'></span>
            <input type='button' value='<?php _e('Send Quotation', QUOTEUP_TEXT_DOMAIN) ?>' name='btnSend'  id='btnQuoteSend' class='quote-send button button-primary'>
        <?php
        global $quoteup;
        $quoteup->quoteDetailsEdit->pdfPreviewModal();
        $quoteup->quoteDetailsEdit->addMessageQuoteModal();
        echo "</div>";
    }

    public function getDefaultDashboardForm()
    {
        $ajax_nonce = wp_create_nonce('quoteup');
        $url = admin_url();
        ?>
        <form method='post' id='frm_dashboaard_quote' name='frm_dashboaard_quote' class='quoteup-quote-form' >
                <input type='hidden' name='quote_ajax_nonce' id='quote_ajax_nonce' value='<?php echo $ajax_nonce; ?>'>
                <input type='hidden' name='submit_value' id='submit_value'>
                <input type='hidden' name='site_url' id='site_url' value='<?php echo $url; ?>'>
                <input type='hidden' name='tried' id='tried' value='yes' />
                <div id='wdm_nonce_error'>
                    <div  class='wdmquoteup-err-display'>
                        <span class='wdm-quoteupicon wdm-quoteupicon-exclamation-circle'></span><?php _e('Unauthorized enquiry', QUOTEUP_TEXT_DOMAIN) ?>
                    </div>
                </div>
        <?php
        do_action('quote_add_custom_field_in_form');
        ?>
                <div id="text"></div>
                <div class='form-errors-wrap mpe_form_input' id='wdm-quoteupform-error'>
                    <div class='mpe-left' style='height: 1px;'></div>
                    <div class='mpe-right form-errors'>
                        <ul class='error-list'>
                        </ul>
                    </div>
                </div>
            </form>
        <?php
    }

    /**
    * Update the Enquiry Details Table with the new Enquiry Details.
    * @param string $name Customer Name in Enquiry
    * @param string $email Customer email
    * @param string $phone Customer phone no.
    * @param string $subject subject to be sent for mail
    * @param string $address Customer address
    * @param array $product_details Product details in Quote
    * @param string $msg
    * @param date $date current date
    * @param date $dateField date field in Quote
    */
    public function updateEnquiryDetailsTable($name, $email, $phone, $subject, $address, $product_details, $msg, $date, $dateField)
    {
        global $wpdb;
        $tbl = getEnquiryDetailsTable();
        if (isset($_POST['globalEnquiryID']) && $_POST['globalEnquiryID'] != 0) {
            $wpdb->update(
                $tbl,
                array(
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
                'subject' => $subject,
                'enquiry_ip' => $address,
                'product_details' => serialize($product_details),
                'message' => $msg,
                'enquiry_date' => $date,
                'date_field' => $dateField,
                ),
                array('enquiry_id' => $_POST['globalEnquiryID']),
                array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                ),
                array('%d')
            );
            $enquiry_id = $_POST['globalEnquiryID'];
            $wpdb->delete(getEnquiryProductsTable(), array('enquiry_id' => $enquiry_id), array('%d'));
        } else {
            $wpdb->insert(
                $tbl,
                array(
                'name' => $name,
                'email' => $email,
                'phone_number' => $phone,
                'subject' => $subject,
                'enquiry_ip' => $address,
                'product_details' => serialize($product_details),
                'message' => $msg,
                'enquiry_date' => $date,
                'date_field' => $dateField,
                ),
                array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                )
            );
            $enquiry_id = $wpdb->insert_id;
        }

        //Update product details in enquiry products table after version 5.0.0
        //After some versions only this table will be used
        updateProductDetails($enquiry_id, $product_details);

        return $enquiry_id;
    }

    /**
    * Gets the variation details value in key value form.
    * @param int $variation_id Variation Id
    * @param array $rawVariationDetails Variation details
    */
    public function getVariationDetailsKeyValue($variation_id, $rawVariationDetails)
    {
        $variationDetails = array();
        if (!empty($variation_id) && !empty($rawVariationDetails)) {
            foreach ($rawVariationDetails as $value) {
                $detectVariationDetails = array_map('trim', explode(':', $value));
                if (!empty($detectVariationDetails)) {
                    //$detectVariationDetails[0] has attribute name
                    //$detectVariationDetails[1] has attribute value
                    $variationDetails[$detectVariationDetails[0]] = $detectVariationDetails[1];
                }
            }
        }

        return $variationDetails;
    }

    /**
    * Gets all the post data from the page.
    * Get the Product details, Customer details,
    * Gets the PDF options .
    * Save the Quotation in db.
    * Generates the Pdf and save in uploads folder.
    */
    public function saveQuote()
    {
        $data = $_POST;
        global $quoteup;
        $name = wp_kses($_POST[ 'custname' ], array());
        $email = filter_var($_POST[ 'txtemail' ], FILTER_SANITIZE_EMAIL);
        $phone = quoteupPhoneNumber();
        $dateField = quoteupDateField();
        $subject = '';
        $msg = '';
        $quoteProducts = isset($_POST['quoteProductsData']) ? $_POST['quoteProductsData'] : '';
        $product_details = array();
        $arrayids = array();
        $newprice = array();
        $quantity = array();
        $oldPrice = array();
        $variationIDArray = array();
        $variationsArray = array();
        $variationIndexInEnquiry = array();

        $counter = 0;
        $quoteProducts = json_decode(stripcslashes($quoteProducts));
        $quoteProducts = (array)$quoteProducts;
        $validMedia = validateAttachField($quoteup);

        foreach ($quoteProducts as $key => $value) {
            $value = (array)$value;
            $prod = array();
            $product_id = $value['productID'];
            $variation_id = isset($value['variationID']) ? $value['variationID'] : '';
            $title = get_the_title($product_id);
            $rawVariationDetails = $value['variationDetails'];
            $variationDetails = $this->getVariationDetailsKeyValue($variation_id, $rawVariationDetails);
            $address = getEnquiryIP();
            $variationDetailsForArray = array();
            $type = 'Y-m-d H:i:s';
            $date = current_time($type);
            if ($variation_id != '') {
                $product = wc_get_product($variation_id);
                $sku = $product->get_sku();
                $img = wp_get_attachment_url(get_post_thumbnail_id($variation_id));
                $img_url = getImgUrl($img, $product_id);
                $variationDetailsForArray = getVariationDetailsForArray($variationDetails);
                // end of For Save quotation data in enquiry_quotation table
            } else {
                $product = wc_get_product($product_id);
                $sku = $product->get_sku();
                $img_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));
            }
            array_push($arrayids, $product_id);
            array_push($newprice, $value['productPrice']);
            array_push($oldPrice, $value['salePrice']);
            array_push($quantity, $value['productQty']);
            array_push($variationIDArray, $variation_id);
            array_push($variationsArray, $variationDetailsForArray);
            array_push($variationIndexInEnquiry, $counter);
            $prod = array(
            'id' => $product_id,
            'title' => $title,
            'price' => $value['salePrice'],
            'quantity' => $value['productQty'],
            'img' => $img_url,
            'remark' => '',
            'sku' => $sku,
            'variation_id' => $variation_id,
            'variation' => $variationDetails,
            'author_email' => '',
            );
            $prod = apply_filters('wdm_filter_quote_product_data', $prod, $data);
            $product_details[$counter] = $prod;
            ++$counter;
            unset($key);
        }

        do_action('quoteup_validate_on_new_quote_creation', $arrayids);

        $this->checkStock($arrayids, $quantity, $variationIDArray, $variationsArray);

        $enquiry_id = $this->updateEnquiryDetailsTable($name, $email, $phone, $subject, $address, $product_details, $msg, $date, $dateField);
        do_action('create_quote_form_entry_added_in_db', $enquiry_id, $data);
        do_action('quoteup_add_dashboard_custom_field_in_db', $enquiry_id, $data);
        $this->addQuoteFlagTOMeta($enquiry_id);
        $language = $this->getLanguage();
        //add Locale in enquiry meta if WPML is activated
        if (quoteupIsWpmlActive()) {
            addLanguageToEnquiryMeta($enquiry_id, 'enquiry_lang_code', $language);
            addLanguageToEnquiryMeta($enquiry_id, 'quotation_lang_code', $language);
        }
        //End of locale insertion

        deleteDirectoryIfExists($enquiry_id);
        uploadAttachedFile($quoteup, $validMedia, $enquiry_id);

        $show_price = $this->getShowPrice();
        $show_pricePDF = $this->getShowPricePDF();
        $show_price_diff = $this->getShowPriceDiff();
        $quotationData = array(
            'enquiry_id' => $enquiry_id,
            'cname' => $name,
            'email' => $email,
            'id' => $arrayids,
            'newprice' => $newprice,
            'quantity' => $quantity,
            'old-price' => $oldPrice,
            'variations_id' => $variationIDArray,
            'variations' => $variationsArray,
            'show-price' => $show_price,
            'show-price-diff' => $show_price_diff,
            'variation_index_in_enquiry' => $variationIndexInEnquiry,
            'security' => $_POST['security'],
            'language' => $language,
            'expiration-date' => $_POST['expiration-date'],
            'previous_enquiry_id' => $_POST['globalEnquiryID'],
            'source' => 'dashboard',
        );

        $PDFData = array(
            'enquiry_id' => $enquiry_id,
            'show-price' => $show_pricePDF,
            'show-price-diff' => $show_price_diff,
            'language' => $language,
        );

        QuoteupQuotesEdit::saveQuotation($quotationData);
        $form_data = quoteupSettings();
        $pdfDisplay = isset($form_data['enable_disable_quote_pdf'])?$form_data['enable_disable_quote_pdf']:1;
        if ($pdfDisplay == 1) {
            QuoteupGeneratePdf::generatePdf($PDFData);
        }
        echo json_encode(
            array(
                'enquiry_id'     => $enquiry_id,
                'saveString'    => 'Saved Successfully.',
            )
        );
        die();
    }

    /**
    * Returns the language of the site.
    * @return language of post
    */
    public function getLanguage()
    {
        return isset($_POST['language']) ? $_POST['language'] : '';
    }

    /**
    * Gets the Show Price checkbox ,that is for Showing Price in quote.
    * @return show price checkbox
    */
    public function getShowPrice()
    {
        return $_POST[ 'show-price' ] == 'yes' ? 'yes' : 'no';
    }

    /**
     * Check whether price differnce should be shown or not.
     *
     * @return  string  Return 'yes' if price difference should be shown,
     *                  'no' otherwise.
     */
    public function getShowPriceDiff()
    {
        return '1' == $_POST['show-price-diff'] || 'yes' == $_POST[ 'show-price-diff' ]  ? 'yes' : 'no';
    }


    /**
    * Gets the Show Price checkbox ,that is for Showing Price in PDF.
    * @return show price checkbox
    */
    public function getShowPricePDF()
    {
        return $_POST[ 'show-price' ] == 'yes' ? '1' : '0';
    }

    /**
    * Checks the stock of Products in quote.
    * @param array $arrayids empty at first
    * @param array $quantity Quantity of Products
    * @param array $variationIDArray variation Id Products.
    * @param array $variationsArray Array of variations.
    */
    public function checkStock($arrayids, $quantity, $variationIDArray, $variationsArray)
    {
        if (!QuoteupQuotesEdit::quoteStockManagementCheck($arrayids, $quantity, $variationIDArray, $variationsArray)) {
            throw new Exception('Product Cannot be added in quotation', 1);
        }
    }

    /**
    * Update Meta value of admin created to 1.
    * @param int $enquiry_id Enquiry Id
    */
    public function addQuoteFlagTOMeta($enquiry_id)
    {
        global $wpdb;
        $metaTbl = getEnquiryMetaTable();
        if (isset($_POST['globalEnquiryID']) && $_POST['globalEnquiryID'] != 0) {
            $wpdb->update(
                $metaTbl,
                array(
                'meta_value' => '1',
                ),
                array(
                    'enquiry_id' => $_POST['globalEnquiryID'],
                    'meta_key' => '_admin_quote_created',
                    ),
                array(
                '%s',
                ),
                array('%d', '%s')
            );
        } else {
            $wpdb->insert(
                $metaTbl,
                array(
                'enquiry_id' => $enquiry_id,
                'meta_key' => '_admin_quote_created',
                'meta_value' => '1',
                ),
                array(
                '%d',
                '%s',
                '%d',
                )
            );
        }
    }
}

$this->quoteCreateQuotation = QuoteupCreateDashboardQuotation::getInstance();
