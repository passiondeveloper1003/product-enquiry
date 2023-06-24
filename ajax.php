<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!defined('DOING_AJAX')) {
    return; // Exit if accessed directly
}
//Ajax for CSV Generation
add_action('wp_ajax_wdm_return_rows', 'quoteupReturnRows');

// Ajax to remove red dot ('pep-update-dot') from menu, Settings sub menu and What's New tab.
add_action('wp_ajax_wdm_whats_new_visited', 'quoteup_whats_new_tab_visited');

//Ajax to add products in enquiry cart no longer in use.
add_action('wp_ajax_wdm_add_product_in_enq_cart', 'quoteupAddProductInEnqCart');
add_action('wp_ajax_nopriv_wdm_add_product_in_enq_cart', 'quoteupAddProductInEnqCart');

//Ajax to update cart
add_action('wp_ajax_wdm_update_enq_cart_session', 'quoteupUpdateEnqCartSession');
add_action('wp_ajax_nopriv_wdm_update_enq_cart_session', 'quoteupUpdateEnqCartSession');

// Ajax to update the enquriy cart in bulk.
add_action('wp_ajax_wdm_bulk_update_enq_cart_session', 'quoteupBulkUpdateEnqCartSession');
add_action('wp_ajax_nopriv_wdm_bulk_update_enq_cart_session', 'quoteupBulkUpdateEnqCartSession');

//Ajax to Migrate Scripts
add_action('wp_ajax_migrateScript', 'migrateScript');

//Ajax for Nonce Validation after Send button click
add_action('wp_ajax_quoteupValidateNonce', 'quoteupValidateNonce');
add_action('wp_ajax_nopriv_quoteupValidateNonce', 'quoteupValidateNonce');

//Ajax for submitting enquiry form
add_action('wp_ajax_quoteupSubmitWooEnquiryForm', 'quoteupSubmitWooEnquiryForm');
add_action('wp_ajax_nopriv_quoteupSubmitWooEnquiryForm', 'quoteupSubmitWooEnquiryForm');

//Ajax to send reply to customer
add_action('wp_ajax_wdmSendReply', 'quoteupSendReply');
add_action('wp_ajax_nopriv_wdmSendReply', 'quoteupSendReply');

//Ajax to update customer data on enquiry edit page on admin side.
add_action('wp_ajax_modify_user_data', 'quoteupModifyUserQuoteData');

/*
 * Ajax To set the global setting option of add_to_cart to individual product.
 * Ajax To set the global setting option of quoteup_enquiry to individual product.
 * No longer in use.
 */
add_action('wp_ajax_wdm_set_add_to_cart_value', 'quoteupSetAddToCartValue');

/*
 * Ajax to add product in Enquiry/Quote Cart currently in use.
 */
add_action('wp_ajax_wdm_trigger_add_to_enq_cart', 'wdmTriggerAddToEnqCart');
add_action('wp_ajax_nopriv_wdm_trigger_add_to_enq_cart', 'wdmTriggerAddToEnqCart');
/*
 * Ajax call to fetch variation details and display those.
 */
add_action('wp_ajax_get_variations', 'getVariationsDropdown');

/*
* Ajax for the Search bar for products
*/
add_action('wp_ajax_woocommerce_wpml_json_search_products_and_variations', array('Includes\Admin\QuoteupCreateDashboardQuotation', 'jsonSearchProductsAndVariations'));

/**
 * Ajax to provide the quote history data to be displayed on the frontend.
 */
add_action('wp_ajax_paginateQuote', 'paginateQuote');

/**
 * Ajax to display the enquiry in the modal.
 */
add_action('wp_ajax_popup_modal_enquiry', 'popupModalEnquiry');

/**
 * Ajax callback for adding products in cart.
 */
function wdmTriggerAddToEnqCart()
{
    quoteupAddProductInEnqCart();
}

/**
 * This function gets the Variation details of the Variable product in the search bar of the product in create quotation through admin side.
 * creates global variable for variable product.
 * we are using quoteupVariationDropdown() instead of woocommerce_variable_add_to_cart(). quoteupVariationDropdown() is just a copy of woocommerce_variable_add_to_cart() loading our template instead of woocommerce variable.php
 * woocommerce_variable_add_to_cart() includes woocommerce/templates/single-product/add-to-cart/variable.php. This file has a form tag and dropdowns are shown in a form tag. Since we are already inside a table, form tag can not be used here and therefore, we are creating a div tag which is very similar to form tag created in variable.php
 */
function getVariationsDropdown()
{
    $variationData = $_POST['perProductDetail'];
    $language = isset($variationData['language']) ? $variationData['language'] : '';
    /**
     * This action is documented in /product-enquiry-pro/includes/class-quoteup-wpml-compatibility.php.
     */
    do_action('quoteup_change_lang', $language);
    ob_start();
    $productID = $variationData['productID'];
    $count = $variationData['count'];
    $variationID = $variationData['variationID'];
    $productImage = $variationData['product_image'];

    if (isset($GLOBALS['product'])) {
        $GLOBALS['oldProduct'] = $GLOBALS['product'];
    }

    //Defining a global variable here because quoteupVariationDropdown() needs a global variable $product
    $GLOBALS[ 'product' ] = wc_get_product($productID);
    /*
     * Below WC 2.6, we also need global $post variable because it is used in variable.php
     */
    if (version_compare(WC_VERSION, '2.6', '<')) {
        $GLOBALS[ 'post' ] = get_post($productID);
    }
    $product = $GLOBALS[ 'product' ];
    // Get Available variations?
    $get_variations = sizeof($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);
    $available_variations = $get_variations ? $product->get_available_variations() : false;

    /*
     * we are using quoteupVariationDropdown() instead of woocommerce_variable_add_to_cart(). quoteupVariationDropdown() is just a copy of woocommerce_variable_add_to_cart() loading our template instead of woocommerce variable.php
     *
     * woocommerce_variable_add_to_cart() includes woocommerce/templates/single-product/add-to-cart/variable.php. This file has a form tag and dropdowns are shown in a form tag. Since we are already inside a table, form tag can not be used here and therefore, we are creating a div tag which is very similar to form tag created in variable.php
     */
    ?>
    <div class="product">
        <div id="variation-<?php echo $count ?>" class="variations_form cart" data-product_id="<?php echo absint($productID); ?>" data-product_variations="<?php echo htmlspecialchars(json_encode($available_variations)) ?>">
    <?php
            $variation = $variationData[ 'rawVariationAttributes' ];
            quoteupVariationDropdown($count, $variationID, $productImage, $productID, $product, $variation);
    ?>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    echo json_encode($html);

    //Reset product data
    if (isset($GLOBALS['oldProduct'])) {
        $GLOBALS['product'] = $GLOBALS['oldProduct'];
    }

    /**
     * This action is documented in /product-enquiry-pro/includes/admin/class-quoteup-create-quotation-from-dashboard.php.
     */
    do_action('quoteup_reset_lang');
    die;
}

/**
 * Verify nonce for CSV generation.
 */
function checkSecurity()
{
    if (!wp_verify_nonce($_POST[ 'security' ], 'quoteup-nonce')) {
        die('SECURITY_ISSUE');
    }

    if (!(quoteupIsCurrentUserHavingManageOptionsCap() || quoteupIsCurrentLoggedInUserVendor())) {
        die('SECURITY_ISSUE');
    }
}

/**
 * This function returns Meta columns to be included in CSV
 * Returns all the meta keys from meta table other than
 * enquiry_lang_code,quotation_lang_code,_unread_enquiry.
 * Also disable quotation is set by User in the settings then the admin_quote_created meta
 * keys are not sent.
 *
 * @param  string $metaTable Meta table name
 * @param  array  $form      quoteUp Settings
 * @return array  $metaHeadings meta keys from the meta table depending upon the
 * conditions
 */
function getMetaColumns($metaTable, $form)
{
    global $wpdb;
    $sql = "SELECT DISTINCT meta_key FROM $metaTable";
    $sql = apply_filters('quoteup_meta_cols_for_csv_query', $sql);
    $metaKeys = $wpdb->get_col($sql);
    $metaHeadings = array();

    foreach ($metaKeys as $metaKey) {
        switch ($metaKey) {
            case 'enquiry_lang_code':
            case 'quotation_lang_code':
            case '_unread_enquiry':
                break;

            default:
                array_push($metaHeadings, $metaKey);
                break;
        }
    }

    // If quotation module is deactivated
    $del_val = '_admin_quote_created';
    if ($form['enable_disable_quote'] && array_search($del_val, $metaHeadings) !== false) {
        $key = array_search($del_val, $metaHeadings);
        unset($metaHeadings[$key]);
    }
    return $metaHeadings;
}

/**
 * This function sets products to be included in CSV and string to get price from array
 * Gets the Product details of the Enquiry created at both admin and frontend sides to
 * include in the CSV.
 *
 * @param string &$csvProducts      blank string which will be set as products array
 * @param string &$price            blank string which will be set as string to get price
 * @param array  $form              settings of product
 * @param array  $individualEnquiry enquiry data
 */
function getCsvProducts(&$csvProducts, &$price, $form, $individualEnquiry)
{
    // If quotation module is activated
    if ($form['enable_disable_quote'] == 0) {
        $csvProducts = getQuoteProducts($individualEnquiry['enquiry_id']);
        $price = 'newprice';
    }

    if (empty($csvProducts)) {
        $csvProducts = getEnquiryProducts($individualEnquiry['enquiry_id']);
        $price = 'price';
    }
}

/**
 * This function returns title of product
 *
 * @param  array $data product details
 * @return string       product title
 */
function getProductTitle($data)
{
    $title = get_the_title($data['product_id']);
    if (empty($title)) {
        $title = get_the_title($data['product_title']);
    }

    if (isset($data[ 'variation_id' ]) && $data[ 'variation_id' ] != '' && $data[ 'variation_id' ] != 0) {
        $variationString = printVariations($data);
        $variationString = preg_replace(']<br>]', ',', $variationString); // Used to replace br tag with ','
        $variationString = preg_replace(']<b>]', '', $variationString); // Used to replace b tag with ''
        $variationString = preg_replace(']</b>]', '', $variationString); // Used to replace </b> tag with ''
        $variationString = preg_replace('],]', '', $variationString, 1); // Used to remove first ,
        $title = $title.' - '. $variationString;
    }

    return $title;
}

/**
 * This function returns meta columns values for each enquiry
 *
 * First it fetches all meta_values in the entries of the specified meta_keys.
 * Checks if the meta_value is admin_quote_created.
 * It sets the Admin Created Quote key of $csvData array to 'yes'.
 *
 * @param  array  $csvData      values of other csv columns
 * @param  array  $metaHeadings Meta columns
 * @param  int    $enquriyID    enquiry ID
 * @param  string $metaTable    metaTbale name.
 * @return array               values of CSV columns with meta columns values
 */
function getMetaColumnsValues($csvData, $metaHeadings, $enquriyID, $metaTable)
{
    global $wpdb;

    if (!empty($metaHeadings)) {
        foreach ($metaHeadings as $value) {
            $sql = "SELECT meta_value FROM $metaTable WHERE enquiry_id=$enquriyID AND meta_key = '$value'";
            $result = $wpdb->get_var($sql);
            if ($value == '_admin_quote_created') {
                $value = 'Admin Created Quote';
                if ($result) {
                    $result = 'Yes';
                } else {
                    $result = 'No';
                }
            }
            $csvData[$value] = $result;
        }
    }

    return $csvData;
}
/**
 * This function gets the status of telephone and date fields from the Quoteup settings.
 */
function getTelephoneAndDateFieldStatus($individualEnquiry, $form, &$csvData)
{
    if (isset($form[ 'enable_telephone_no_txtbox' ]) && $form[ 'enable_telephone_no_txtbox' ]) {
        $csvData['Phone Number'] = quoteupGetValueFromArray($individualEnquiry, 'phone_number');
    }

    if (isset($form[ 'enable_date_field' ]) && $form[ 'enable_date_field' ]) {
        $csvData['Date'] = quoteupGetValueFromArray($individualEnquiry, 'date_field');
    }
}

/*
 * This function is a Callback for CSV generation ajax
 * When the enquiry details are needed to be exported in CSV format.
 * Called onclick apply of the Bulk actions on admin side.
 *
 * It first checks the nonce and also the User role if he is an admin.
 * get all ids in array for CSV generation.
 * Gets all the enquiries of the required ids with required status.
 * Gets the metakeys according to the settings which are set by the User.
 * Gets the Products details associated with the quotes from both admin and frontend sides.
 * Get the Product Title of the Products involved.
 * Get if the telephone and date fields are set in the QuoteUp settings or not.
 * Get Meta values to specify if admin created quote or not.
 * Get the last added entry of enquiry.
 * Updates the CSV data.
 * echos the CSV data in the json format.
 * This can be seen in the Preview pdf.
 */
if (!function_exists('quoteupReturnRows')) {
    function quoteupReturnRows()
    {
        checkSecurity();
        global $wpdb;
        $ids = array();

        $enquiryTable = getEnquiryDetailsTable();
        $metaTable = getEnquiryMetaTable();
        $form = quoteupSettings();
        $arr = getarr($ids);
        $sql = "SELECT * FROM $enquiryTable ";

        $qry = appendConditionInQUery($arr);

        $sql = $sql.$qry;
        $enquiryData = $wpdb->get_results($sql, ARRAY_A);

        $metaHeadings = getMetaColumns($metaTable, $form);

        $data = array();
        foreach ($enquiryData as $individualEnquiry) {
            $csvProducts = '';
            $price = '';
            $quantity = '';
            $title    = '';

            getCsvProducts($csvProducts, $priceKey, $form, $individualEnquiry);

            $individualEnquiry = apply_filters('quoteup_individual_enquiry_data', $individualEnquiry, $individualEnquiry['enquiry_id']);

            if (empty($csvProducts) && apply_filters('quoteup_should_skip_no_products', true, $individualEnquiry)) {
                continue;
            }

            if (!empty($csvProducts)) {
                $title    = getProductTitle($csvProducts[0]);
                $quantity = $csvProducts[0]['quantity'];
                $price    = $csvProducts[0][$priceKey] ;
            }

            $form = quoteupSettings();

            $csvData = array(
                'enquiry ID' => $individualEnquiry['enquiry_id'],
                'Enquiry Date' => $individualEnquiry['enquiry_date'],
                'Enquiry IP' => quoteupGetValueFromArray($individualEnquiry, 'enquiry_ip'),
                'Customer Name' => quoteupGetValueFromArray($individualEnquiry, 'name'),
                'Email' => quoteupGetValueFromArray($individualEnquiry, 'email'),
                'Product Name' => $title,
                'Quantity' => $quantity,
                'Price' => $price,
            );

            getTelephoneAndDateFieldStatus($individualEnquiry, $form, $csvData);

            $csvData['Subject'] = quoteupGetValueFromArray($individualEnquiry, 'subject');
            $csvData['Message'] = quoteupGetValueFromArray($individualEnquiry, 'message');

            $enquriyID = $individualEnquiry['enquiry_id'];
            $csvData   = getMetaColumnsValues($csvData, $metaHeadings, $enquriyID, $metaTable);

            // If quotation module is activated
            if ($form['enable_disable_quote'] == 0) {
                global $quoteupManageHistory;
                $quoteStatus = $quoteupManageHistory->getLastAddedHistory($individualEnquiry['enquiry_id']);
                if ($quoteStatus != null && is_array($quoteStatus)) {
                    $quoteStatus = $quoteStatus[ 'status' ];
                }
                $csvData['Expiration Date'] = $individualEnquiry['expiration_date'];
                $csvData['Order ID'] = $individualEnquiry['order_id'];
                $csvData['Total'] = $individualEnquiry['total'];
                $csvData['Quote Status'] = $quoteStatus;
            }

            $csvData = apply_filters('quoteup_enquiry_meta_data', $csvData, $enquriyID, $csvProducts, $individualEnquiry);
            array_push($data, $csvData);

            $firstProduct = true;
            foreach ($csvProducts as $individualProduct) {
                if ($firstProduct) {
                    $firstProduct = false;
                    continue;
                }
                $title = getProductTitle($individualProduct);

                $productsData = array(
                'enquiry ID' => '',
                'Enquiry Date' => '',
                'Enquiry IP' => '',
                'Customer Name' => '',
                'Email' => '',
                'Product Name' => $title,
                'Quantity' => $individualProduct['quantity'],
                'Price' => $individualProduct[$priceKey],
                );

                array_push($data, $productsData);
            }
            
            $blankArray = array();

            array_push($data, $blankArray);
        }

        $data = apply_filters('quoteup_csv_export_data', $data);
        echo json_encode($data);
        die();
    }
}

function quoteup_whats_new_tab_visited()
{
    update_option('is_whats_new_tab_visited', 'yes');
    echo 'valid';
    die();
}

/**
 * This function is used to append the where clause in query depending upon condition.
 *
 * If the ids are given to the function it appends the enquiry id with the where clause of
 * the query
 * If the status is set it gets the ids with the same status and for those enquiry ids it
 * creates the where clause.
 *
 * @param [string] $arr [enquiry ids appended in a single string]
 *
 * @return [string] $qry [where clause query according to the condition]
 */
function appendConditionInQUery($arr)
{
    $qry = '';
    if (isset($_POST[ 'status' ])) {
        $status = filter_var($_POST[ 'status' ], FILTER_SANITIZE_STRING);
    }
    if (!empty($arr) && $arr != '') {
        $qry = "WHERE enquiry_id in ($arr)";
    } elseif (isset($status) && 'all' != $status) {
        $resultSet = getSqlStatus($status);
        if (isset($resultSet)) {
            $qry = "WHERE enquiry_id in ($resultSet)";
        }
    }

    return $qry;
}

/**
 * This function gives sql query as per status.
 *
 * Gets the rows from the wp_enquiry_history table with the specific status.
 *
 * @param  [string] $filter [Enquiry Status]
 * @return [string] $resultSet  [enquiry Ids of the required status all appended in single   * string]
 */
function getSqlStatus($filter)
{
    global $wpdb;
    $tableName = getEnquiryHistoryTable();

    $sql = "SELECT s1.enquiry_id
                FROM $tableName s1
                LEFT JOIN $tableName s2 ON s1.enquiry_id = s2.enquiry_id
                AND s1.id < s2.id
                WHERE s2.enquiry_id IS NULL AND s1.status ='".$filter."'AND s1.enquiry_id > 0 AND s1.ID > 0";
    $res = $wpdb->get_col($sql);
    if (isset($res)) {
        $resultSet = implode(',', $res);
    }

    return $resultSet;
}

/**
 * get all ids in array for CSV generation.
 * returns them appended in a single string.
 *
 * @param [array] $ids [enquiry ids]
 *
 * @return [string] $arr [all the enquiry ids appended to a string]
 */
function getarr($ids)
{
    if (isset($_POST[ 'ids' ])) {
        $ids = $_POST[ 'ids' ];
    }
    if ($ids == '') {
        $arr = '';
    } else {
        $arr = implode(',', $ids);
    }

    return $arr;
}

/**
 * Trim column names for CSV generation.
 *
 * @param [type] $ids [description]
 */
function quoteupTrimNamesOfAllColumns(&$array_item)
{
    $array_item = trim($array_item);
}

/**
 * This function is used to trace through each enquriy id for CSV generation.
 *
 * @param [type] $result  [description]
 * @param [type] $columns [description]
 */
function forEachEnquiry($result, $columns)
{
    foreach ($result as &$single_result) {
        $single_result->name = apply_filters('pep_export_csv_customer_name_data', $single_result->name);
        $single_result->name = apply_filters('quoteup_export_csv_customer_name_data', $single_result->name);

        $single_result->email = apply_filters('pep_export_csv_customer_email_data', $single_result->email);
        $single_result->email = apply_filters('quoteup_export_csv_customer_email_data', $single_result->email);

        $single_result->subject = apply_filters('pep_export_csv_subject_data', $single_result->subject);
        $single_result->subject = apply_filters('quoteup_export_csv_subject_data', $single_result->subject);

        $single_result->message = apply_filters('pep_export_csv_message_data', $single_result->message);
        $single_result->message = apply_filters('quoteup_export_csv_message_data', $single_result->message);

        $single_result->product_details = apply_filters('pep_export_csv_product_details_data', $single_result->product_details);
        $single_result->product_details = apply_filters('quoteup_export_csv_product_details_data', $single_result->product_details);

        foreach ($columns as $single_custom_column) {
            $single_result->{$single_custom_column} = apply_filters('pep_export_csv_'.$single_custom_column.'_data', $single_result->{$single_custom_column});
            $single_result->{$single_custom_column} = apply_filters('quoteup_export_csv_'.$single_custom_column.'_data', $single_result->{$single_custom_column});
        }
    }
}


/**
 * This function is used to convert variation name and value in required format
 * array[variation name] = variation value;.
 * Split each string of individual variation in the key value pair to form newVariation array
 *
 * @param [array] $variation_detail [Details of Variation Product]
 *
 * @return [array] $newVariation [Details of Variable Products in key value pair form].
 */
function getNewVariation($variation_detail)
{
    foreach ($variation_detail as $individualVariation) {
        $keyValue = explode(':', $individualVariation);
        $keyValue[0] = stripslashes($keyValue[0]);
        $keyValue[1] = stripcslashes($keyValue[1]);
        $newVariation[trim($keyValue[0])] = trim($keyValue[1]);
    }

    return $newVariation;
}

/**
 * This function returns author_email field.
 *
 * @return [string] Author Email.
 */
function getAuthorMail()
{
    return isset($_POST['author_email']) ? filter_var($_POST['author_email'], FILTER_SANITIZE_EMAIL) : "";
}

/**
 * Callback for Add products to enquiry cart ajax.
 * Checks the Session variable product_info has any product or not.
 * Get the Product Details to put in Cart.
 * If Variable Product get the variation details.
 * Checks whether cart already contains any product if yes increase the session variable of product count in cart.
 * Add the details of new added product in cart in the session variable of product info.
 * Send the response of the product count in cart.
 */
function quoteupAddProductInEnqCart()
{
    global $quoteup;
    // @session_start();
    $data = $_POST;

    $prod = $quoteup->wcCartSession->get('wdm_product_info') != null ? $quoteup->wcCartSession->get('wdm_product_info') : array();
    //$prod = isset($_SESSION['wdm_product_info']) ? $_SESSION['wdm_product_info'] : array();

    $product_id       = filter_var($_POST[ 'product_id' ], FILTER_SANITIZE_NUMBER_INT);
    $prod_quant       = filter_var($_POST[ 'product_quant' ], FILTER_SANITIZE_NUMBER_INT);
    $title            = get_the_title($product_id);
    $remark           = isset($_POST['remark']) ? filter_var($_POST['remark'], FILTER_SANITIZE_STRING) : '';
    $id_flag          = 0;
    $counter          = 0;
    $authorEmail      = getAuthorMail();
    $variation_id     = filter_var($_POST['variation'], FILTER_SANITIZE_NUMBER_INT);
    $variation_detail = '';

    //Variable Product
    if ($variation_id != '' && $variation_id != 0) {
        $product = wc_get_product($variation_id);
        $variation_detail = array_map('sanitize_text_field', $_POST['variation_detail']);
        $variation_detail = getNewVariation($variation_detail);
    } else {
        $product = wc_get_product($product_id);
    }
    $price = quoteupGetPriceToDisplay($product, $prod_quant);

    //End of Variable Product
    if ($quoteup->wcCartSession->get('wdm_product_info') != null) {
    //if (isset($_SESSION[ 'wdm_product_info' ]) && !empty($_SESSION[ 'wdm_product_info' ])) {
        $flag_counter = quoteupGetFlag($product_id, $id_flag, $counter, $variation_detail, $variation_id);
        $id_flag = $flag_counter[ 'id_flag' ];
        $counter = $flag_counter[ 'counter' ];
    }

    if ($id_flag == 0) {
        if ($quoteup->wcCartSession->get('wdm_product_count') != null) {
        //if (isset($_SESSION[ 'wdm_product_count' ]) && $_SESSION[ 'wdm_product_count' ] != '') {
            $counter = $quoteup->wcCartSession->get('wdm_product_count');
            //$counter = $_SESSION[ 'wdm_product_count' ];
        }
        $prod[ $counter ] = array('id' => $product_id,
            'title' => $title,
            'price' => $price,
            'quantity' => $prod_quant,
            'remark' => $remark,
            'variation_id' => $variation_id,
            'variation' => $variation_detail,
            'author_email' => $authorEmail,
            );
        $prod[ $counter ] = apply_filters('wdm_filter_product_data', $prod[ $counter ], $data);
        $quoteup->wcCartSession->set('wdm_product_info', $prod);
        //$_SESSION[ 'wdm_product_info' ] = $prod;
        getWpmlLanguage();
        setProductCount();
    } else {
        setProductInfo($counter, $remark, $prod_quant, $price);
    }
    //echo $quoteup->wcCartSession->get('wdm_product_info');

    $productCount     = $quoteup->wcCartSession->get('wdm_product_count');
    $productCountText = $productCount > 1 ? sprintf(__('%d products added in Enquiry Cart', QUOTEUP_TEXT_DOMAIN), $productCount) : sprintf(__('%d product added in Enquiry Cart', QUOTEUP_TEXT_DOMAIN), $productCount);

    echo json_encode(array(
            'status'                => 'SUCCESS',
            'product_count'         => $productCount,
            'product_count_in_text' => $productCountText
        )
    );

    //echo $_SESSION[ 'wdm_product_count' ];
    die;
}

/**
 * This function is used to set cart language if WPML is active.
 * sets the session variable for the language specified.
 */
function getWpmlLanguage()
{
    global $quoteup;
    $quoteup->wcCartSession->set('wdm_cart_language', $_POST['language']);
    //$_SESSION[ 'wdm_cart_language' ] = $_POST['language'];
}

/**
 * This function is used to update total count of products in cart.
 *
 * @param [int] $productCount [total number of products in cart]
 */
function setProductCount()
{
    global $quoteup;

    if ($quoteup->wcCartSession->get('wdm_product_count') != null && is_int($quoteup->wcCartSession->get('wdm_product_count'))) {
    //if (isset($_SESSION[ 'wdm_product_count' ]) && !empty($_SESSION[ 'wdm_product_count' ]) && is_int($_SESSION[ 'wdm_product_count' ])) {
        $count = $quoteup->wcCartSession->get('wdm_product_count');
        $quoteup->wcCartSession->set('wdm_product_count', $count+1);
        //$_SESSION[ 'wdm_product_count' ] = $_SESSION[ 'wdm_product_count' ] + 1;
    } else {
        $quoteup->wcCartSession->set('wdm_product_count', 1);
        //$_SESSION[ 'wdm_product_count' ] = 1;
    }
}

/**
 * This function is used to update remart, product quantity and price in cart.
 *
 * @param [type] $counter    [description]
 * @param [type] $remark     [description]
 * @param [type] $prod_quant [description]
 * @param [type] $price      [description]
 */
function setProductInfo($counter, $remark, $prod_quant, $price)
{
    global $quoteup;
    $prod = $quoteup->wcCartSession->get('wdm_product_info');
    if ($remark != '') {
        $prod[ $counter ][ 'remark' ] = $remark;
        //$_SESSION[ 'wdm_product_info' ][ $counter ][ 'remark' ] = $remark;
    }
    $prod[ $counter ][ 'quantity' ] += $prod_quant;
    //$_SESSION[ 'wdm_product_info' ][ $counter ][ 'quant' ] += $prod_quant;
    $prod[ $counter ][ 'price' ] = $price;
    //$_SESSION[ 'wdm_product_info' ][ $counter ][ 'price' ] = $price;
    $quoteup->wcCartSession->set('wdm_product_info', $prod);
}

/**
 * Checks whether product has already been added to Enquiry/Quote cart.
 *
 * If product is already there in the Enquiry cart, returns id_flag as 1, else returns id_flag as 0.
 *
 * @param  int  $product_id     Product Id.
 * @param  int  $id_flag        0 or 1 depending upon product present in cart or not.
 * @param  array    $variation_detail   Numeric array containing variation attributes.
 * @param  int  $variation_id   Single variation id.
 *
 * @return array Return array containing id_flag and $counter.
 */
function quoteupGetFlag($product_id, $id_flag, $counter, $variation_detail, $variation_id)
{
    global $quoteup;
    $prod = $quoteup->wcCartSession->get('wdm_product_info');

    for ($search = 0; $search < count($prod); ++$search) {
        if ($product_id == $prod[ $search ][ 'id' ]) {
            if ($variation_detail != '') {
                if ($prod[$search]['variation'] == $variation_detail && $prod[$search]['variation_id'] == $variation_id) {
                //if ($_SESSION['wdm_product_info'][$search]['variation'] == $variation_detail && $_SESSION['wdm_product_info'][$search]['variation_id'] == $variation_id) {
                    $id_flag = 1;
                    $counter = $search;
                }
            } else {
                $id_flag = 1;
                $counter = $search;
            }
        }
    }

    return array(
        'id_flag' => $id_flag,
        'counter' => $counter,
    );
}

/**
 * THis function returns the quantity to update in session.
 *
 * @param [int] $pid [Product ID]
 *
 * @return [int] $quant [Product quantity]
 */
function getQuantity()
{
    if (isset($_POST[ 'clickcheck' ]) && $_POST[ 'clickcheck' ] == 'remove') {
        $quant = 0;
    } else {
        $quant = filter_var($_POST[ 'quantity' ], FILTER_SANITIZE_NUMBER_INT);
    }

    return $quant;
}

function getRemark()
{
    $remark = '';
    if (isset($_POST[ 'remark' ])) {
        $remark = stripcslashes($_POST[ 'remark' ]);
    }

    return $remark;
}

function getProduct($pid, $vid)
{
    $product = wc_get_product($pid);
    if ($vid != '') {
        $product = wc_get_product($vid);
    }

    return $product;
}

/**
 * Callback for Update cart ajax.
 * Whenever any product is removed from the enquiry cart, this function is called through ajax.
 */
function quoteupUpdateEnqCartSession()
{
    global $quoteup;

    $prod = $quoteup->wcCartSession->get('wdm_product_info');
    $prodCount = $quoteup->wcCartSession->get('wdm_product_count');

    // @session_start();
    $pid = filter_var($_POST[ 'product_id' ], FILTER_SANITIZE_NUMBER_INT);
    $vid = filter_var($_POST['product_var_id'], FILTER_SANITIZE_NUMBER_INT);
    $variation_detail = $_POST['variation'];
    $variation_detail = sanitizeVariationDetail($vid, $variation_detail);
    $quant = getQuantity();
    $remark = getRemark();
    $product = getProduct($pid, $vid);
    $pri = quoteupGetPriceToDisplay($product, $quant);
    $shouldHidePrice = quoteupShouldHidePrice($pid);
    $countProductInfo = isset($prod) ? count($prod) : 0;
    $formData = get_option('wdm_form_data');
    //$countProductInfo = isset($_SESSION[ 'wdm_product_info' ]) ? count($_SESSION[ 'wdm_product_info' ]) : 0;
    for ($search = 0; $search < $countProductInfo; ++$search) {
        if ($pid == $prod[ $search ][ 'id' ]) {
        //if ($pid == $_SESSION[ 'wdm_product_info' ][ $search ][ 'id' ]) {
            //If this is not the product to update
            if ($vid != '' && ($prod[$search]['variation_id'] != $vid || $prod[$search]['variation'] != $variation_detail)) {
                continue;
            }

            // If remove is triggered
            if ($quant == 0) {
                array_splice($prod, $search, 1);
                //array_splice($_SESSION['wdm_product_info'], $search, 1);
                $prodCount = $prodCount - 1;
                //$_SESSION['wdm_product_count'] = $_SESSION['wdm_product_count'] - 1;
            } else {
                $prod[$search]['quantity'] = $quant;
                //$_SESSION['wdm_product_info'][$search]['quant'] = $quant;
                $prod[ $search ][ 'price' ] = $pri;
                //$_SESSION[ 'wdm_product_info' ][ $search ][ 'price' ] = $pri;
                $prod[$search]['remark'] = $remark;
                //$_SESSION['wdm_product_info'][$search]['remark'] = $remark;
            }

            if ($prodCount == 0) {
                $quoteup->wcCartSession->set('wdm_cart_language', null);
            }
            /*if ($_SESSION['wdm_product_count'] == 0) {
                unset($_SESSION['wdm_cart_language']);
            }*/

            $quoteup->wcCartSession->set('wdm_product_info', $prod);
            $quoteup->wcCartSession->set('wdm_product_count', $prodCount);
            $price            = getPriceToSend($shouldHidePrice, $pri, $quant);
            $productCountText = $prodCount > 1 ? sprintf(__('%d products added in Enquiry Cart', QUOTEUP_TEXT_DOMAIN), $prodCount) : sprintf(__('%d product added in Enquiry Cart', QUOTEUP_TEXT_DOMAIN), $prodCount);
            $total            = $quoteup->quoteupEnquiryCart->getEnquiryCartTotal();
            $totalString      = $quoteup->quoteupEnquiryCart->getMiniCartTotalText($total);

            echo json_encode(array('product_id' => $pid, 'variation_id' => $vid, 'variation_detail' => $variation_detail, 'price' => $price, 'status'     => 'COMPLETED', 'count'    => $prodCount, 'product_count_in_text' => $productCountText, 'total' => $total, 'totalString' => $totalString, 'customLabel' => $formData['custom_label'], 'cartCustomLabel' => $formData['cart_custom_label']));
           
            die();
        }
    }
}

/**
 * Update the enquiry cart in bulk using Ajax.
 *
 * Modify the product info session when enquiry cart is updated on the
 * enquiry cart page using Ajax.
 *
 * @since 6.5.0
 */
function quoteupBulkUpdateEnqCartSession()
{
    global $quoteup;

    $productsInfo  = $quoteup->wcCartSession->get('wdm_product_info');
    $ajaxResponse   = array();

    if (!empty($_POST['products_data'])) {
        $postProductsData = $_POST['products_data'];

        foreach ($productsInfo as $index => $singleProductInfo) {
            $productId = $singleProductInfo['id'];

            // Generate current product hash.
            if (empty($singleProductInfo['variation_id'])) {
                $productHash = quoteupReturnProductHash($productId);
            } else {
                $productHash = quoteupReturnProductHash($productId, $singleProductInfo['variation_id'], $singleProductInfo['variation']);
            }
            
            if (array_key_exists($productHash, $postProductsData)) {
                // Filter/ Sanitize the POST data.
                $postSingleProductData = $postProductsData[$productHash];
                $postVariationId       = filter_var($postSingleProductData['product_var_id'], FILTER_SANITIZE_NUMBER_INT);
                if (is_array($postSingleProductData['variation'])) {
                    $postVariation = array_map('sanitize_text_field', $postSingleProductData['variation']);
                } else {
                    $postVariation = '';
                }
                $postQuantity = filter_var($postSingleProductData['quantity'], FILTER_SANITIZE_NUMBER_INT);
                $postRemark   = sanitize_textarea_field($postSingleProductData['remark']);

                // Get price.
                $product = getProduct($productId, $postVariationId);
                $price   = quoteupGetPriceToDisplay($product, $postQuantity);

                // Modify the data in session 'wdm_product_info'.
                $productsInfo[$index]['price']    = $price;
                $productsInfo[$index]['quantity'] = $postQuantity;
                $productsInfo[$index]['remark']   = $postRemark;

                // Prepare Ajax response.
                $shouldHidePrice = quoteupShouldHidePrice($productId);
                $priceHTML       = getPriceToSend($shouldHidePrice, $price, $postQuantity);

                $responseProductsData[$productHash] = array(
                    'product_id'       => $productId,
                    'variation_id'     => $postVariationId,
                    'variation_detail' => $postVariation,
                    'price'            => $priceHTML,
                );
            }
        }

        // Update the data in session 'wdm_product_info'.
        $quoteup->wcCartSession->set('wdm_product_info', $productsInfo);

        $totalPriceHTML                = $quoteup->quoteupEnquiryCart->getEnquiryCartTotal();
        $ajaxResponse['products_data'] = $responseProductsData;
        $ajaxResponse['total']         = $totalPriceHTML;
        $ajaxResponse['status']        = 'SUCCESS';

        /**
         * Filter to modify the Ajax response for bulk enquiry cart update.
         *
         * @since 6.5.0
         *
         * @param  array  $ajaxResponse  Ajax reponse.
         */
        $ajaxResponse = apply_filters('bulk_update_enq_cart_ajax_response', $ajaxResponse);

        echo json_encode($ajaxResponse);
        die();
    }
}

function sanitizeVariationDetail($vid, $variation_detail)
{
    if (!empty($vid)) {
        foreach ($variation_detail as $key => $value) {
            $variation_detail[$key] = stripcslashes($value);
        }
    }

    return $variation_detail;
}

function getPriceToSend($shouldHidePrice, $pri, $quant)
{
    if (false == $shouldHidePrice) {
        $price = wc_price((float)$pri * $quant);
    } else {
        $price = '-';
    }

    return $price;
}

/*
 * Callback for script migration ajax
 * Not in use
 */
if (!function_exists('migrateScript')) {
    function migrateScript()
    {
        if (!wp_verify_nonce($_POST[ 'security' ], 'migratenonce')) {
            die('SECURITY_ISSUE');
        }

        if (!current_user_can('manage_options')) {
            die('SECURITY_ISSUE');
        }

        $migrated = get_option('wdm_enquiries_migrated');
        if ($migrated != 1) {
            global $wpdb;
            $enquiry_tbl = $wpdb->prefix.'enquiry_details';
            $enquiry_tbl_new = getEnquiryDetailsTable();
            $enquiry_meta_tbl = getEnquiryMetaTable();
            $enquiries = $wpdb->get_results("SELECT * FROM {$enquiry_tbl}");
            foreach ($enquiries as $enquiry) {
                $pid = $enquiry->product_id;
                $pname = $enquiry->product_name;
                $psku = $enquiry->product_sku;
                $price = get_post_meta($pid, '_regular_price', true);
                $id = $enquiry->enquiry_id;
                $sql = $wpdb->prepare("select meta_key,meta_value FROM {$enquiry_meta_tbl} WHERE enquiry_id=%d", $id);
                $meta = $wpdb->get_results($sql);

                $cust_name = $enquiry->name;
                $cust_email = $enquiry->email;
                $ip = $enquiry->enquiry_ip;
                $dt = $enquiry->enquiry_date;
                $sub = $enquiry->subject;
                $number = $enquiry->phone_number;
                $msg = $enquiry->message;
                $img_url = wp_get_attachment_url(get_post_thumbnail_id($pid));

                $products_arr = array();
                $products_arr[][ 0 ] = array('id' => $pid, 'title' => $pname, 'quant' => 1, 'sku' => $psku, 'img' => $img_url, 'price' => $price, 'remark' => '');
                $record = serialize($products_arr);
                $wpdb->insert(
                    $enquiry_tbl_new,
                    array('name' => $cust_name,
                    'email' => $cust_email,
                    'message' => $msg,
                    'phone_number' => $number,
                    'subject' => $sub,
                    'enquiry_ip' => $ip,
                    'product_details' => $record,
                    'enquiry_date' => $dt,
                    ),
                    array('%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    )
                );
                echo $insert_id = $wpdb->insert_id;

                foreach ($meta as $pair) {
                    $key = $pair->meta_key;
                    $value = $pair->meta_value;
                    $wpdb->insert(
                        $enquiry_meta_tbl,
                        array(
                        'enquiry_id' => $insert_id,
                        'meta_key' => $key,
                        'meta_value' => $value,
                        ),
                        array('%d',
                        '%s',
                        '%s',
                        )
                    );
                }
            }
            update_option('wdm_enquiries_migrated', 1);

            $table_name = getEnquiryHistoryTable();
            $enquiryDetailTable = getEnquiryDetailsTable();
            $sql = "SELECT enquiry_id,enquiry_date FROM  $enquiryDetailTable WHERE  enquiry_id NOT IN (SELECT enquiry_id FROM $table_name)";
            $oldEnquiryIDs = $wpdb->get_results($sql, ARRAY_A);
            foreach ($oldEnquiryIDs as $enquiryID) {
                $enquiry = $enquiryID[ 'enquiry_id' ];
                $date = $enquiryID[ 'enquiry_date' ];
                $table_name = getEnquiryHistoryTable();
                $performedBy = null;
                $wpdb->insert(
                    $table_name,
                    array(
                    'enquiry_id' => $enquiry,
                    'date' => $date,
                    'message' => '-',
                        'status' => 'Requested',
                    'performed_by' => $performedBy,
                    )
                );
            }
        }

        die();
    }
}

/**
 * Callback for nonce ajax.
 * When the send button is clicked for any enquiry on front-end , after the validation of the enquiry the nonce is checked through ajax call.
 */
function quoteupValidateNonce()
{
    $formSetting = quoteupSettings();
    if (isset($formSetting['deactivate_nonce']) && $formSetting['deactivate_nonce'] == 1) {
        echo 1;
    } else {
        echo check_ajax_referer('nonce_for_enquiry', 'security', false);
    }
    die();
}

/**
 * This function adds the current language (locale) value for the meta keys of languages in the meta table of the enquiry.
 *
 * @param int    $enquiryId     Current enquiry id
 * @param string $metaKey       meta keys for language (enquiry_lang_code,quotation_lang_code)
 * @param string $currentLoacle Locale for currently selected language.
 */
function addLanguageToEnquiryMeta($enquiryID, $metaKey, $currentLocale)
{
    global $wpdb;
    $metaTbl = getEnquiryMetaTable();

    if (isset($_POST['globalEnquiryID']) && $_POST['globalEnquiryID'] != 0) {
        $wpdb->update(
            $metaTbl,
            array(
                'meta_value' => $currentLocale,
                ),
            array(
                    'enquiry_id' => $_POST['globalEnquiryID'],
                    'meta_key' => $metaKey,
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
            'enquiry_id' => $enquiryID,
            'meta_key' => $metaKey,
            'meta_value' => $currentLocale,
            ),
            array(
            '%d',
            '%s',
            '%s',
            )
        );
    }
}

/**
 * This function adds the Meta details of the Product in the Enquiry Meta table.
 *
 * @param [int]    $enquiryID enquiry Id
 * @param [string] $metaKey   key value
 * @param [string] $metaValue Value associated with it
 */
function addEnquiryMeta($enquiryID, $metaKey, $metaValue)
{
    global $wpdb;
    $metaTbl = getEnquiryMetaTable();

    if (isset($_POST['globalEnquiryID']) && $_POST['globalEnquiryID'] != 0) {
        $wpdb->update(
            $metaTbl,
            array(
                'meta_value' => $metaValue,
                ),
            array(
                    'enquiry_id' => $_POST['globalEnquiryID'],
                    'meta_key' => $metaKey,
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
            'enquiry_id' => $enquiryID,
            'meta_key' => $metaKey,
            'meta_value' => $metaValue,
            ),
            array(
            '%d',
            '%s',
            '%s',
            )
        );
    }
}

/**
 * Callback for submitting enquiry form ajax.
 * When send button is clicked , and the Form validation and nonce validation are successful, this is ajax call for Enquiry form submission.
 * Checks if google captcha is enabled or not.
 * If yes Google secret key is fetched from the Settings and response from User and both of * them are verified, if verification unsuccessful give an error.
 * If successfully verified ,
 * Gets the Enquiry details through post, for attach files field checks the valid media file or not through ajax.
 * Get the product details for the enquiry whether it is single product or multiproduct
 * Deletes the folder in uploads for the current enquiry if already exists , create one and upload files in that.
 * Provides action for adding the custom fields and default fields on the from and the same in database.
 * Puts the current locale value in the meta table and also the other meta values in the meta tables.
 * Gets the static email Object.
 */
function quoteupSubmitWooEnquiryForm()
{
    // @session_start();

    $formSetting = quoteupSettings();
    $isNonceDisabled = isset($formSetting['deactivate_nonce']) && $formSetting['deactivate_nonce'] == 1 ? true : false;

    if ($isNonceDisabled || (isset($_POST[ 'security' ]) && wp_verify_nonce($_POST[ 'security' ], 'nonce_for_enquiry'))) {
        $form_data = quoteupSettings();
        quoteupVerifyCaptcha($form_data);
        
        global $wpdb, $quoteup;
        $name = wp_kses($_POST[ 'custname' ], array());
        $email = filter_var($_POST[ 'txtemail' ], FILTER_SANITIZE_EMAIL);
        $phone = quoteupPhoneNumber();
        $dateField = quoteupDateField();
        $subject = quoteupSubject();
        $authorEmail = '';

        $validMedia = validateAttachField($quoteup);

        $msg = wp_kses($_POST[ 'txtmsg' ], array());
        $msg = stripcslashes($msg);
        $product_table_and_details = getEmailAndDbDataOfProducts($form_data, $_POST);
        $product_details = setProductDetails($product_table_and_details);
        $authorEmail = setAuthorEmail($product_table_and_details);
        $gaProducts = setGaProducts($product_table_and_details);
        $address = getEnquiryIP();
        $type = 'Y-m-d H:i:s';
        $date = current_time($type);
        $totalPrice = quoteupReturnSumOfProductPrices($product_details);
        $tbl = getEnquiryDetailsTable();

        if ($wpdb->insert(
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
                'total' => $totalPrice,
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
                '%f',
            )
        )
        ) {
            $enquiryID = $wpdb->insert_id;
            processEnquiryAfterInsertion($enquiryID, $product_details, $quoteup, $validMedia, $_POST, $authorEmail, $subject);
        }
    }
    echo json_encode(
        array(
        'status'     => 'COMPLETED',
        'message'    => 'COMPLETED',
        'gaProducts' => $gaProducts,
        'enquiryID'  => $enquiryID,
        )
    );
    die();
}

function quoteupVerifyCaptcha($form_data)
{
    if (isset($form_data['enable_google_captcha']) && $form_data['enable_google_captcha'] == 1) {
        $secretKey          = $form_data[ 'google_secret_key' ];
        $response           = isset($_POST["captcha"])?$_POST["captcha"] : '';
        $verify             = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$response}");
        $captcha_success    = json_decode($verify);
        $errorMsg           = isset($form_data['google_captcha_err_msg']) && !empty($form_data['google_captcha_err_msg']) ? quoteupReturnWPMLVariableStrTranslation($form_data['google_captcha_err_msg']) : __('Captcha could not be verified.', QUOTEUP_TEXT_DOMAIN);
        $errorMsgHTML       = '<li class="error-list-item">'.$errorMsg.'</li>';
        $quoteupSettings    = quoteupSettings();
        if (!$captcha_success->success || (quoteupIsCaptchaVersion3($quoteupSettings) && !quoteupVerifyCaptchaV3($captcha_success))) {
            // This user was not verified by recaptcha.
            echo json_encode(
                array(
                'status'     => 'failed',
                'message'    => $errorMsgHTML
                )
            );
            die();
        }
    }
}

function processEnquiryAfterInsertion($enquiryID, $product_details, $quoteup, $validMedia, $data, $authorEmail, $subject)
{
    global $quoteup;

    updateProductDetails($enquiryID, $product_details, $data);
    do_action('mpe_form_entry_added_in_db', $enquiryID);
    do_action('quoteup_form_entry_added_in_db', $enquiryID);
    do_action('pep_form_entry_added_in_db', $enquiryID, $data, $product_details);
    do_action('quoteup_create_custom_field');
    do_action('pep_create_custom_field');
    do_action('quoteup_add_custom_field_in_db', $enquiryID, $data);
    do_action('pep_add_custom_field_in_db', $enquiryID);

    deleteDirectoryIfExists($enquiryID);
    uploadAttachedFile($quoteup, $validMedia, $enquiryID);

    //add Locale in enquiry meta
    addLanguageToEnquiryMeta($enquiryID, 'enquiry_lang_code', $data['wdmLocale']);
    addLanguageToEnquiryMeta($enquiryID, 'quotation_lang_code', $data['wdmLocale']);
    //End of locale insertion
    
    addEnquiryMeta($enquiryID, '_unread_enquiry', 'yes');

    // Customer entered subject functionality in custom form
    $subject     = apply_filters('product_enquiry_pro_email_subject', $subject, $data);
    $emailObject = Includes\Frontend\SendEnquiryMail::getInstance($enquiryID, $authorEmail, $subject, $data);

    do_action_deprecated('quoteup_send_auto_responder_enquiry_email', array($data), '6.5.0');

    /**
     * To send the enquiry email made by the customer.
     *
     * @param  Object  $emailObject  Instance of SendEnquiryMail class.
     */
    do_action('quoteup_send_enquiry_email', $emailObject);

    /**
     * Use hook after enquiry email is sent.
     *
     * @since 6.5.0
     *
     * @param  int  $enquiryID  Current enquiry Id.
     * @param  array  $product_details  Product details.
     * @param  array  $data  $_POST data
     * @param  array  $authorEmail  Each products' author emails.
     */
    do_action('quoteup_after_enquiry_email_sent', $enquiryID, $product_details, $data, $authorEmail);

    $quoteup->wcCartSession->set('wdm_product_info', '');
    $quoteup->wcCartSession->set('wdm_product_count', 0);
    $quoteup->wcCartSession->set('wdm_cart_language', null);
    $quoteup->wcCartSession->set('wdm_product_info', null);
}

function sendAutoSenderEmailSettings($data)
{
    $option_data      = get_option('wdm_form_data');
    $subject          = isset($option_data['autosender_email_subject']) ? $option_data['autosender_email_subject'] : '';
    $header           = isset($option_data['autosender_email_header']) ? $option_data['autosender_email_header'] : '';
    $body             = isset($option_data['autosender_email_body']) ? $option_data['autosender_email_body'] : '';
    $admin            = get_option('admin_email');
    $client_headers   = array();
    $client_headers[] = 'Content-Type: text/html; charset=UTF-8';
    $client_headers[] = 'MIME-Version: 1.0';
    $client_headers[] = "Reply-to: {$admin}";
    $admin_headers    = explode(" ", $header);
    array_merge($client_headers, $admin_headers);
    $recipient = isset($data['uemail']) ? $data['uemail'] : '';
    if ($recipient) {
        wp_mail($recipient, $subject, $body, $client_headers);
    }
}

/**
 * This function is used to validate files if attach field is activated.
 *
 * @param  global variable object $quoteup contains details of quoteup settings, expiration,session, attached file constraints,dependancies.
 * @return boolean $validMedia true if valid otherwise gives an error.
 */
function validateAttachField($quoteup)
{
    $validMedia = false;
    if (isset($_FILES) && !empty($_FILES)) {
        $validMedia = $quoteup->QuoteupFileUpload->validateFileUpload();
    }
    return $validMedia;
}

/**
 * This function is used to delete existing folder of files if exists
 *
 * @param int $enquiryID enquiry id of current enquiry
 */
function deleteDirectoryIfExists($enquiryID)
{
    $upload_dir = wp_upload_dir();
    $path = $upload_dir[ 'basedir' ].'/QuoteUp_Files/';
    $files = glob($path.$enquiryID.'/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file); // delete file
        }
    }
}

/**
 * This function is used to upload files if attach field is activated
 *
 * @param  object  $quoteup    Global object for classes
 * @param  boolean $validMedia true if media is valid
 * @param  int     $enquiryID  enquiry id of current enquiry
 * @return true if succesful file upload
 */
function uploadAttachedFile($quoteup, $validMedia, $enquiryID)
{
    $success = true;
    if (isset($_FILES) && !empty($_FILES) && $validMedia) {
        $success =  $quoteup->QuoteupFileUpload->quoteupUploadFiles($enquiryID);
        if (!$success) {
            echo json_encode(
                array(
                'status'     => 'failed',
                'message'    => __('Some issue with file upload', QUOTEUP_TEXT_DOMAIN),
                )
            );
            die();
        }
    }
}




/**
 * This function is used to set author mail.
 *
 * @param [type] $product_table_and_details [description]
 */
function setAuthorEmail($product_table_and_details)
{
    if (isset($product_table_and_details[ 'authorEmail' ])) {
        $authorEmail = $product_table_and_details[ 'authorEmail' ];
    } else {
        $authorEmail = '';
    }

    return $authorEmail;
}

/**
 * This function is used to set GA Products.
 *
 * @param [type] $product_table_and_details [description]
 */
function setGaProducts($product_table_and_details)
{
    if (isset($product_table_and_details[ 'gaProducts' ])) {
        $gaProducts = $product_table_and_details[ 'gaProducts' ];
    } else {
        $gaProducts = array();
    }

    return $gaProducts;
}



/**
 * set phone number
 * sets phone number if entered by customer or keeps it blank.
 *
 * @return [string] $phone [Phone number]
 */
function quoteupPhoneNumber()
{
    if (isset($_POST[ 'txtphone' ])) {
        $phone = filter_var($_POST[ 'txtphone' ], FILTER_SANITIZE_NUMBER_INT);
    } else {
        $phone = '';
    }

    return $phone;
}

/**
 * set Date Field Value
 * sets Date field value if entered by customer or keeps it blank.
 *
 * @return [date] $dateFeild [date field]
 */
function quoteupDateField()
{
    if (isset($_POST[ 'txtdate' ])) {
        $dateField = $_POST[ 'txtdate' ];
        if (!empty($dateField)) {
            $dateField = date('Y-m-d', strtotime($dateField));
        }
    } else {
        $dateField = null;
    }

    return $dateField;
}

/**
 * set Subject Value
 * sets Subject value if entered by customer or keeps it blank.
 */
function quoteupSubject()
{
    $subject = '';
    if (isset($_POST[ 'txtsubject' ])) {
        $subject = wp_kses($_POST[ 'txtsubject' ], array());
        $subject = stripcslashes($subject);
        $subject = (strlen($subject) > 50) ? substr($subject, 0, 47).'...' : $subject;
    }
    return $subject;
}

/**
 * This function is used to get img url of product.
 *
 * @param [string] $img        [image url]
 * @param [int]    $product_id Product Id
 *
 * @return [string]$img_url [image url]
 */
function getImgUrl($img, $product_id)
{
    if ($img != '') {
        $img_url = $img;
    } else {
        $img_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));
    }

    return $img_url;
}

/**
 * This function is used to get variation details of the product.
 *
 * @param [array] $variation_detail [Variation details]
 *
 * @return [array] $newVariation [variation details]
 */
function getVariationDetails($variation_detail)
{
    $variation_detail = explode(",", $variation_detail);
    foreach ($variation_detail as $individualVariation) {
        $keyValue = explode(':', $individualVariation);
        $newVariation[trim($keyValue[0])] = trim($keyValue[1]);
    }

    return $newVariation;
}

/**
 * Returns email content to be sent to customer and admin. It also returns content
 * to be saved in the database. Checks whether multi product enquiry mode is enabled
 * or not.
 * If yes then fetch the data of products in the cart.
 * If no fetch the product details of single product.
 *
 * @param [array] $form_data     [settings stored by admin]
 * @param [array]  $productData   [$_POST data]
 */
function getEmailAndDbDataOfProducts($form_data, $productData)
{
    global $quoteup;
    // @session_start();
    $product_details = '';
    $gaProducts = array();
    $authorEmail = array();
    //if multiproduct enquiry is set
    if (isset($form_data[ 'enable_disable_mpe' ]) && $form_data[ 'enable_disable_mpe' ] == 1) {
        $product_details = getEnquirySessionProductsDetails($gaProducts, $productData);
        $prod = $quoteup->wcCartSession->get('wdm_product_info');
        foreach ($prod as $arr) {
            array_push($authorEmail, $arr['author_email']);
        }
        /*foreach ($_SESSION[ 'wdm_product_info' ] as $arr) {
            array_push($authorEmail, $arr['author_email']);
        }*/
    } else {
        $product_id = $productData[ 'product_id' ];
        $prod_quant = filter_var($productData[ 'product_quant' ], FILTER_SANITIZE_NUMBER_INT);
        $title = get_the_title($product_id);
        $variation_id = filter_var($productData['variation_id'], FILTER_SANITIZE_NUMBER_INT);
        $variation_detail = '';
        $authorEmail = array();
        array_push($authorEmail, isset($productData[ 'uemail' ]) ? $productData[ 'uemail' ] : '');
        array_push($gaProducts, get_the_title($product_id));

        // Variable Product
        if ($variation_id != '') {
            $product = wc_get_product($variation_id);
            $sku = $product->get_sku();
            $variation_detail = $productData['variation_detail'];
            $price = $product->get_price();
            $img = wp_get_attachment_url(get_post_thumbnail_id($variation_id));
            $img_url = getImgUrl($img, $product_id);
            $variation_detail = getVariationDetails($variation_detail);
        } else {
            $product = wc_get_product($product_id);
            $price = $product->get_price();
            $sku = $product->get_sku();
            $img_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));
        }

        $customerEmail = filter_var($productData[ 'txtemail' ], FILTER_SANITIZE_EMAIL);
        $price = quoteupGetPriceToDisplay($product, $prod_quant, $price, $customerEmail);

        // End of Variable Product
        $prod[] = array('id' => $product_id,
            'title' => $title,
            'price' => $price,
            'quantity' => $prod_quant,
            'img' => $img_url,
            'remark' => '',
            'sku' => $sku,
            'variation_id' => $variation_id,
            'variation' => $variation_detail, );

        $product_details = $prod;
    }

    return array(
        'product_details' => $product_details,
        'authorEmail' => $authorEmail,
        'gaProducts' => $gaProducts,
    );
}

/**
 * This function get Enquiry Session Products Details
 * get the Product details of both the Variable and simple products whatever is in the cart.
 *
 * @return [array] $data [product details in the cart]
 */
function getEnquirySessionProductsDetails(&$gaProducts, $productData)
{
    global $quoteup;
    // @session_start();
    $data = array();
    $prod = $quoteup->wcCartSession->get('wdm_product_info');
    foreach ($prod as $key => $value) {
        //foreach ($_SESSION[ 'wdm_product_info' ] as $key => $value) {
        $productId   = intval($value['id']);
        $title       = sanitize_text_field($value['title']);
        $quantity    = intval($value['quantity']);
        $remark      = filter_var($value['remark'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $variationId = intval($value['variation_id']);
        $variation   = empty($value['variation']) ? '' : filter_var_array($value['variation'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $customerEmail = filter_var($productData[ 'txtemail' ], FILTER_SANITIZE_EMAIL);        

        if (!empty($variationId)) {
            $product = wc_get_product($variationId);
            $price   = $product->get_price();
        } else {
            $product = wc_get_product($productId);
            $price   = $product->get_price();
        }

        $price = quoteupGetPriceToDisplay($product, $quantity, $price, $customerEmail);

        array_push($gaProducts, get_the_title($productId));
        $value['id'] = $productId;
        $value['title'] = $title;
        $value['quantity'] = $quantity;
        $value['remark'] = $remark;
        $value['variation_id'] = $variationId;
        $value['variation'] = $variation;
        $value['price'] = $price;
        array_push($data, $value);
        unset($key);
    }
    return $data;
}

/**
 * Set product details from an array.
 *
 * @param  array $product_table_and_details product details of the enquiry.
 * @return array $product_details product details of the enquiry.
 */
function setProductDetails($product_table_and_details)
{
    if (isset($product_table_and_details[ 'product_details' ])) {
        $product_details = $product_table_and_details[ 'product_details' ];
    } else {
        $product_details = '';
    }

    return $product_details;
}

/**
 * Get IP of client.
 *
 * @return [string] $address [IP address]
 */
function getEnquiryIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //check ip from share internet
        $address = $_SERVER[ 'HTTP_CLIENT_IP' ];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //to check ip is pass from proxy
        $address = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        $address = explode(',', $address);
        $address = $address[0];
    } else {
        $address = $_SERVER[ 'REMOTE_ADDR' ];
    }

    return $address;
}

/*
 * Function to check input currency and return only sale price
 * @param  [string] $original_price Original string containing price.
 * @return [int]                    Sale price
 */
if (!function_exists('getSalePrice')) {
    function getSalePrice($original_price)
    {
        // Trim spaces
        $original_price = trim($original_price);
        // Extract Sale Price
        $price = extractSalePrice($original_price);
        $sanitized_price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (!$sanitized_price) {
            return $original_price;
        }

        return $sanitized_price;
    }
}

if (!function_exists('extractSalePrice')) {
    function extractSalePrice($price)
    {
        //Check if more than 1 value is present
        $prices = explode(' ', $price);
        if (count($prices) > 1) {
            return $prices[ 1 ];   // If yes return sale price.
        }

        return $prices[ 0 ]; //  Else return same string.
    }
}

/*
* No longer in use.
 * To set the global setting option of add_to_cart to individual product.
 * To set the global setting option of quoteup_enquiry to individual product.
 * To set the global setting option of show price to individual product.
 */
function quoteupSetAddToCartValue()
{
    if (!current_user_can('manage_options')) {
        die('SECURITY_ISSUE');
    }
    $add_to_cart_option = $_POST[ 'option_add_to_cart' ];
    $global_quoteup_enquiry_option = $_POST[ 'option_quoteup_enquiry' ];
    $quoteup_price = $_POST[ 'option_quoteup_price' ];
    if ($global_quoteup_enquiry_option == 'yes') {
        $individual_quoteup_enquiry_option = 'yes';
    }
    if ($global_quoteup_enquiry_option == 'no') {
        $individual_quoteup_enquiry_option = '';
    }

    global $post;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => '-1',
    );
    $wp_query = new WP_Query($args);

    if ($wp_query->have_posts()) :
        while ($wp_query->have_posts()) :
            $wp_query->the_post();

            $product = get_product($post->ID);
            update_post_meta($post->ID, '_enable_add_to_cart', $add_to_cart_option);
            update_post_meta($post->ID, '_enable_pep', $individual_quoteup_enquiry_option);
            update_post_meta($post->ID, '_enable_price', $quoteup_price);
        endwhile;
    endif;

    die();
    unset($product);
}

/*
 * Ajax callback to modify user name and email on enquiry/quote edit page on admin side.
 * Checks if user is admin or not, if yes
 * Update the changes made in the Enquiry details table.
 */
if (!function_exists('quoteupModifyUserQuoteData')) {
    function quoteupModifyUserQuoteData()
    {
        if (!current_user_can('manage_options')) {
            die('SECURITY_ISSUE');
        }

        global $wpdb;
        $enq_tbl = getEnquiryDetailsTable();
        $name = filter_var($_POST[ 'cname' ], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST[ 'email' ], FILTER_SANITIZE_EMAIL);
        $enquiry_id = filter_var($_POST[ 'enquiry_id' ], FILTER_SANITIZE_NUMBER_INT);
        $wpdb->update(
            $enq_tbl,
            array(
            'name' => $name, // string
            'email' => $email, // integer (number)
            ),
            array('enquiry_id' => $enquiry_id),
            array(
            '%s',
            '%s',
            ),
            array('%d')
        );
        echo 'Saved Successfully.';
        die;
    }
}

/**
 * Ajax callback to send reply from enquiry edit page
 * When admin/ vendor sends a reply on any quote from customer , on send button click this function is called through ajax.
 * In the enquiry Thread table, put the details of reply sent by admin/ vendor.
 * Send an email to the customer on whose enquiry reply is sent.
 *
 * @global $wpdb wordpress database global
 * @global object $quoteupEmail Email object for quoteup
 */
if (!function_exists('quoteupSendReply')) {
    function quoteupSendReply()
    {
        global $wpdb, $quoteupEmail;

        $wdm_reply_message = getEnquiryThreadTable();
        $uemail = filter_var($_POST[ 'email' ], FILTER_SANITIZE_EMAIL);
        $subject = wp_kses($_POST[ 'subject' ], array());
        $subject = stripcslashes($subject);
        $message = wp_kses($_POST[ 'msg' ], array());
        $message = stripcslashes($message);
        $enquiryID = filter_var($_POST[ 'eid' ], FILTER_SANITIZE_NUMBER_INT);
        $type = 'Y-m-d H:i:s';
        $date = current_time($type);
        $parent = filter_var($_POST[ 'parent_id' ], FILTER_SANITIZE_NUMBER_INT);
        $email_data = quoteupSettings();
        $admin_emails = array();

        /**
         * Use hook to validate the reply action.
         *
         * @param  int  $enquiryID  Enquiry Id.
         * @param  string  $uemail  Enquiry email.
         */
        do_action('quoteup_validate_enquiry_reply_action', $enquiryID, $uemail);

        $shouldIncRecipientEmails = apply_filters('quoteup_inc_recipient_emails_in_reply', true);
        if ($shouldIncRecipientEmails && $email_data[ 'user_email' ] != '') {
            $admin_emails = explode(',', $email_data[ 'user_email' ]);
        }

        $admin_emails = array_map('trim', $admin_emails);

        if (isset($email_data[ 'send_mail_to_admin' ]) && $email_data[ 'send_mail_to_admin' ] == 1) {
            $admin = get_option('admin_email');
            if (!in_array($admin, $admin_emails)) {
                $admin_emails[] = $admin;
            }
        }

        // Filter used to modify the 'Reply-to' emails when an admin/
        // vendor sends reply to customer
        $admin_emails = apply_filters('qupteup_reply_email_lists', $admin_emails);

        if (class_exists('Postman')) {
            $emails = implode(';', $admin_emails);
        } else {
            $emails = implode(',', $admin_emails);
        }

        $client_headers[] = 'Content-Type: text/html; charset=UTF-8';
        $client_headers[] = 'MIME-Version: 1.0';
        $client_headers[] = "Reply-to: {$emails}";

        $uemail = apply_filters('quoteup_send_reply_email', $uemail);
        $subject = apply_filters('quoteup_subject', $subject);
        $message = apply_filters('quoteup_msg', $message);
        $client_headers = apply_filters('quoteup_client_headers', $client_headers);

        $insertId = $wpdb->insert(
            $wdm_reply_message,
            array(
            'enquiry_id' => $enquiryID,
            'subject' => $subject,
            'message' => $message,
            'parent_thread' => $parent,
            'date' => $date,
            ),
            array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            )
        );
        
        $quoteupEmail->send($uemail, $subject, $message, $client_headers);

        /**
         * After reply is sent to the customer from the enquiry edit page.
         *
         * @param  int  $insertId  Reply Id.
         */
        do_action('quoteup_after_send_reply', $insertId);
        echo $enquiryID;

        die();
    }
}

/**
 * Ajax call to provide quote history data.
 */
function paginateQuote()
{
    $length = isset($_POST['length']) ? $_POST['length'] : 10;
    $start  = $_POST["start"];
    $viewText = __('View', QUOTEUP_TEXT_DOMAIN);
    $statusArray = array(
        'Quote Created' => __('Quote Created', QUOTEUP_TEXT_DOMAIN),
        'Requested' => __('Requested', QUOTEUP_TEXT_DOMAIN),
        'Saved' => __('Saved', QUOTEUP_TEXT_DOMAIN),
        'Sent' => __('Sent', QUOTEUP_TEXT_DOMAIN),
        'Approved' => __('Approved', QUOTEUP_TEXT_DOMAIN),
        'Rejected' => __('Rejected', QUOTEUP_TEXT_DOMAIN),
        'Order Placed' => __('Order Placed', QUOTEUP_TEXT_DOMAIN),
        'Expired' => __('Expired', QUOTEUP_TEXT_DOMAIN),
    );

    $search_str = implode(' ', $_POST["search"]);
    $new_str    = preg_replace('/ false$/', '', $search_str);
    $new_str    = trim($new_str);
    $order      = $_POST['order'];
    $getquote = new \Includes\Frontend\QuoteUpGetQuoteData();
    $totalrow = $getquote->getQuote($length, $start, $new_str, $order);
    $formData = get_option('wdm_form_data'); // PEP settings
    $isQuotationModActive = isset($formData['enable_disable_quote']) && $formData['enable_disable_quote'] == 1 ? false : true;

    $data = array();
    foreach ($totalrow as $row) {
        $quotedata=array();
        $quotedata['id']=$row->enquiry_id;
        $qdate=$row->date;
        $date=date_create($qdate);
        $quotedata['date']=date_format($date, "F d, Y");
        $quoteProdPriceQty = \Includes\Frontend\QuoteUpGetQuoteData::getQuoteProdPriceQty($quotedata['id'], $isQuotationModActive);
        $isPriceHiddenProduct = isset($quoteProdPriceQty['disable-price']) && true == $quoteProdPriceQty['disable-price'] ? true : false;

        // If enquiry contains hidden product and enquiry/ quote status is 'Requested' OR
        // contains hidden product and quotation module is disabled.
        if ($isPriceHiddenProduct && (!$isQuotationModActive || 'Requested' == $row->status)) {
            $quotetotal = '-';
        } else {
            $quotetotal = sprintf(__('%1$d for %2$d items', QUOTEUP_TEXT_DOMAIN), $quoteProdPriceQty['total-price'], $quoteProdPriceQty['total-quantity']);
        }

        // if quotation module is enabled
        if ($isQuotationModActive) {
            $quotedata['status']=$statusArray[$row->status];
            $quotedata['total']=$quotetotal;
            if ($row->status == 'Requested') {
                $quotedata['action']= '<center>-</center>';
            } else {
                $quotedata['action']="<button type='button' class='quotes button' id='$row->enquiry_id'  data-toggle='wdm-quotes-modal' data-target='#wdm-quotes-modal-$row->enquiry_id'>{$viewText}</button>";
            }
        } else {
            $quotedata['total'] = $quotetotal;
        }

        $data[]=$quotedata;
    }

    $recordfilter=$getquote->countQuote($new_str);

    $jsondata=json_encode(array("recordsFiltered" => $recordfilter, "data" => $data));

    echo $jsondata;
    die();
}

/**
 * Ajax call to return enquiry data to be displayed in the frontend modal.
 */
function popupModalEnquiry()
{
    $quoteid = $_GET['id'];
    $args = array(
        'enquiryid' => $quoteid
    );

    $html = quoteupGetPublicTemplatePart('quote-frontend/popup', '', $args);
    echo $html;
    die();
}
