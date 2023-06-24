<?php

/**
 * This function generates hash products
 *
 * @param  int   $productID     Product ID of product
 * @param  int   $variationID   Variation ID of product(if variable product)
 * @param  array $variationData Variation data of product (if variable product)
 * @return [string] Product hash value
 */
function generateProductHash($productID, $variationID = 0, $variationData = array())
{
    if (!$variationID) {
        return md5($productID);
    } else {
        return md5($variationID.'_'.implode('_', $variationData));
    }
}

/**
 * This function is used to add attribute_ prefix to the label of variations.
 *
 * @param  int    $variation_id Variation_id of the Variable product.
 * @param  string $variation    attribute of the variation
 * @return array $variationData Variation data of the product corresponding to the Variation.
 */
function sanitizeVariationLabel($variation_id, $variation)
{
    $variationData = array();
    if ($variation_id) {
        $setAttributeName = function ($value) {
            return 'attribute_'.$value;
        };
        //Sets attribute_ prefix to all keys of an array
        $variationData = array_combine(
            array_map($setAttributeName, array_keys($variation)),
            $variation
        );
    }

    return $variationData;
}


/**
 * This function returns enquiry data of particular enquiry or array of enquiry id
 *
 * @param  mixed $enquiryId Enquiry id's
 * @return [array] $results [enquiry details from wp_enquiry_detail_new]
 */
function getEnquiryData($enquiryId = 0)
{
    global $wpdb;
    $enquiryTable = getEnquiryDetailsTable();
    if (is_array($enquiryId)) {
        $enquiryIDs = implode(',', $enquiryId);
        $sql = "SELECT * FROM $enquiryTable WHERE enquiry_id IN ($enquiryIDs)";
        $results = $wpdb->get_results($sql, ARRAY_A);
    } elseif ($enquiryId == 0) {
        $sql = "SELECT * FROM $enquiryTable";
        $results = $wpdb->get_results($sql, ARRAY_A);
    } else {
        $sql = "SELECT * FROM $enquiryTable WHERE enquiry_id = $enquiryId";
        $results = $wpdb->get_row($sql, ARRAY_A);
    }
    return $results;
}


/**
 * This function returns enquiry products of particular enquiry or array of enquiry id
 *
 * @param  mixed $enquiryId Enquiry id's
 * @return [array] product data associated with the enquiry_id of enquiry created on the
 * frontend.
 */
function getEnquiryProducts($enquiryId)
{
    $enquiryProductsTable = getEnquiryProductsTable();
    return quoteupGetProducts($enquiryId, $enquiryProductsTable);
}

/**
 * This function returns enquiry products of particular enquiry or array of enquiry id
 *
 * @param  int|array $enquiryId Enquiry id's
 * @return [array] product data associate with the enquiry_id of quote created on admin side.
 */
function getQuoteProducts($enquiryId)
{
    $quoteProductsTable = getQuotationProductsTable();
    return quoteupGetProducts($enquiryId, $quoteProductsTable);
}

/**
 * This function returns the products data associated with the quotes.
 *
 * @param  [int/array] $enquiryId [Enquiry Id/s]
 * @param  [string]    $tableName [Table Name]
 * @return [array] $results [Product Details]
 */
function quoteupGetProducts($enquiryId, $tableName)
{
    global $wpdb;
    if (is_array($enquiryId)) {
        $enquiryIDs = implode(',', $enquiryId);
        $sql = "SELECT * FROM $tableName WHERE enquiry_id IN ($enquiryIDs) ";
    } else {
        $sql = "SELECT * FROM $tableName WHERE enquiry_id = $enquiryId ";
    }

    $sql = apply_filters('quoteup_enquiry_quote_products_query', $sql, $tableName, $enquiryId);
    if ($tableName == $wpdb->prefix.'enquiry_quotation') {
        $sql .= " ORDER BY variation_index_in_enquiry ASC";
    }
    $results = $wpdb->get_results($sql, ARRAY_A);
    return $results;
}

/**
 * Gets meta value for meta keys from database.
 *
 * @param int    $enquiryId Enquiry Id
 * @param string $metaKey   meta keys
 */
function getEnquiryMeta($enquiryId, $metaKey)
{
    global $wpdb;
    $metaTbl = $wpdb->prefix.'enquiry_meta';
    $sql = $wpdb->prepare("SELECT meta_value FROM $metaTbl WHERE meta_key=%s AND enquiry_id=%d", $metaKey, $enquiryId);
    return $wpdb->get_var($sql);
}

/**
 * Updated meta value for meta keys in database.
 *
 * @param int    $enquiryId Enquiry Id
 * @param string $metaKey   meta key
 * @param string $metaValue meta value
 */
function updateEnquiryMeta($enquiryId, $metaKey, $metaValue)
{
    global $wpdb;
    $metaTbl = $wpdb->prefix.'enquiry_meta';
    $updateStatus = $wpdb->update(
        $metaTbl,
        array(
            'meta_value' => $metaValue,
            ),
        array(
            'enquiry_id' => $enquiryId,
            'meta_key' => $metaKey,
            ),
        array(
            '%s',
            ),
        array('%d', '%s')
    );

    return $updateStatus;
}

/**
 * Updates the enquiry product table with product details.
 *
 * @param int   $enquiryID       Enquiry Id.
 * @param array $product_details Multidimensional array containing product data.
 * @param array $postData        $_POST data.
 */
function updateProductDetails($enquiryID, $product_details, $postData = array())
{
    global $wpdb;
    $enquiryProductsTable = getEnquiryProductsTable();
    foreach ($product_details as $singleProduct) {
        $productID      = $singleProduct['id'];
        $variation_id   = $singleProduct['variation_id'];
        $variation      = $singleProduct['variation'];
        $product        = empty($variation_id) ? wc_get_product($productID) : wc_get_product($variation_id);
        $title          = $singleProduct['title'];
        $price          = $singleProduct['price'];
        $quantity       = $singleProduct['quantity'];
        $remark         = $singleProduct['remark'];
        // function to sanitize variations with prefix attribute_ for generating hash
        $variationData  = sanitizeVariationLabel($variation_id, $variation);
        $product_hash   = GenerateProductHash($productID, $variation_id, $variationData);

        if (isset($postData[ 'txtemail' ])) {
            $customerEmail = filter_var($postData[ 'txtemail' ], FILTER_SANITIZE_EMAIL);
            $price = quoteupGetPriceToDisplay($product, $quantity, $price, $customerEmail);
        }
        if (empty($price) || '' == $price) {
            $price = 0;
        }

        $wpdb->insert(
            $enquiryProductsTable,
            array(
                'enquiry_id' => $enquiryID,
                'product_id' => $productID,
                'product_title' => $title,
                'price' => $price,
                'quantity' => $quantity,
                'remark' => $remark,
                'variation_id' => $variation_id,
                'variation' => serialize($variation),
                'product_hash' => $product_hash,
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%f',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
            )
        );

        /**
         * Use hook after product details of the enquiry are inserted.
         *
         * @param  int  $enquiryID  Enquiry Id.
         * @param  int  $productID  Product Id.
         */
        do_action('quoteup_product_data_inserted', $enquiryID, $productID);
    }
}
/**
 * This function returns name of the Enquiry Details Table i.e,wp_enquiry_detail_new.
 *
 * @return [string] table name: wp_enquiry_detail_new.
 */
function getEnquiryDetailsTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_detail_new';
}

/**
 * This function returns name of the Enquiry History Table i.e,wp_enquiry_history.
 *
 * @return [string] table name: wp_enquiry_history.
 */

function getEnquiryHistoryTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_history';
}
/**
 * This function returns name of the Enquiry Meta Table i.e,wp_enquiry_meta.
 *
 * @return [string] table name: wp_enquiry_meta.
 */

function getEnquiryMetaTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_meta';
}
/**
 * This function returns name of the Enquiry Products Table i.e,wp_enquiry_products.
 *
 * @return [string] table name: wp_enquiry_products.
 */
function getEnquiryProductsTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_products';
}
/**
 * This function returns name of the Enquiry Quotation Products Table i.e,wp_enquiry_quotation.
 *
 * @return [string] table name: wp_enquiry_quotation.
 */
function getQuotationProductsTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_quotation';
}

/**
 * This function returns name of the Quotation Version Table i.e,wp_enquiry_quotation_version.
 *
 * @return [string] table name: wp_enquiry_quotation_version.
 */
function getVersionTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_quotation_version';
}
/**
 * This function returns name of the Enquiry Thread Table i.e,wp_enquiry_thread.
 *
 * @return [string] table name: wp_enquiry_thread.
 */

function getEnquiryThreadTable()
{
    global $wpdb;
    return $wpdb->prefix.'enquiry_thread';
}
