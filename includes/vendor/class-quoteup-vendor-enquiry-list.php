<?php

namespace Includes\Admin\Vendor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * This class is responsible for changing the content on the
 * Enquiry List page.
 */
class QuoteupVendorEnquiryList
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
        // If vendor compability setting is not enabled, then return.
        if (!quoteupIsVendorCompEnabled()) {
            return;
        }

        $this->addExportCSVHooks();

        if (!defined('DOING_AJAX') && (!isset($_GET['page']) || 'quoteup-details-new' != $_GET['page'])) {
            return;
        }

        // Hooks required for admin as well as vendor.
        add_filter('quoteup_enq_quote_products_on_hover', array($this, 'getProductsForEnqQuote'), 10, 2);

        // If current user is an admin.
        if (quoteupIsCurrentUserHavingManageOptionsCap()) {
            add_filter('quoteup_should_fetch_enquiry_products', array($this, 'shouldFetchEnquiryProducts'), 20, 2);
            add_filter('quoteup_quotes_edit_enquiry_products', array($this, 'mergeVendorAddedProducts'), 20, 2);
        } else if (quoteupIsCurrentLoggedInUserVendor()) {
            // If user is vendor and not an admin.

            add_filter('quoteup_enquiry_quote_products_query', array($this, 'modifyProductsQueryOnListPage'), 20, 2);
            // add_filter('quoteup_unread_enquiry_count_query', array($this, 'returnVendorUnreadEnquiryCountQuery'));
            add_filter('wdm_quote_list_columns', array($this, 'modifyListColumns'));
            add_filter('wdm_enquiry_list_columns', array($this, 'modifyListColumns'));
            add_filter('quote_total_on_enquiry_list', array($this, 'modifyQuoteTotal'), 10, 2);
            add_filter('order_id_ass_with_quote_on_enq_list', array($this, 'returnVendorSubOrderIdOfQuote'));
            add_action('admin_enqueue_scripts', array($this, 'loadEnquiryListPageScripts'));
            add_filter('quoteup_get_bulk_actions', array($this, 'removeExportBulkActions'), 20);
            add_filter('quoteup_enquiry_actions_cap', array($this, 'removeDeleteActionCap'));
            add_filter('quoteup_enq_quote_ids', array($this, 'filterEnqQuoteIdsForStatus'));
            add_filter('quoteup_status_specific_enq_query', array($this, 'returnVendorEnqIdsForWhereClause'), 20, 2);
            add_filter('quoteup_enquiry_status_add_where_clause', array($this, 'returnVendorEnqIdsForWhereClause'), 20, 2);
            add_filter('quoteup_admin_created_quote_where_clause', array($this, 'returnVendorEnqIdsForWhereClause'), 20, 2);
            // security parameters check
            add_action('quoteup_validate_enquiry_delete_action', array($this, 'restrictEnquiryDelAction'));
        }
    }

    public function removeDeleteActionCap($actions)
    {
        unset($actions['delete']);
        return $actions;
    }

    /**
     * Returns the enquiry Ids for enquiry list table after manipulation.
     *
     * @param  array    $queryWhere     Enquiry Ids.
     *
     * @return array    Return array containing enquiry Ids after manipulation.
     */
    public function filterEnqQuoteIdsForStatus($enquiryIds)
    {
        $vendorId = get_current_user_id();

        // If status is set.
        if (!isset($_GET['status']) && !empty($vendorId)) {
            $enquiryIdsForVendor = quoteupReturnVendorEnqIds($vendorId);
            return $enquiryIdsForVendor;
        } else if (empty($enquiryIds)) {
            return -1;
        }

        return $enquiryIds;
    }

    public function returnVendorEnqIdsForWhereClause($sql, $columnName)
    {
        $stmt = quoteupVendorEnqIdsForWhereClause($columnName);

        if (!empty($stmt)) {
            $sql .= ' AND '.$stmt;
        }

        return $sql;
    }

    /**
     * Restrict the vendor from deleting the enquiry when vendor tries to
     * delete enquiry.
     */
    public function restrictEnquiryDelAction()
    {
        wp_die(__('You are not authorized to delete the enquiry. Contact administrator!', QUOTEUP_TEXT_DOMAIN));
    }

    /**
     * Add the hooks required while exporting CSV file for vendor.
     */
    public function addExportCSVHooks()
    {
        if (quoteupIsCurrentLoggedInUserVendor()) {
            add_filter('quoteup_individual_enquiry_data', array($this, 'modifyIndividualEnquiryData'));
            add_filter('quoteup_meta_cols_for_csv_query', array($this, 'modifyMetaColsForCSVQuery'));
            add_filter('quoteup_enquiry_meta_data', array($this, 'modifyOrderDetails'), 20, 2);
            add_filter('quoteup_should_skip_no_products', array($this, 'shouldSkipNoProducts'));
        }
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
     * Callback function for 'quoteup_quotes_edit_enquiry_products' hook.
     *
     * Merge original enquiry products data with vendor modified product data.
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
    
    public function getProductsForEnqQuote($products, $enquiryId)
    {
        $isVendor = quoteupIsCurrentLoggedInUserVendor();

        if ($isVendor) {
            $products = quoteupGetProductsForVendorFromEnqQuote($enquiryId);
        } else {
            $products = quoteupGetProductsForAdminFromEnqQuote($enquiryId);
        }

        return $products;
    }

    /**
     * Callback function for 'quoteup_enquiry_quote_products_query' hook.
     *
     * Modify query which is used to fetch products data
     * (from 'enquiry_quotation' table or 'enquiry_products' table)
     *
     * @return string   Returns modified query so only vendor related products
     *                  are fetched.
     */
    public function modifyProductsQueryOnListPage($sql, $tableName)
    {
        $sql = quoteupModifyProductsDataRetrievalQuery($sql);

        if ($tableName == getEnquiryProductsTable() && !empty(did_action('wp_ajax_wdm_return_rows'))) {
            $sql .= ' AND REMOVED IS NULL ';
        }
        return $sql;
    }

    public function filterVendorAccessibleData($individualEnquiry)
    {
        $fields = array('enquiry_id', 'enquiry_date');
        $fields = array_merge($fields, quoteupGetVendorAccessibleDefaultFields());
        $fields = array_flip($fields);

        $individualEnquiry = array_intersect_key($individualEnquiry, $fields);
        return $individualEnquiry;
    }

    /**
     * Callback function for 'quoteup_enquiry_meta_data' filter hook.
     *
     * Modify order details such as order Id, total price.
     * Order Id is replaced with the corressponding sub order Id.
     * Total price includes the prices of the vendor products only.
     */
    public function modifyOrderDetails($csvData, $enquiryId)
    {
        $parentOrderId = $csvData['Order ID'];
        
        // If order has not been placed.
        if (empty($parentOrderId)) {
            $subOrderId = '';
            $total      = empty($csvData['Total']) ? '' : quoteupGetTotalForVendorByEnquiryId($enquiryId, get_current_user_id());
        } else {
            // Fetch sub order Id when order is placed.
            $subOrderId = pepAddReturnVendorSubOrderId($parentOrderId, get_current_user_id());

            if (empty($subOrderId)) {
                $subOrderId = '-';
                $total      = '-';
            } else {
                $order = wc_get_order($subOrderId);
                $total = $order->get_total();
            }
        }

        $csvData['Order ID'] = $subOrderId;
        $csvData['Total']    = $total;

        return $csvData;
    }

    /**
     * Callback function for 'quoteup_should_skip_no_products' hook.
     *
     * Return false, so that the CSV file always contains alteast some data.
     * For example, Enquiry Id, Enquiry Date, Quote Status.
     *
     * @return  bool    false
     */
    public function shouldSkipNoProducts()
    {
        return false;
    }

    public function modifyIndividualEnquiryData($individualEnquiry)
    {
        $fields = array('enquiry_id' => '', 'enquiry_date' => '', 'expiration_date' => '', 'order_id' => '', 'total' => '');
        $fields = array_merge($fields, quoteupGetVendorAccessibleDefaultFields());

        $individualEnquiry = array_intersect_key($individualEnquiry, $fields);
        return $individualEnquiry;
    }

    /**
     * Callback for 'quoteup_meta_cols_for_csv_query' filter.
     *
     * Modifies the query to include vendor accessible meta columns (meta data/
     * meta keys)
     * in the exported CSV.
     *
     * @param   string  Query to fetch meta columns (meta data/ meta keys) from
     *                  the 'enquiry_meta' table.
     *
     * @return  string  Returns the modified query to include vendor accessible
     *                  meta columns (meta data/ meta keys).
     */
    public function modifyMetaColsForCSVQuery($query)
    {
        $metaFields = quoteupGetVendorAccessibleMetaFields();
        $metaFields = array_keys($metaFields);
        array_push($metaFields, '_admin_quote_created');
        $metaFieldsInString = implode("', '", $metaFields);

        $query .= " WHERE meta_key IN ('" . $metaFieldsInString . "')";
        return $query;
    }

    public function removeExportBulkActions($actions)
    {
        if (quoteupVendorHasExportCapability()) {
            $actions = array(
                'bulk-export' => __('Export', QUOTEUP_TEXT_DOMAIN),
                'bulk-export-all' => __('Export all enquiries', QUOTEUP_TEXT_DOMAIN),
            );
        } else {
            $actions = array();
        }

        return $actions;
    }

    /**
     * Callback for 'wdm_enquiry_quote_list_columns' filter.
     *
     * Check if 'Custmer Name (name)' and 'Customer Email (email)' fields are
     * accessible to the vendors. If not, then remove the particular field(s)
     * from the enquiry list columns
     *
     * @param   $columns    array   Array containing enquiry list columns.
     *
     * @return  array       Return an array containing enquiry list columns
     *                      which are accessible to the vendors.
     */
    public function modifyListColumns($columns)
    {
        $vendorAccessibleFields = quoteupGetVendorAccessibleDefaultFields();

        if (!isset($vendorAccessibleFields['name'])) {
            unset($columns['name']);
        }

        if (!isset($vendorAccessibleFields['email'])) {
            unset($columns['email']);
        }

        return apply_filters('wdm_vendor_enquiry_quote_list_columns', $columns);
    }

    /**
     * Return the quote total for a vendor.
     *
     * Callback for 'quote_total_on_enquiry_list' filter.
     *
     * @param   float|string   $total   Quote total or '-'.
     * @param   array          $item    Enquiry details.
     */
    public function modifyQuoteTotal($total, $item)
    {
        if ('-' != $total) {
            $enquiryId = $item['enquiry_id'];
            $vendorId  = get_current_user_id();
            $total     = quoteupGetTotalForVendorByEnquiryId($enquiryId, $vendorId);
        }

        return $total;
    }

    /**
     * Callback function 'order_id_ass_with_quote_on_enq_list' filter hook.
     *
     * Return the vendor's sub order Id associated with the quote.
     *
     * @param   int     $subOrderId  Parent Order Id.
     *
     * @return  int     Return sub orde Id if found, 0 otherwise.
     */
    public function returnVendorSubOrderIdOfQuote($subOrderId)
    {
        if ('-' != $subOrderId) {
            $subOrderId = pepAddReturnVendorSubOrderId($subOrderId, get_current_user_id());
        }

        return $subOrderId;
    }

    /**
     * Enqueue CSS and scripts required on the vendor enquiry list page.
     */
    public function loadEnquiryListPageScripts()
    {
        wp_enqueue_style('quoteup_vendor_enquiry_list_css', QUOTEUP_PLUGIN_URL.'/css/vendor/enquiry-list.css', false);
    }
}

QuoteupVendorEnquiryList::getInstance();
