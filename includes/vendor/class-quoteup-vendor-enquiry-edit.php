<?php

namespace Includes\Admin\Vendor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * This class is responsible for changing the content on the
 * Enquiry Edit page.
 */
class QuoteupVendorEnquiryEdit
{
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }
        
        return static::$instance;
    }

    public function __construct()
    {
        if (!quoteupIsVendorCompEnabled()) {
            return;
        }

        // If not the quote edit page, then return.
        if (!defined('DOING_AJAX') && (!isset($_GET['page']) || 'quoteup-details-edit' != $_GET['page'])) {
            return;
        }
 
        $settings = quoteupSettings();

        // If 'quotation system' is disabled.
        if (isset($settings['enable_disable_quote']) && $settings['enable_disable_quote'] == 1) {
            return;
        }

        // Functions added to hooks required by an admin as well as the vendor.
        add_filter('quoteup_quotes_edit_enquiry_products', array($this, 'mergeVendorAddedProducts'), 20, 2);
        add_action('quoteup_enquiry_edit_after_sku_th', array($this, 'addVendorStatusTableHead'));
        add_action('quoteup_enquiry_edit_after_sku_td', array($this, 'addVendorStatusTableData'), 20, 2);
        add_filter('quoteup_enquiry_edit_product_row_class', array($this, 'addClassToProductRow'), 10, 3);
        add_filter('quoteup_enquiry_edit_tmpl_product_row_class', array($this, 'addClassToProductRow'), 10);
        add_action('quoteup_enquiry_edit_quote_options_start', array($this, 'showQuoteSendDeadlineField'));
        add_action('admin_enqueue_scripts', array($this, 'loadEnquiryEditPageScripts'));
        add_filter('quoteup_enq_quote_products', array($this, 'getProductsForEnquiry'), 10, 2);
        
        // Functions added to hooks required by an admin.
        if (current_user_can('manage_options')) {
            add_filter('quoteup_should_fetch_enquiry_products', array($this, 'shouldFetchEnquiryProducts'), 20, 2);
            add_action('wp_ajax_quoteup_save_quote_send_dl', array($this, 'quoteupSaveQuoteSendDeadline'));
            add_action('quoteup_after_quote_send_deadline_set', array($this, 'sendInitialQuoteDlEmail'), 20, 2);
            add_action('quoteup_before_gen_quote_btn', array($this, 'displaySaveButton'));
            add_action('wp_ajax_quoteup_save_admin_products_data', array($this, 'quoteupSaveAdminProductsData'));
            add_action('quoteup_after_quote_generated', array($this, 'removeReminderCrons'));
            // add_filter('quoteup_json_search_found_products', array($this, 'addVendorStatusElem'));
            add_action('quoteup_enquiry_edit_tmpl_after_sku_th', array($this, 'vendorStatusSecInQuoteEditTmpl'));
        } else if (quoteupIsCurrentLoggedInUserVendor()) {
            // Add following filters/ actions when current user is vendor and not an admin.
            // Modify query which is used to fetch the products data on enquiry edit page.
            add_filter('quoteup_enquiry_quote_products_query', array($this, 'modifyProductsQueryOnEditPage'));
            add_filter('quoteup_should_disable_show_price_opt_edit_page', array($this, 'disableShowPriceOptionOnEditPage'), 20);
            add_filter('quoteup_should_disable_exp_date_opt_edit_page', array($this, 'disableExpDateOnEditPage'), 20);
            add_filter('quoteup_should_disable_quote_send_dl_opt_edit_page', array($this, 'disableQuoteSendDeadlineOnEditPage'), 20);
            add_action('quoteup_after_quote_rel_btns', array($this, 'addSavePriceBtn'));
            add_action('wp_ajax_quoteup_save_vendor_quote_price', array($this, 'saveVendorQuotePrice'));
            add_filter('quoteup_customer_meta_data_query', array($this, 'appendCondToCustomerMetaDataQuery'));
            add_filter('quoteup_customer_default_data_to_show', array($this, 'returnCustomerDefaultDataToShow'));
            add_filter('quoteup_add_remove_link_enq_edit', array($this, 'hideRemoveBtn'));
            add_filter('quoteup_order_id_ass_with_quote', array($this, 'returnVendorSubOrderIdOfQuote'));
            add_filter('quote_allow_meta_fields_enq_edit', array($this, 'returnQuoteAllowMetaFields'));
            add_filter('quoteup_enb_reply_in_enq_rem_msg_sec', array($this, 'shouldEnbReplyInEnqRemMsgSec'));
        }
    }

    /**
     * Callback function for 'quoteup_enquiry_edit_after_sku_th' hook.
     *
     * Add table head after 'SKU' table head in 'Product Deatils' section
     * in Enquiry Edit page.
     *
     */
    public function addVendorStatusTableHead()
    {
        if (quoteupIsCurrentUserHavingManageOptionsCap()) :
            ?>
            <th class="wdmpe-detailtbl-head-item item-head-vendor-status">
                <?php echo __('Vendor Status', QUOTEUP_TEXT_DOMAIN); ?>
            </th>
            <?php
        elseif (quoteupIsCurrentLoggedInUserVendor()) :
            ?>
            <th class="wdmpe-detailtbl-head-item item-head-vendor-status">
                <?php
                echo __('Status', QUOTEUP_TEXT_DOMAIN);
                ?>
            </th>
            <?php
        endif;
        do_action('quoteup_enquiry_edit_after_vendor_status_th');
    }

    /**
     * Function for inserting data in enquiry_quotation table.
     * Checks the Nonce for quotation and the user capabilties
     * And then calls function to save quotation.
     */
    public static function quoteupSaveQuoteSendDeadline()
    {
        $quotationData = $_POST;
        if (!wp_verify_nonce($quotationData['security'], 'quoteup')) {
            die('SECURITY_ISSUE');
        }

        $capability = apply_filters('quoteup_quote_send_deadline_capability', 'manage_options');

        if (! current_user_can($capability)) {
            die('SECURITY_ISSUE');
        }

        // validate quote send deadline.
        $enquiryId = filter_var($_POST['enquiry_id']);
        $deadline  = filter_var($_POST['quote_send_deadline']);
        do_action('quoteup_validate_quote_send_deadline', $enquiryId, $deadline);

        static::setQuoteSendDeadline($enquiryId, $deadline);
        echo 'Saved Successfully';
        wp_die();
    }

    /**
     * Save products prices when vendor clicks on 'Save Price' button on the
     * enquiry edit page.
     */
    public function saveVendorQuotePrice()
    {
        $quotationData = $_POST;
        if (!wp_verify_nonce($quotationData['security'], 'quoteup')) {
            die('SECURITY_ISSUE');
        }

        $shouldSavePrice = quoteupIsCurrentLoggedInUserVendor();
        $shouldSavePrice = apply_filters('quoteup_allow_vendor_save_price', $shouldSavePrice);

        if (! $shouldSavePrice) {
            die('SECURITY_ISSUE');
        }

        self::saveProductsDataInQuotationTbl($quotationData, true);
        die();
    }

    /**
     * Save products data when admin clicks on 'Save' button on the enquiry
     * edit page.
     */
    public function quoteupSaveAdminProductsData()
    {
        $quotationData = $_POST;
        if (!wp_verify_nonce($quotationData['security'], 'quoteup')) {
            die('SECURITY_ISSUE');
        }

        $capability = 'manage_options';
        $capability = apply_filters('quoteup_save_quote_products_data_cap', $capability);

        if (! current_user_can($capability)) {
            die('SECURITY_ISSUE');
        }

        self::saveProductsDataInQuotationTbl($quotationData, false);
        die();
    }

    /**
     * Callback for 'quoteup_after_quote_generated' action hook.
     *
     * Remove vendor reminder cron and admin reminder crons.
     */
    public function removeReminderCrons($enquiryId)
    {
        $quoteupCron = QuoteupVendorCrons::getInstance();
        $quoteupCron->clearCrons($enquiryId);
    }

    /**
     * Callback for 'quoteup_json_search_found_products' hook.
     *
     * Add 'vendor status' key in products data. This key is used to add
     * 'Vendor Status' column on the enquiry edit page for newly added
     * products.
     */
    public function addVendorStatusElem($foundProducts)
    {
        $vendorStatus = __('Vendor Status', QUOTEUP_TEXT_DOMAIN);
        $vendorStatus = '<th class="wdmpe-detailtbl-head-item item-head-vendor-status">' . $vendorStatus .'</th>';

        foreach ($foundProducts as $index => $product) {
            $foundProducts[$index]['vendor_status'] = $vendorStatus;
            unset($product);
        }
        return $foundProducts;
    }

    public function vendorStatusSecInQuoteEditTmpl()
    {
        ?>
        <td class="wdmpe-detailtbl-content-item item-content-vendor-status">
            <p><?php echo __('Not Updated'); ?></p>
        </td>
        <?php
    }

    public function getProductsForEnquiry($products, $enquiryId)
    {
        $isVendor = quoteupIsCurrentLoggedInUserVendor();

        if ($isVendor) {
            $products = quoteupGetProductsForVendorFromEnqQuote($enquiryId);
        } else {
            $products = quoteupGetProductsForAdminFromEnqQuote($enquiryId);
        }

        return $products;
    }

    public function saveProductsDataInQuotationTbl($quotationData, $isVendorUpdate = false)
    {
        $enquiry_id         = filter_var($quotationData[ 'enquiry_id' ], FILTER_SANITIZE_NUMBER_INT);
        $product_id         = $quotationData['id'];
        $newprice           = $quotationData['newprice'];
        $quantity           = $quotationData['quantity'];
        $oldprice           = $quotationData['old-price'];
        $variation_id       = $quotationData['variations_id'];
        $variation_details  = $quotationData['variations'];
        $show_price         = $quotationData['show-price'];
        $variation_index_in_enquiry = $quotationData['variation_index_in_enquiry'];
        $size               = sizeof($product_id);
        $loggedInVendorId   = get_current_user_id();

        // This function is used to check if total quantity is greater than 0.
        \Includes\Admin\QuoteupQuotesEdit::checkValidQuantity($size, $quantity);
        try {
            if (!\Includes\Admin\QuoteupQuotesEdit::quoteStockManagementCheck($product_id, $quantity, $variation_id, $variation_details)) {
                throw new Exception('Product Cannot be added in quotation', 1);
            }

            for ($i = 0; $i < $size; ++$i) {
                $productAvailable = wc_get_product($product_id[ $i ]);
                if ($productAvailable == '') {
                    continue;
                }
                $variation_details[ $i ] = \Includes\Admin\QuoteupQuotesEdit::getQuoteVariationDetails($variation_details[$i]);
                $title = get_the_title($product_id[ $i ]);
                $productData = array(
                    'enquiry_id'    => $enquiry_id,
                    'product_id'    => $product_id[ $i ],
                    'product_title' => $title,
                    'newprice'      => $newprice[ $i ],
                    'quantity'      => $quantity[ $i ],
                    'oldprice'      => $oldprice[ $i ],
                    'variation_id'  => $variation_id[$i],
                    'variation'     => serialize($variation_details[ $i ]),
                    'show_price'    => $show_price,
                    'variation_index_in_enquiry' => $variation_index_in_enquiry[$i],
                    'vendor_id'     => $loggedInVendorId,
                );

                // If record exists in 'enquiry_quotation' table for a
                // particular enquiry and product pair, then update the quantity
                // and new price data.
                if (quoteupIsDataExistInQuotationTable($productData)) {
                    quoteupUpdateQuotationTblRec($productData, $isVendorUpdate);
                } else {
                    // Add/ insert newly added product data.
                    quoteupAddProductDataInQuotationTbl($productData, $isVendorUpdate);
                }
            }

            if (!$isVendorUpdate) {
                quoteupUpdateRemovedColInEnquiryTbl($enquiry_id, $product_id, $variation_id);
            }

            // Set Order Id to NULL, so that it opens up a communication channel after rejecting the Quote
            // \Includes\Admin\QuoteupQuotesEdit::setOrderID($enquiry_id);
            // Don't translate this string here. It's translation is handled in js
            if (!isset($quotationData['source']) || $quotationData['source'] != 'dashboard') {
                echo 'Saved Successfully.';
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        wp_die();
    }

    /**
     * This function is used to set and update quote send deadline in database.
     * @param [int]  $enquiry_id      [Enquiry ID]
     * @param [date] $deadline        [Quote Send Deadline]
     */
    public static function setQuoteSendDeadline($enquiryId, $deadline)
    {
        global $wpdb;
        $date                = date_create_from_format('Y-m-d', $deadline);
        $mysqlDatetime       = date_format($date, 'Y-m-d');
        $isDeadlineSame      = false;
        $enquiryDetailsTable = getEnquiryDetailsTable();

        do_action('quoteup_before_quote_send_deadline_set', $enquiryId, $deadline);

        $selectQuery = "SELECT enquiry_id FROM {$enquiryDetailsTable} WHERE enquiry_id=%d AND quote_send_deadline=%s";
        $selectQuery = $wpdb->prepare($selectQuery, $enquiryId, $deadline);
        $res = $wpdb->get_var($selectQuery);

        if (!empty($res)) {
            $isDeadlineSame = true;
            return;
        }

        $wpdb->update(
            $enquiryDetailsTable,
            array(
                'quote_send_deadline' => $mysqlDatetime,
            ),
            array(
                'enquiry_id' => $enquiryId,
            )
        );

        do_action('quoteup_after_quote_send_deadline_set', $enquiryId, $deadline);
    }

    /**
     * Callback function for 'quoteup_enquiry_edit_product_row_class' hook.
     *
     * Add class to product row in 'Product Details' section in Enquiry Edit
     * page.
     * Change the background-color of the product row if product price has not
     * been updated by the vendor.
     */
    public function addClassToProductRow($productRowClass, $singleProduct = array(), $enquiryId = 0)
    {
        $class                = 'vendor-price-not-updated-row';
        $didVendorUpdatePrice = isset($singleProduct['vendor_updated']) ? true : false;

        if ($didVendorUpdatePrice) {
            $class = 'vendor-price-updated-row';
        } else if (quoteupHasQuotationBeenCreated($enquiryId)) {
            $class = 'quote-generated';
        }

        return $productRowClass . ' ' . $class;
    }

    /**
     * Callback function for 'quoteup_enquiry_edit_after_sku_td' hook.
     *
     * Add table data (content) after 'SKU' table data (content) in 'Product
     * Details' section in Enquiry Edit page.
     */
    public function addVendorStatusTableData($enquiryProductData, $enquiryId)
    {
        ?>
        <td class="wdmpe-detailtbl-content-item item-content-vendor-status">
            <p>
                <?php
                $didVendorUpdatePrice    = isset($enquiryProductData['vendor_updated']) ? true : false;
                $hasQuotationBeenCreated = quoteupHasQuotationBeenCreated($enquiryId);

                if ($hasQuotationBeenCreated || $didVendorUpdatePrice) {
                    $vendorStatus = __('Updated', QUOTEUP_TEXT_DOMAIN);
                } else {
                    $vendorStatus = __('Not Updated', QUOTEUP_TEXT_DOMAIN);
                }

                echo $vendorStatus;
                ?>
            </p>
        </td>
        <?php
    }

    /**
     * Callback for 'admin_enqueue_scripts' hook.
     *
     * Load required scripts and styles on the enquiry edit page.
     */
    public function loadEnquiryEditPageScripts($hook)
    {
        // Check if enquiry edit page.
        if ($hook != 'admin_page_quoteup-details-edit') {
            return;
        }

        $localizedData = array(
            'saving_dl'      => __('Saving Quote send deadline...', QUOTEUP_TEXT_DOMAIN),
            'security_issue' => __('Quote send deadline is not set due to security issues.', QUOTEUP_TEXT_DOMAIN),
            'successful'     => __('Quote send deadline set succesfully.', QUOTEUP_TEXT_DOMAIN),
        );

        $localizedData = apply_filters('quoteup_send_dl_localization', $localizedData);

        wp_enqueue_style('quoteup_vendor_enquiry_edit_css', QUOTEUP_PLUGIN_URL.'/css/vendor/edit-quote.css', false);
        wp_enqueue_script('quoteup_vendor_enquiry_edit_js', QUOTEUP_PLUGIN_URL.'/js/vendor/edit-quote.js', array('jquery', 'jquery-ui-datepicker'), false, true);
        wp_localize_script('quoteup_vendor_enquiry_edit_js', 'quote_send_dl_data', $localizedData);
    }

    /**
     * Returns true if products data should be fetched from the
     * 'enquiry_products' table. It is necessary to decide when one vendor has
     * saved the prices and admin is viewing the product details. In this case,
     * we need to fetch the products from both tables 'enquiry_quotation' and
     * 'enquiry_products' and merge the products data together.
     */
    public function shouldFetchEnquiryProducts($shouldFetchEnquiryProducts, $enquiryId)
    {
        if (!$shouldFetchEnquiryProducts) {
            if (!quoteupHasQuotationBeenCreated($enquiryId)) {
                $shouldFetchEnquiryProducts = true;
            }
        }

        return $shouldFetchEnquiryProducts;
    }

    /**
     * Callback function for 'quoteup_enquiry_quote_products_query' hook.
     *
     * Modify query which is used to fetch products data
     * (from 'enquiry_quotation' table or 'enquiry_products' table)
     */
    public function modifyProductsQueryOnEditPage($sql)
    {
        if (did_action('wp_ajax_wdm_return_rows')) {
            return $sql;
        }

        $sql = quoteupModifyProductsDataRetrievalQuery($sql);
        return $sql;
    }

    /**
     * Callback function for 'quoteup_quotes_edit_enquiry_products' hook.
     *
     * Merge vendor modified product data with original enquiry products data.
     * @param array $enquiryProducts    Array containing enquiry products.
     * @param array $quoteProducts      Array containing quote products.
     *
     * @return array Returns array after merging the original enquiry products
     *               data with vendor modified products.
     */
    public function mergeVendorAddedProducts($enquiryProducts, $quoteProducts)
    {
        $mergedProducts = quoteupMergeQuoteProductsWithOriginalEnquiry($enquiryProducts, $quoteProducts);
        return $mergedProducts;
    }

    /**
     * This function returns enquiry products which have been updated/ added
     * by the vendors in a particular enquiry.
     * @param int|array $enquiryId Enquiry id's
     * @param int   $vendorId  Vendor id when fetching all products added by the
     *                         particular vendor for a particular enquiry.
     *                         Pass negative integer when fetching
     *                         all products added by all vendors for a particular
     *                         enquiry.
     * @return [array] product data associate with the enquiry_id of quote created on admin side.
     */
    public function getVendorModifiedProductsData($enquiryId, $vendorId)
    {
        global $wpdb;
        $quoteProductsTable = getQuotationProductsTable();

        if (is_array($enquiryId)) {
            $enquiryIDs = implode(',', $enquiryId);
            $sql = "SELECT * FROM $quoteProductsTable WHERE enquiry_id IN ($enquiryIDs) ";
        } else {
            $sql = "SELECT * FROM $quoteProductsTable WHERE enquiry_id = $enquiryId ";
        }

        if ($vendorId < 0) {
            $sql .= " AND vendor_id IS NOT NULL ";
        } else {
            $sql .= " AND vendor_id = $vendorId ";
        }

        $sql  = apply_filters('quoteup_vendor_modified_products_data_query', $sql, $enquiryId, $vendorId);
        $sql .= " ORDER BY variation_index_in_enquiry ASC ";
        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results;
    }

    /**
     * This function returns enquiry products Id which have been updated/ added
     * by the vendors in a particular enquiry.
     * @param int|array $enquiryId Enquiry id's
     * @param int   $vendorId  Vendor id when fetching all products added by the
     *                         particular vendor for a particular enquiry.
     *                         Pass negative integer when fetching
     *                         all products added by all vendors for a particular
     *                         enquiry.
     * @return [array] product Id associated with the enquiry_id of quote created on admin side.
     */
    public function getVendorModifiedProductsId($enquiryId, $vendorId)
    {
        global $wpdb;
        $quoteProductsTable = getQuotationProductsTable();

        if (is_array($enquiryId)) {
            $enquiryIDs = implode(',', $enquiryId);
            $sql = "SELECT product_id FROM $quoteProductsTable WHERE enquiry_id IN ($enquiryIDs) ";
        } else {
            $sql = "SELECT product_id FROM $quoteProductsTable WHERE enquiry_id = $enquiryId ";
        }

        if ($vendorId < 0) {
            $sql .= " AND (admin_added IS NULL OR admin_added <> 'yes') ";
        } else {
            $sql .= " AND vendor_id = $vendorId ";
        }

        $sql  = apply_filters('quoteup_vendor_modified_products_id_query', $sql, $enquiryId, $vendorId);
        $sql .= " ORDER BY variation_index_in_enquiry ASC ";
        $results = $wpdb->get_col($sql);
        return $results;
    }

    /**
     * Callback for 'quoteup_customer_meta_data_query' filter.
     *
     * Append a condition to the query so only vendor accessible meta fields
     * (meta data) will be shown.
     *
     * @param   $query  string
     *
     * @return  string  Return a modified query so only vendor accessible meta fields
     *                  (meta data) will be shown to the vendor.
     */
    public function appendCondToCustomerMetaDataQuery($query)
    {
        $accessibleFields       = quoteupGetVendorAccessibleMetaFields();
        $accessibleFields       = array_keys($accessibleFields);
        $accessibleFieldsString = implode("', '", $accessibleFields);

        $query .= " AND meta_key IN ('" . $accessibleFieldsString . "')";
        return $query;
    }

    public function returnCustomerDefaultDataToShow($customerData)
    {
        $customerData = array_merge(array('enquiry_date'), array_keys(quoteupGetVendorAccessibleDefaultFields()));
        return $customerData;
    }

    /**
     * Callback function for 'quoteup_add_remove_link_enq_edit' hook.
     *
     * Hide remove icon from the Product Details section by returning false.
     *
     * @return bool     Return false to hide remove icon.
     */
    public function hideRemoveBtn()
    {
        return false;
    }

    /**
     * Callback function for 'quote_allow_meta_fields_enq_edit' hook.
     *
     * Return allowed fields to vendor.
     *
     * @return array     Return allowed fields.
     */
    public function returnQuoteAllowMetaFields($fields)
    {
        $fields = array_diff($fields, array('products-selection'));
        return $fields;
    }

    /**
     * Callback function for 'quoteup_enb_reply_in_enq_rem_msg_sec' filter hook.
     *
     * Return false to disable 'reply' functionality for vendors in Enquiry
     * Remarks and Message section.
     *
     * @return  bool    false.
     */
    public function shouldEnbReplyInEnqRemMsgSec()
    {
        return false;
    }

    /**
     * Callback function 'quoteup_order_id_ass_with_quote' filter hook.
     *
     * Return the vendor's sub order Id associated with the quote.
     *
     * @param   int     $parentOrderId  Parent Order Id.
     *
     * @return  int     Return sub orde Id if found, 0 otherwise.
     */
    public function returnVendorSubOrderIdOfQuote($parentOrderId)
    {
        $subOrderId = $parentOrderId;

        if (!empty($parentOrderId)) {
            $subOrderId = pepAddReturnVendorSubOrderId($parentOrderId, get_current_user_id());
        }

        return $subOrderId;
    }

    /**
     * Returns true if current user is vendor and not an admin.
     *
     * @return  bool    True if current user is vendor and not an admin, false otherwise.
     */
    public function disableShowPriceOptionOnEditPage($hasCap)
    {
        return (quoteupIsCurrentLoggedInUserVendor() && !quoteupIsCurrentUserHavingManageOptionsCap()) || $hasCap;
    }

    /**
     * Returns true if current user is vendor and not an admin.
     *
     * @return  bool    True if current user is vendor and not an admin, false otherwise.
     */
    public function disableExpDateOnEditPage($hasCap)
    {
        return (quoteupIsCurrentLoggedInUserVendor() && !quoteupIsCurrentUserHavingManageOptionsCap()) || $hasCap;
    }

    /**
     * Returns true if current user is vendor and not an admin.
     *
     * @return  bool    True if current user is vendor and not an admin, false otherwise.
     */
    public function disableQuoteSendDeadlineOnEditPage($hasCap)
    {
        return (quoteupIsCurrentLoggedInUserVendor() && !quoteupIsCurrentUserHavingManageOptionsCap()) || $hasCap;
    }

    /**
     * Callback for 'quoteup_after_quote_rel_btns' hook.
     *
     * Displays the 'Save Price' button inside 'Product Details' section.
     *
     * @return  void
     */
    public function addSavePriceBtn($enquiryId)
    {
        $disabledBtn       = '';
        $savePriceBtnclass = '';
        $loggedInVendorId  = get_current_user_id();
        $savePriceBtnText  = __('Save Price', QUOTEUP_TEXT_DOMAIN);

        if (quoteupHasQuotationBeenCreated($enquiryId) || quoteupHasVendorUpdatedPrice($enquiryId, $loggedInVendorId)) {
            $disabledBtn = 'disabled';
            $savePriceBtnclass  = 'quoteup-backend-disabled-field';
        }
        
        $savePriceBtnclass = apply_filters('quoteup_vendor_save_price_btn_class', $savePriceBtnclass);
        ?>
        <input type="button" id="btnSaveVendorQuotePrice" class="button <?php echo $savePriceBtnclass; ?>" value="<?php echo $savePriceBtnText; ?>" <?php echo $disabledBtn; ?>>
        <?php
    }

    /**
     * Callback for 'quoteup_enquiry_edit_quote_options_start'.
     * Show Quote Send Deadline field for admin.
     */
    public function showQuoteSendDeadlineField($enquiryData)
    {
        $result = \Includes\QuoteupOrderQuoteMapping::getOrderIdOfQuote($_GET[ 'id' ]);
        if ($result != null && $result != 0) {
            return;
        }

        global $quoteup;
        $form_data           = quoteupSettings();
        $enquiryId           = (int) $_GET['id'];
        $deadline            = empty($enquiryData['quote_send_deadline']) ? '' : $enquiryData['quote_send_deadline'] . ' 00:00:00';
        $isCurrentUserHasCap = quoteupIsCurrentUserHavingManageOptionsCap();
        $quoteSendDlToolTip  = __('If all vendors have updated the prices or admin has generated the quote, the quotation would be sent to the customer on this date.', QUOTEUP_TEXT_DOMAIN);

        // Disabled properties for 'Quote Send Deadline' option.
        $disabledClass  = '';
        $disabledString = '';
        if (apply_filters('quoteup_should_disable_quote_send_dl_opt_edit_page', !$isCurrentUserHasCap, $enquiryId, $deadline)) {
            $disabledClass  = 'quoteup-backend-disabled-field';
            $disabledString = 'disabled';
        }
        ?>
        <div class="quote-send-deadline">
            <?php
            if (!isset($form_data['enable_disable_quote']) || $form_data['enable_disable_quote'] != 1) {
                ?>
                <div class="wdm-quote-send-deadline" title="<?php echo $quoteSendDlToolTip; ?>">
                    <input type="hidden" name="quote_send_deadline" class="quote_send_deadline_hidden" value='<?php echo $deadline; ?>'>
                    <label class="quote_send_deadline left-label"><?php _e('Quote Send Deadline', QUOTEUP_TEXT_DOMAIN) ?> : </label>
                    <input type="text" value='<?php echo $quoteup->manageExpiration->getHumanReadableDate($deadline); ?>' class="wdm-input-quote-send-deadline <?php echo $disabledClass; ?>" readonly <?php echo $disabledString; ?>>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Display 'Save' button before 'Generate Quote' button in the
     * 'Product Details' section.
     * If quote has been created, then don't show the 'Save' button.
     *
     * @return void
     */
    public function displaySaveButton($enquiryId)
    {
        // Return if 'quote' has been generated/ created.
        if (quoteupHasQuotationBeenCreated($enquiryId)) {
            return;
        }

        $saveBtnText = __('Save', QUOTEUP_TEXT_DOMAIN);
        ?>
        <input type="button" id="saveAdminProductsData" class="button" value="<?php echo $saveBtnText; ?>" >
        <?php
    }

    /**
     * Callback for 'quoteup_after_quote_send_deadline_set' hook.
     *
     * Send email to vendors to inform about the deadline.
     */
    public function sendInitialQuoteDlEmail($enquiryId, $deadline)
    {
        $emailInstance = \Includes\Vendor\Emails\QuoteupVendorReminderEmail::getInstance();
        $emailInstance->sendInitialQuoteDlEmail($enquiryId, $deadline);
    }
}

QuoteupVendorEnquiryEdit::getInstance();

