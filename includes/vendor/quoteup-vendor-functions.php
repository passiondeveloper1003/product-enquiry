<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Returns true if current user is vendor and not an admin.
 *
 * @param  Object   $userData   WP_User object. Default. null.
 * @return boolean  True if vendor, false otherwise
 */
function quoteupIsCurrentLoggedInUserVendor()
{
    static $isVendor = null;

    if (null === $isVendor) {
        $isVendor      = false;
        $userData      = wp_get_current_user();
        $addOnInstance = quoteupGetVendorAddonInstance();
    
        if (!quoteupIsCurrentUserHavingManageOptionsCap() && null != $addOnInstance && $addOnInstance->isVendor($userData)) {
            $isVendor = true;
        }
    }

    return $isVendor;
}

/**
 * Returns the author Id/ vendor Id of the product.
 *
 * @param   int     $productId  Product Id.
 *
 * @return  int     Returns the author Id.
 */
if (!function_exists('quoteupGetAuthorIdOfProduct')) {
    function quoteupGetAuthorIdOfProduct($productId)
    {
        return get_post_field('post_author', $productId);
    }
}

/**
 * Return the author/ vendor emails of the specified products.
 *
 * @param   array   $productIds  Product Ids.
 *
 * @return  array   Return the author/ vendor emails of the
 *                  products.
 */
if (!function_exists('quoteupGetAuthorEmailsByProdIds')) {
    function quoteupGetAuthorEmailsByProdIds($productIds)
    {
        $emails = array();

        if (empty($productIds)) {
            return $emails;
        }

        global $wpdb;
        $productIdsString = implode(', ', $productIds);

        $query  = "SELECT DISTINCT user_email FROM {$wpdb->posts} AS p JOIN {$wpdb->users} AS u ON p.post_author=u.ID WHERE p.ID IN ($productIdsString)";
        $emails = $wpdb->get_col($query);

        return $emails;
    }
}

/**
 * Return the author/ vendor Ids of the specified products.
 *
 * @param   array   $productIds  Product Ids.
 *
 * @return  array   Return the author/ vendor Ids of the
 *                  products.
 */
if (!function_exists('quoteupGetAuthorIdsByProdIds')) {
    function quoteupGetAuthorIdsByProdIds($productIds)
    {
        $authorIds = array();

        if (empty($productIds)) {
            return $authorIds;
        }

        global $wpdb;
        $productIdsString = implode(', ', $productIds);

        $query  = "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE ID IN ($productIdsString)";
        $authorIds = $wpdb->get_col($query);

        return $authorIds;
    }
}

/**
 * Returns true if the product has been created by the currently logged in vendor.
 *
 * @param   array|int   $productData    product id or array of product data
 *                                      which must contain product Id in key
 *                                      'product_id'
 * @return  bool
 */
if (!function_exists('quoteupIsProductOfAuthor')) {
    function quoteupIsProductOfAuthor($productData)
    {
        static $authorProductIds = null;

        if (empty($authorProductIds)) {
            $authorProductIds = getProductIdsByAuthor();
        }

        if (is_array($productData)) {
            $productId = $productData['product_id'];
        } else {
            $productId = $productData;
        }

        $isAuthorProduct = in_array($productId, $authorProductIds);
        return apply_filters('quoteup_is_author_product', $isAuthorProduct);
    }
}

/**
 * Check whether the enquiry is of particular vendor. Return true, if enquiry
 * is of vendor, false otherwise.
 *
 * @param   int     $enquiryId      Required. Enquiry Id
 * @param   int     $vendorId       Required. Vendor Id
 *
 * @return  bool    Return false, if vendorId or enquiryId is empty or enquiry
 *                  is not of vendor. Return true if enquiry is of vendor.
 */
if (!function_exists('quoteupIsEnquiryOfVendor')) {
    function quoteupIsEnquiryOfVendor($enquiryId, $vendorId)
    {
        if (empty($enquiryId) || empty($vendorId)) {
            return false;
        }

        global $wpdb;
        $table         = getEnquiryProductsTable();
        $productIds    = getProductIdsByAuthor($vendorId);
        $productIdsStr = implode(', ', $productIds);
 
        $foundEnquiryId = $wpdb->get_col("SELECT enquiry_id FROM {$table} WHERE enquiry_id={$enquiryId} AND product_id IN ({$productIdsStr})");

        // If there is no vendor product in the 'enquiry_products' table.
        if (empty($foundEnquiryId)) {
            $table = getQuotationProductsTable();
            // Fetch Id if a vendor product exists in 'enquiry_quotation' table.
            $foundEnquiryId = $wpdb->get_col("SELECT enquiry_id FROM {$table} WHERE enquiry_id={$enquiryId} AND product_id IN ({$productIdsStr})");
        }

        if (empty($foundEnquiryId)) {
            return false;
        }
        return true;
    }
}

/**
 * Returns true if the quotation has been created for a provided enquiry,
 * false otherwise.
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  bool    Returns true if quotation has been created, false otherwise.
 */
if (!function_exists('quoteupHasQuotationBeenCreated')) {
    function quoteupHasQuotationBeenCreated($enquiryId)
    {
        global $wpdb;
        $isQuotationCreated = false;
        $enquiryDetailsTable = getEnquiryDetailsTable();
        $query = 'SELECT total FROM ' . $enquiryDetailsTable . ' WHERE enquiry_id = ' . $enquiryId;
        $res = $wpdb->get_var($query);

        if (!empty($res)) {
            $isQuotationCreated = true;
        }

        return apply_filters('quoteup_is_quotation_created', $isQuotationCreated, $enquiryId);
    }
}

/**
 * Check if record already exists in 'enquiry_quotation' table for the
 * particular enquiry Id, product Id and variation Id pair.
 *
 * @param   array   $productData    Product Data.
 *
 * @return  bool    Return true if record exists, false otherwise.
 */
function quoteupIsDataExistInQuotationTable($productData)
{
    global $wpdb;
    $isRecordExist = false;
    $quotationTable = getQuotationProductsTable();
    
    $query  = "SELECT ID from {$quotationTable} WHERE enquiry_id = %d AND product_id = %d AND variation_id = %d";
    $query  = $wpdb->prepare($query, $productData['enquiry_id'], $productData['product_id'], $productData['variation_id']);
    $found  = $wpdb->get_var($query);

    // If a record found.
    if (!empty($found)) {
        $isRecordExist = true;
    }

    return $isRecordExist;
}

/**
 * Check if record exists in 'enquiry_products' table for the
 * particular enquiry Id, product Id and variation Id pair.
 *
 * @param   array   $productData    Product Data.
 *
 * @return  bool    Return true if record exists, false otherwise.
 */
function quoteupIsDataExistInEnquiryTable($productData)
{
    global $wpdb;
    $isRecordExist        = false;
    $enquiryProductsTable = getEnquiryProductsTable();
    
    $query  = "SELECT ID from {$enquiryProductsTable} WHERE enquiry_id = %d AND product_id = %d AND variation_id = %d";
    $query  = $wpdb->prepare($query, $productData['enquiry_id'], $productData['product_id'], $productData['variation_id']);
    $found  = $wpdb->get_var($query);

    // If a record found.
    if (!empty($found)) {
        $isRecordExist = true;
    }

    return $isRecordExist;
}

/**
 * Update a single record in 'enquiry_quotation' table.
 * Update a new_price, quantity, show_price and vendor_updated columns using
 * enquiry Id, product Id, variation Id.
 *
 * @param   array  $productData     Product Data.
 * @param   bool   $isVendorUpdate  True if operation is being performed
 *                                  by the vendor, false otherwise.
 */
function quoteupUpdateQuotationTblRec($productData, $isVendorUpdate)
{
    global $wpdb;
    $quotationTable = getQuotationProductsTable();
    $updateRec = array(
        'newprice'   => $productData['newprice'],
        'quantity'   => $productData['quantity'],
    );
    $whereCon =  array(
        'enquiry_id'   => $productData['enquiry_id'],
        'product_id'   => $productData['product_id'],
        'variation_id' => $productData['variation_id']
    );
    $format      = array('%d', '%d');
    $whereFormat = '%d';

    if ($isVendorUpdate) {
        $updateRec['vendor_updated'] = 'yes';
        array_push($format, '%s');
        quoteupModifyVendorUpdatedColInEnquiryProductsTbl($productData['enquiry_id'], $productData['product_id'], $productData['variation_id']);
    } else {
        $updateRec['show_price'] = $productData['show_price'];
        array_push($format, '%s');
    }

    $wpdb->update(
        $quotationTable,
        $updateRec,
        $whereCon,
        $format,
        $whereFormat
    );
}

/**
 * Update 'enquiry_products' table.
 * Update vendor_updated column to 'yes' based on enquiry Id, product Id
 * and variation Id.
 */
function quoteupModifyVendorUpdatedColInEnquiryProductsTbl($enquiryId, $productId, $variationId)
{
    global $wpdb;
    $enqProductsTable = getEnquiryProductsTable();

    $wpdb->update(
        $enqProductsTable,
        array (
            'vendor_updated' => 'yes'
        ),
        array(
            'enquiry_id'   => $enquiryId,
            'product_id'   => $productId,
            'variation_id' => $variationId
        ),
        '%s',
        '%d'
    );
}

/**
 * Update 'enquiry_products' table.
 * Update 'removed' column to 'yes' based on enquiry Id, product Id
 * and variation Id pair.
 *
 * @param   int     $enquiryId          Enquiry Id.
 * @param   array   $newProductIds      Product Ids which need to be saved in
 *                                      'enquiry_quotation' table.
 * @param   array   $newVariationIds    Variation Ids which need to be saved in
 *                                      'enquiry_quotation' table.
 */
function quoteupUpdateRemovedColInEnquiryTbl($enquiryId, $newProductIds, $newVariationIds)
{
    global $wpdb;
    $where               = '';
    $enqProductsTable    = getEnquiryProductsTable();
    $quotationTable      = getQuotationProductsTable();
    $removedProductIds   = quoteupReturnRemovedProductIds($enquiryId, $newProductIds);
    $removedVariationIds = quoteupReturnRemovedVariationIds($enquiryId, $newVariationIds);

    if (empty($removedProductIds) && empty($removedVariationIds)) {
        return;
    }

    $productIdsString   = implode(', ', $removedProductIds);
    $variationIdsString = implode(', ', $removedVariationIds);

    if (!empty($productIdsString)) {
        $productIdsString = ' product_id IN (' . $productIdsString . ') ';
        $where = $productIdsString;
    }

    if (!empty($variationIdsString)) {
        $variationIdsString = ' variation_id IN (' . $variationIdsString . ') ';
        
        if (empty($where)) {
            $where = $variationIdsString;
        } else {
            $where .= ' OR ' .$variationIdsString;
        }
    }

    $query = "UPDATE {$enqProductsTable} SET removed = 'yes' WHERE {$where}";
    $wpdb->query($query);

    // Remove the products from the 'enquiry_quotation' table.
    $query = "DELETE FROM {$quotationTable} WHERE {$where}";
    $wpdb->query($query);
}

/**
 * Add/ insert product data in 'enquiry_quotation' table.
 *
 * @param   array   $productData    Products data to be inserted in the table.
 * @param   bool    $isVendorUpdate True if operation is being performed
 *                                  by the vendor, false otherwise.
 */
function quoteupAddProductDataInQuotationTbl($productData, $isVendorUpdate)
{
    global $wpdb;
    $quotationTable = getQuotationProductsTable();

    if ($isVendorUpdate) {
        $productData['vendor_updated'] = 'yes';
        quoteupModifyVendorUpdatedColInEnquiryProductsTbl($productData['enquiry_id'], $productData['product_id'], $productData['variation_id']);
    }

    unset($productData['vendor_id']);
    $wpdb->insert(
        $quotationTable,
        $productData
    );
}

/**
 * Return the product Ids which have been removed from the 'enquiry_products'
 * and 'enquiry_quotation' table.
 *
 * @param   int     $enquiryId      Enquiry Id.
 * @param   array   $newProductIds  Array containing new product Ids.
 *
 * @return  array   Return array contaning removed product Ids.
 */
function quoteupReturnRemovedProductIds($enquiryId, $newProductIds)
{
    // Fetch product Ids in 'enquiry_products' table.
    $oldEnqProductIds = quoteupGetAllProductIdsForEnquiry($enquiryId);
    // Fetch product Ids in 'enquiry_quotation' table.
    $oldQuoteProdIds  = getProductIdsInQuote($enquiryId);

    $removedProdIds = array_diff($oldEnqProductIds, $newProductIds);
    $removedProdIds = array_merge($removedProdIds, array_diff($oldQuoteProdIds, $newProductIds));
    $removedProdIds = array_unique($removedProdIds);

    $removedProdIds = apply_filters('quoteup_rmv_prod_ids_from_enq_quote', $removedProdIds, $enquiryId, $newProductIds);
    return $removedProdIds;
}

/**
 * Return the variation Ids which have been removed from the 'enquiry_products'
 * and 'enquiry_quotation' table.
 *
 * @param   int     $enquiryId      Enquiry Id.
 * @param   array   $newProductIds  Array containing new variation Ids.
 *
 * @return  array   Return array contaning removed variation Ids.
 */

function quoteupReturnRemovedVariationIds($enquiryId, $newVariationIds)
{
    $oldEnqVariationIds   = quoteupGetVariationIdsInEnquiry($enquiryId);
    $oldQuoteVariationIds = quoteupGetVariationIdsInQuote($enquiryId);

    $removedVariationIds = array_diff($oldEnqVariationIds, $newVariationIds);
    $removedVariationIds = array_merge($removedVariationIds, array_diff($oldQuoteVariationIds, $newVariationIds));
    $removedVariationIds = array_unique($removedVariationIds);

    $removedVariationIds = apply_filters('quoteup_rmv_var_ids_from_enq_quote', $removedVariationIds, $enquiryId, $newVariationIds);
    return $removedVariationIds;
}

/**
 * Returns all the enquiry ids related to a particular vendor.
 *
 * @param   int     $vendorId Optional. Vendor ids
 *
 * @return  array   Returns an array containing all enquiry ids belonging to
 *                  a particular vendor.
 */
if (!function_exists('quoteupReturnVendorEnqIds')) {
    function quoteupReturnVendorEnqIds($vendorId = 0)
    {
        if (empty($vendorId)) {
            $vendorId = get_current_user_id();
            if (empty($vendorId)) {
                return array();
            }
        }

        global $wpdb;
        $enquiryProductsTable  = getEnquiryProductsTable();
        $enquiryQuotationTable = getQuotationProductsTable();
        $productIds            = getProductIdsByAuthor($vendorId);
        $productIdsString      = implode(', ', $productIds);

        $sql = "SELECT ENQUIRY_ID FROM {$enquiryProductsTable} WHERE product_id IN ({$productIdsString})
                UNION 
                SELECT ENQUIRY_ID FROM {$enquiryQuotationTable} WHERE product_id IN ({$productIdsString})
        ";
        return apply_filters('quoteup_vendor_enq_ids', $wpdb->get_col($sql));
    }
}

/**
 * Returns a where clause which can be added to the query statement.
 *
 * @param   string      $columnName     Optional. Column name to include in where
 *                                      clause.
 *
 * @return  string      Returns the empty string if no enquiry is found or
 *                      string containing the where clause.
 */
if (!function_exists('quoteupVendorEnqIdsForWhereClause')) {
    function quoteupVendorEnqIdsForWhereClause($columnName = 'enquiry_id')
    {
        $enquiryIds = quoteupReturnVendorEnqIds();
        $stmt = '';

        if (empty($enquiryIds)) {
            $stmt = $columnName.' = 0 ';
        } else {
            $stmt = $columnName.' IN ('.implode(',', $enquiryIds).')';
        }
        return apply_filters('quoteup_vendor_enq_ids_where_clause', $stmt);
    }
}

/**
 * Returns the vendor column name present in the 'enquiry_detail_new' table
 * This vendor column of the table will be populated with the vendor ID only if
 * customer makes an enquiry about the product or vendor creates a quotation and
 * PEP add on is activated.
 *
 * @return string return the vendor column name
 */
if (!function_exists('quoteupGetVendorColumnName')) {
    function quoteupGetVendorColumnName()
    {
        return apply_filters('quoteup_vendor_column_name', 'vendor_id');
    }
}

/**
 * Returns PEP vendor addon instance of class defined in add-on plugin.
 */
if (!function_exists('quoteupGetVendorAddonInstance')) {
    function quoteupGetVendorAddonInstance()
    {
        return apply_filters('quoteup_get_vendor_addon_instance', null);
    }
}

/**
 * This function returns the values of ID column from 'enquiry_products' table
 * if product of a particular enquiry belongs to currently logged in vendor.
 */
if (!function_exists('quoteupGetEnquiryProductsIdForVendor')) {
    function quoteupGetEnquiryProductsIdForVendor($enquiryId, $tableName)
    {
        global $wpdb;
        $resultIds  = array();
        $vendorId   = get_current_user_id();

        $resultIds = $wpdb->get_col("SELECT ID FROM $tableName WHERE enquiry_id = $enquiryId AND vendor_id = $vendorId");
        return $resultIds;
    }
}

/**
 * Return all form fields including default form fields, custom form
 * fields. It also returns the form fields which have been removed
 * from the forms, but still present in the database.
 */
if (!function_exists('quoteupGetAllFormFields')) {
    function quoteupGetAllFormFields()
    {
        global $wpdb;
        $formFields = array();
    
        // retrieveing fields from enquiry_details_new table
        $formFields['enquiry-detail'] = quoteupEnquiryDtlFieldsToAnonymize();
    
        // retrieveing fields from enquiry_meta table
        $metaFields = quoteupEnquiryMetaFieldsToAnonymize($wpdb);
    
        // retrieveing fields from custom form
        $customFormFields = quoteupGetCustomFormFields();
    
        $formFields['enquiry-meta'] = array_merge($metaFields, $customFormFields);
        return $formFields;
    }
}

/**
 * Return vendors Id who have not the updated product price for a particular
 * enquiry.
 *
 * @param int   $enquiryId
 * @param bool  $vendorEmails   Flag whether to return vendor emails or vendor
 *                              Ids.
 *
 * @return array Returns Array containing vendors Id who have not updated the
 *               product price. Returns empty array if all vendors have updated
 *               the price.
 */
function quoteupGetVendorsNotUpdatedPrice($enquiryId, $vendorEmails = false)
{
    $vendors = array();
    if (!quoteupHasQuotationBeenCreated($enquiryId)) {
        $notUpdatedProducts = quoteupGetNotUpdatedProductsInQuoteTbl($enquiryId);
        if (empty($notUpdatedProducts)) {
            $notUpdatedProducts = quoteupGetNotUpdatedProductsInEnquiryProdTbl($enquiryId);
        }
        
        if ($vendorEmails) {
            $vendors = quoteupGetAuthorEmailsByProdIds($notUpdatedProducts);
        } else {
            $vendors = quoteupGetAuthorIdsByProdIds($notUpdatedProducts);
        }
    }

    return $vendors;
}

/**
 * Return the product Ids  from the 'enquiry_quotation' table which have not
 * been updated by the corresponding vendors.
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  array   Return array consisting the product Ids whose prices have
 *                  not been updated by the corresponding vendors.
 */
function quoteupGetNotUpdatedProductsInQuoteTbl($enquiryId)
{
    global $wpdb;
    $quotationTable = getQuotationProductsTable();

    $query = "SELECT DISTINCT product_id FROM {$quotationTable} WHERE enquiry_id = {$enquiryId} AND vendor_updated IS NULL";

    $results = $wpdb->get_col($query);
    return $results;
}

/**
 * Return the product Ids from the 'enquiry_products' table which have not been
 * updated by the corresponding vendors .
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  array   Return array consisting the product Ids whose prices have
 *                  not been updated by the corresponding vendors.
 */
function quoteupGetNotUpdatedProductsInEnquiryProdTbl($enquiryId)
{
    global $wpdb;
    $enquiryProductsTable = getEnquiryProductsTable();

    $query = "SELECT DISTINCT product_id FROM {$enquiryProductsTable} WHERE enquiry_id = {$enquiryId} AND vendor_updated IS NULL AND removed IS NULL";

    $results = $wpdb->get_col($query);
    return $results;
}

/**
 * Return the product Ids from the 'enquiry_products' table which have been
 * updated by the corresponding vendors .
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  array   Return array consisting the product Ids whose prices have
 *                  been updated by the corresponding vendors.
 */
function quoteupGetUpdatedProductsInEnquiryProdTbl($enquiryId)
{
    global $wpdb;
    $enquiryProductsTable = getEnquiryProductsTable();

    $query = "SELECT DISTINCT product_id FROM {$enquiryProductsTable} WHERE enquiry_id = {$enquiryId} AND vendor_updated IS NOT NULL AND removed IS NULL";

    $results = $wpdb->get_col($query);
    return $results;
}

/**
 * Return all vendors' emails related to a particular enquiry.
 *
 * @param int $enquiryId Enquiry Id.
 *
 * @return array Vendor ids related to an enquiry.
 */
if (!function_exists('quoteupGetAllVendorsEmailsForEnquiry')) {
    function quoteupGetAllVendorsEmailsForEnquiry($enquiryId)
    {
        $vendorEmails = array();

        // Check if vendor compability enabled.
        if (quoteupIsVendorCompEnabled()) {
            global $wpdb;
            $enquiryId             = (int) $enquiryId;
            $productIdsArray       = quoteupGetAllProductIdsForEnquiry($enquiryId);
            $numberOfProducts      = count($productIdsArray);
            $productIdPlaceholders = implode(', ', array_fill(0, $numberOfProducts, '%d'));

            global $wpdb;
            $query  = "SELECT DISTINCT user_email FROM {$wpdb->posts} AS p JOIN {$wpdb->users} AS u ON p.post_author=u.ID WHERE p.ID IN ($productIdPlaceholders)";
            $query  = $wpdb->prepare($query, $productIdsArray);
            $vendorEmails = $wpdb->get_col($query);
        }

        return $vendorEmails;
    }
}

/**
 * This function returns the user emails based on provided user Ids.
 *
 * @param   array   $userIds    User Ids.
 *
 * @return  array   User Emails.
 */
function quoteupGetUserEmailsByIds($userIds)
{
    global $wpdb;
    $numberOfUsers      = count($userIds);
    $userIdPlaceholders = implode(', ', array_fill(0, $numberOfUsers, '%d'));

    $query  = "SELECT DISTINCT user_email FROM {$wpdb->users} WHERE ID IN ($userIdPlaceholders)";
    $query  = $wpdb->prepare($query, $userIds);
    $userEmails = $wpdb->get_col($query);
    return $userEmails;
}

/**
 * This functions returns all products' Ids which belong to a particular enquiry
 * specified by enquiryId parameter.
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  array   Return all product Ids which belong to a particular
 *                  enquiry.
 */
if (!function_exists('quoteupGetAllProductIdsForEnquiry')) {
    function quoteupGetAllProductIdsForEnquiry($enquiryId)
    {
        global $wpdb;
        $enquiryProductsTable = getEnquiryProductsTable();

        $query = "SELECT DISTINCT product_id FROM {$enquiryProductsTable} WHERE enquiry_id = %d";
        $query = $wpdb->prepare($query, $enquiryId);
        $productIds = $wpdb->get_col($query);

        return $productIds;
    }
}

/**
 * This functions returns all products' Ids which belong to a particular enquiry
 * specified by enquiryId parameter.
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  array   Return all product Ids which belong to a particular
 *                  enquiry.
 */
if (!function_exists('quoteupGetVariationIdsInEnquiry')) {
    function quoteupGetVariationIdsInEnquiry($enquiryId)
    {
        global $wpdb;
        $enquiryProductsTable = getEnquiryProductsTable();

        $query = "SELECT DISTINCT variation_id FROM {$enquiryProductsTable} WHERE enquiry_id = %d AND variation_id <> 0";
        $query = $wpdb->prepare($query, $enquiryId);
        $productIds = $wpdb->get_col($query);

        return $productIds;
    }
}

/**
 * Return all variation Ids which belong to a particular enquiry
 * specified by enquiryId parameter.
 * It returns variation Ids from 'enquiry_quotation' table.
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  array   Return all variation Ids from 'enquiry_quotation' table.
 */
if (!function_exists('quoteupGetVariationIdsInQuote')) {
    function quoteupGetVariationIdsInQuote($enquiryId)
    {
        global $wpdb;
        $quoteTable = getQuotationProductsTable();

        $query = "SELECT DISTINCT variation_id FROM {$quoteTable} WHERE enquiry_id = %d AND variation_id <> 0";
        $query = $wpdb->prepare($query, $enquiryId);
        $productIds = $wpdb->get_col($query);

        return $productIds;
    }
}

/**
 * Returns true if vendor has updated the products' prices for a particular
 * enquiry, false otherwise.
 *
 * @param   int     $enquiryId  Enquiry Id.
 * @param   int     $vendorId   Vendor Id.
 *
 * @return  bool    Returns true if vendor has updated price, false otherwise.
 */
function quoteupHasVendorUpdatedPrice($enquiryId, $vendorId)
{
    if (quoteupHasQuotationBeenCreated($enquiryId)) {
        return true;
    }

    global $wpdb;
    $hasVendorUpdatedPrice = false;
    $quotationTableName    = getQuotationProductsTable();
    $productIds            = getProductIdsByAuthor();
    $productIdsString      = implode(', ', $productIds);

    // Check if there is any row.
    $result = $wpdb->get_col("SELECT ID FROM $quotationTableName WHERE enquiry_id = $enquiryId AND product_id IN ({$productIdsString})");

    if (!empty($result)) {
        $result = $wpdb->get_col("SELECT ID FROM $quotationTableName WHERE enquiry_id = $enquiryId AND product_id IN ({$productIdsString}) AND vendor_updated IS NULL");

        // If there is no record with vendor_updated column as NULL value,
        // it means vendor has updated the products prices.
        if (empty($result)) {
            $hasVendorUpdatedPrice = true;
        }
    }

    return apply_filters('quoteup_has_vendor_updated_price', $hasVendorUpdatedPrice, $vendorId, $enquiryId);
}

/**
 * Modify query which is used to fetch products data
 * (from 'enquiry_quotation' table or 'enquiry_products' table)
 */
if (!function_exists('quoteupModifyProductsDataRetrievalQuery')) {
    function quoteupModifyProductsDataRetrievalQuery($sql)
    {
        if (quoteupIsCurrentLoggedInUserVendor()) {
            $loggedInVendorId = get_current_user_id();
                $productIds = getProductIdsByAuthor($loggedInVendorId);
                $sql .= ' AND product_id IN (' . implode(', ', $productIds) . ')';
        }

        return $sql;
    }
}

/**
 * Merge quote products with original enquiry. It is required to merge
 * the quote products with original enquiry products when one vendor
 * has saved the prices and an admin viewing the product details (on enquiry
 * list page or on enquiry edit page).
 *
 * @param array $enquiryProducts    Array containing enquiry products.
 *                                  (Products from 'enquiry_products' table).
 * @param array $quoteProducts      Array containing quote products
 *                                  (Products from 'enquiry_quotation' table).
 *
 * @return array Returns array after merging the vendor modified products
 *               data with original enquiry products.
 */
if (!function_exists('quoteupMergeQuoteProductsWithEnquiry')) {
    function quoteupMergeQuoteProductsWithOriginalEnquiry($enquiryProducts, $quoteProducts)
    {
        $mergedProducts = array();

        if (empty($quoteProducts)) {
            $mergedProducts = $enquiryProducts;
        } else {
            $mergedProducts = $enquiryProducts;

            foreach ($quoteProducts as $quoteProduct) {
                $quoteProductId           = $quoteProduct['product_id'];
                $isEnquiryProductModified = false;
                foreach ($enquiryProducts as $key => $enquiryProduct) {
                    if ($quoteProductId == $enquiryProduct['product_id']) {
                        $isEnquiryProductModified       = true;
                        $quoteProduct['vendor-updated'] = true;
                        $mergedProducts[$key]           = $quoteProduct;
                        break;
                    }
                }

                if (!$isEnquiryProductModified) {
                    $quoteProduct['vendor-updated'] = true;
                    $mergedProducts[] = $quoteProduct;
                }
            }
        }

        return $mergedProducts;
    }
}

function quoteupGetProductsForAdminFromEnqQuote($enquiryId)
{
    global $wpdb;
    $query      = '';
    $enqTable = getEnquiryProductsTable();
    $quoteTable = getQuotationProductsTable();

    if (quoteupHasQuotationBeenCreated($enquiryId)) {
        $query = "SELECT * FROM $quoteTable WHERE enquiry_id = $enquiryId";
        $results = $wpdb->get_results($query, ARRAY_A);
    } else {
        $query = "SELECT DISTINCT product_id FROM $quoteTable WHERE enquiry_id = $enquiryId";
        $results = $wpdb->get_col($query);
        if (empty($results)) {
            $query    = "SELECT * FROM $enqTable WHERE enquiry_id = $enquiryId";
            $results = $wpdb->get_results($query, ARRAY_A);
        } else {
            $query = "SELECT * FROM $quoteTable WHERE enquiry_id = $enquiryId";
            $quoteProducts = $wpdb->get_results($query, ARRAY_A);
            $enqProducts   = $wpdb->get_results("SELECT * FROM $enqTable WHERE enquiry_id = $enquiryId AND removed IS NULL AND product_id NOT IN (SELECT DISTINCT product_id FROM $quoteTable WHERE enquiry_id = $enquiryId)", ARRAY_A);
            $results = array_merge($enqProducts, $quoteProducts);
        }
    }

    return $results;
}

function quoteupGetProductsForVendorFromEnqQuote($enquiryId)
{
    global $wpdb;
    $query      = '';
    $enqTable = getEnquiryProductsTable();
    $quoteTable = getQuotationProductsTable();
    $vendProducts = getProductIdsByAuthor();
    $vendProductsString = implode(', ', $vendProducts);

    if (quoteupHasQuotationBeenCreated($enquiryId)) {
        $query = "SELECT * FROM $quoteTable WHERE enquiry_id = $enquiryId AND product_id IN ($vendProductsString)";
        $results = $wpdb->get_results($query, ARRAY_A);
    } else {
        $query = "SELECT * FROM $quoteTable WHERE enquiry_id = $enquiryId AND product_id IN ($vendProductsString)";
        $results = $wpdb->get_results($query, ARRAY_A);
        if (empty($results)) {
            $query    = "SELECT * FROM $enqTable WHERE enquiry_id = $enquiryId AND product_id IN ($vendProductsString) AND removed IS NULL";
            $results = $wpdb->get_results($query, ARRAY_A);
        }
    }

    return $results;
}

/**
 * Returns all the fields accessible to the vendor.
 *
 * @return  array   Returns all the fields accessible to vendor.
 */
function quoteupGetVendorAccessibleAllFields()
{
    static $accessibleFields = null;

    if (null === $accessibleFields) {
        $accessibleFields = get_option('wdm_vendor_visible_customer_data');
    }

    return $accessibleFields;
}

/**
 * Returns all the default fields accessible to the vendor.
 *
 * @return  array   Returns all the default fields accessible to vendor.
 */
function quoteupGetVendorAccessibleDefaultFields()
{
    static $accessibleFields = null;

    if (null === $accessibleFields) {
        $accessibleFields = get_option('wdm_vendor_visible_customer_data');
        $accessibleFields = $accessibleFields['enquiry-detail'];
    }

    return $accessibleFields;
}

/**
 * Returns all the meta fields accessible to the vendor.
 *
 * @return  array   Returns all the meta fields accessible to vendor.
 */
function quoteupGetVendorAccessibleMetaFields()
{
    static $accessibleFields = null;

    if (null === $accessibleFields) {
        $accessibleFields = get_option('wdm_vendor_visible_customer_data');
        $accessibleFields = $accessibleFields['enquiry-meta'];
    }

    return $accessibleFields;
}

function quoteupGetDeadlineOfEnquiry($enquiryId)
{
    global $wpdb;
    $enquiryDetailsTable = getEnquiryDetailsTable();

    $deadline = $wpdb->get_var('SELECT quote_send_deadline FROM ' . $enquiryDetailsTable . ' WHERE enquiry_id = ' . $enquiryId);
    return $deadline;
}

/**
 * Return the total of all the products exist in the enquiry for a vendor.
 *
 * @param   int     $enquiryId  Enquiry Id.
 * @param   int     $vendorId   Vendor Id.
 *
 * @return  float   Return the total.
 */
function quoteupGetTotalForVendorByEnquiryId($enquiryId, $vendorId)
{
    global $wpdb;
    $total          = 0;
    $quoteTable     = getQuotationProductsTable();
    $productIds     = getProductIdsByAuthor($vendorId);
    $productIdsStr  = implode(', ', $productIds);
 

    $query = "SELECT sum(quantity * newprice) FROM {$quoteTable} WHERE enquiry_id = {$enquiryId} AND product_id IN ({$productIdsStr})";
    $total = $wpdb->get_var($query);

    if (empty($total)) {
        $total = 0;
    }

    return $total;
}

/**
 * Check if:
 * 1. 'Auto Send Quotation' setting is enabled.
 * 2. All vendors have updated the products prices.
 * Send quote to the customer if above conditions are met.
 *
 * @param   int     $enquiryId  Enquiry Id.
 *
 * @return  bool    Return true if quote is sent, false otherwise.
 */
function quoteupCheckAndSendQuote($enquiryId)
{
    $isQuoteSent = false;

    if (!quoteupIsAutoSendQuoteEnabled() || !empty(quoteupGetVendorsNotUpdatedPrice($enquiryId))) {
        return $isQuoteSent;
    }

    if (!class_exists('\Includes\Admin\SendQuoteMail')) {
        include_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-send-quote-mail.php';
        include_once QUOTEUP_PLUGIN_DIR.'/includes/admin/class-quoteup-generate-pdf.php';
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
    }

    $isQuoteSent = true;
    $mailData    = quoteupReturnQuoteSendingMailData($enquiryId);
    \Includes\Admin\SendQuoteMail::sendMail($mailData);

    return $isQuoteSent;
}
