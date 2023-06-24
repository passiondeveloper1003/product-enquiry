<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('admin_init', 'quoteupUpdateCheck', 10);

/**
 * Checks the update of PEP Plugin.
 * If Plugin is not of the latest version , the features of previous version are applied.
 */
function quoteupUpdateCheck()
{
    global $quoteup;

    // If dependencies are not fullfilled, return from the function.
    if (!$quoteup->getDependenciesFullfilledStatus()) {
        return;
    }

    $get_plugin_version = get_option('wdm_quoteup_version');
    if (false === $get_plugin_version || QUOTEUP_VERSION != $get_plugin_version) {
        quoteupCreateTables();
        quoteupConvertMpeSettings();
        quoteupTogglePerProductDisablePepSettings();
        quoteupConvertPerProductAddToCart();
        quoteupSetAddToCartPepPriceOnActivation();
        quoteupUpdateHistoryStatus();
        quoteupConvertOldCheckboxes();
        quoteupMigrateEnqBtnVisibilitySetting();
        quoteupMigratePriceRemarksColSettings();
        quoteupSetDefaultSettings();
        quoteupCreateCronJobs();
        quoteupMigrateProductDetails();
        quoteupSetPrivacyAnonymizeSettings();
        quoteupUpdateVersionInDb();
        /**
         * After setting up database.
         *
         * @since 6.5.0
         *
         * @param  bool|string  $get_plugin_version  Previous plugin version. false if plugin has been installed newly.
         */
        do_action('quoteup_updated_new_version', $get_plugin_version);
    }
}

/**
 * Changes _disable_quoteup for every product to _enable_quoteup.
 *
 *
 * @global type $wpdb
 */
function quoteupTogglePerProductDisablePepSettings()
{
    //Not a fresh install and version is less than 4.1.0
    if (isFreshInstalledQuoteup() || !isQuoteupLesserThanVersion('4.1.0')) {
        return;
    }

    //Check if Dropdowns in Settings are converted to Checkbox
    $convertPerProductPepDropdown = get_option('quoteup_convert_per_product_pep_dropdown', 0);

    if ($convertPerProductPepDropdown == 1) {
        return;
    }
    
    //Migrating Old PEP to New PEP
    migrateOldDetails('_disable_pep');

    //Migrating from QuoteUp to PEP
    migrateOldDetails('_disable_quoteup');

    update_option('quoteup_convert_per_product_pep_dropdown', 1);
}

function migrateOldDetails($metakey)
{
    global $wpdb;
    $getDisabledPEPForProducts = $wpdb->get_results($wpdb->prepare("SELECT meta_value, post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = %s", $metakey));

    foreach ($getDisabledPEPForProducts as $singleProduct) {
        $trimMetaValue = trim($singleProduct->meta_value);
        if (empty($trimMetaValue)) {
            continue;
        }

        if ($trimMetaValue == 'no') {
            update_post_meta($singleProduct->post_id, '_enable_pep', 'yes');
        } else {
            update_post_meta($singleProduct->post_id, '_enable_pep', '');
        }
        delete_post_meta($singleProduct->post_id, $metakey);
    }
}

/*
 * Check products which have _enable_add_to_cart set to 'no' and sets them to ''
 */

function quoteupConvertPerProductAddToCart()
{
    if (!isFreshInstalledQuoteup() && isQuoteupLesserThanVersion('4.1.0')) {
        global $wpdb;

        //Check if Dropdowns in Settings are converted to Checkbox
        $convertPerProductAddToCartDropdown = get_option('quoteup_convert_per_product_add_to_cart_dropdown', 0);

        if ($convertPerProductAddToCartDropdown == 1) {
            return;
        }

        //Migrating Old PEP to New PEP
        $products = $wpdb->get_results($wpdb->prepare("SELECT meta_value, post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND meta_value = %s", '_enable_add_to_cart', 'no'));

        foreach ($products as $singleProduct) {
            update_post_meta($singleProduct->post_id, '_enable_add_to_cart', '');
        }

        update_option('quoteup_convert_per_product_add_to_cart_dropdown', 1);
    }
}

/**
 * This function is used to enable or disable Add to cart button, Enable or disable PEP, show or hide Price on all products after activating the plugin again.
 * Add the mets key value to the products which were not having them previously.
 */
function quoteupSetAddToCartPepPriceOnActivation()
{
    //Enable Enquiry, Add to cart and Price for all products who are not having the meta set
    $defaultAddToCart = $defaultPrice = $defaultEnquiry = 'yes';

    $productsWithoutAddToCart = quoteupSearchProductsNotHavingMeta('_enable_add_to_cart');
    if (!empty($productsWithoutAddToCart) && is_array($productsWithoutAddToCart)) {
        foreach ($productsWithoutAddToCart as $singleProduct) {
            update_post_meta($singleProduct, '_enable_add_to_cart', $defaultAddToCart);
        }
    }

    $productsWithoutPrice = quoteupSearchProductsNotHavingMeta('_enable_price');
    if (!empty($productsWithoutPrice) && is_array($productsWithoutPrice)) {
        foreach ($productsWithoutPrice as $singleProduct) {
            update_post_meta($singleProduct, '_enable_price', $defaultPrice);
        }
    }

    $productsWithoutPep = quoteupSearchProductsNotHavingMeta('_enable_pep');
    if (!empty($productsWithoutPep) && is_array($productsWithoutPep)) {
        foreach ($productsWithoutPep as $singleProduct) {
            update_post_meta($singleProduct, '_enable_pep', $defaultEnquiry);
        }
    }
}

/**
 * Returns the list of products which does not have specific meta value set.
 *
 * @static array $allProductsFromDb posts with type product on posts table.
 *
 * @param string $meta_key meta-key to be searched.
 *
 * @return array $allProductsFromDb Products from Db excluding the products having meta key specified.
 */
function quoteupSearchProductsNotHavingMeta($meta_key = '')
{
    global $wpdb;
    static $allProductsFromDb = null;
    if (empty($meta_key)) {
        return;
    }

    if (null === $allProductsFromDb) {
        $allProductsFromDb = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'product'");
        if (!$allProductsFromDb) {
            $allProductsFromDb = false;
        }
    }

    if ($allProductsFromDb) {
        $commaSeparatedProducts = implode(',', $allProductsFromDb);
        $productsToBeOmitted = $wpdb->get_col("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE post_id IN ($commaSeparatedProducts) AND meta_key = '$meta_key'");
        if ($productsToBeOmitted) {
            return array_diff($allProductsFromDb, $productsToBeOmitted);
        } else {
            return $allProductsFromDb;
        }
    }
}

/**
 * This function updates history status and makes it past tense.
 */
function quoteupUpdateHistoryStatus()
{

    //Not a fresh install and version is less than 4.1.0
    if (isFreshInstalledQuoteup() || !isQuoteupLesserThanVersion('4.1.0')) {
        return;
    }

    global $wpdb;
    $convertHistoryStatus = get_option('quoteup_convert_history_status', 0);

    if ($convertHistoryStatus == 1) {
        return;
    }

    $table_name = getEnquiryHistoryTable();
    $sql = "SELECT enquiry_id, status,message FROM $table_name";
    $result = $wpdb->get_results($sql, ARRAY_A);
    foreach ($result as $res) {
        if ($res[ 'status' ] == 'Reject') {
            updateHistoryTable('Rejected', $res[ 'enquiry_id' ], 'Reject');
        }
        if ($res[ 'status' ] == 'Accept') {
            updateHistoryTable('Approved', $res[ 'enquiry_id' ], 'Accept');
        }
        if ($res[ 'status' ] == 'Completed') {
            updateHistoryTable('Order Placed', $res[ 'enquiry_id' ], 'Completed');
        }
        if ($res[ 'message' ] == 'Approved but payment pending') {
            updateHistoryTable('Approved but order not yet placed', $res[ 'enquiry_id' ], 'Approved but payment pending');
        }
    }

    addRequestedToOldEnquiries();

    update_option('quoteup_convert_history_status', 1);
}

/**
 * This function adds requested status to all enquiries which dont have any status
 */
function addRequestedToOldEnquiries()
{
    global $wpdb;
    $table_name = getEnquiryHistoryTable();
    $enquiryDetailTable = getEnquiryDetailsTable();

    $sql = "SELECT enquiry_id,enquiry_date FROM  $enquiryDetailTable WHERE  enquiry_id NOT IN (SELECT enquiry_id FROM $table_name)";
    $oldEnquiryIDs = $wpdb->get_results($sql, ARRAY_A);
    foreach ($oldEnquiryIDs as $enquiryID) {
        $enquiry = $enquiryID[ 'enquiry_id' ];
        $date = $enquiryID[ 'enquiry_date' ];
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

/**
 * This function is to update enquiry history table, with the current enquiry status.
 *
 * @param string $statusToUpdate current status to be updated
 * @param int    $enquiryId      Enquiry Id
 * @param string $whereStatus    previous status.
 */
function updateHistoryTable($statusToUpdate, $enquiryId, $whereStatus)
{
    global $wpdb;
    $table_name = getEnquiryHistoryTable();
    $wpdb->update(
        $table_name,
        array(
        'status' => $statusToUpdate, // string
        ),
        array('enquiry_id' => $enquiryId,
        'status' => $whereStatus,
        )
    );
}

/**
 * This function converts old checkboxes that is 1/unavailable to 1/0.
 * And add the new checkboxes values to 0.
 */
function quoteupConvertOldCheckboxes()
{
    //Run only if not fresh installed and upgrading from version below 4.1.0
    if (!isFreshInstalledQuoteup() && isQuoteupLesserThanVersion('4.1.0')) {
        $oldSettings = get_option('wdm_form_data');
        //Check if Dropdowns in Settings are converted to Checkbox
        $settingsConversion = get_option('quoteup_settings_convert_old_checkboxes', 0);

        if ($settingsConversion == 1) {
            return;
        }

        $settingsAvailable = array(
            'enable_disable_quote' => '0',
            'only_if_out_of_stock' => '0',
            'show_enquiry_on_shop' => '0',
            'show_button_as_link' => '0',
            'enable_send_mail_copy' => '0',
            'enable_telephone_no_txtbox' => '0',
            'make_phone_mandatory' => '0',
        );

        $settingsToBeSet = array_diff_assoc($settingsAvailable, $oldSettings);

        if (empty($settingsToBeSet)) {
            update_option('quoteup_settings_convert_old_checkboxes', 1);

            return;
        }

        $newSettings = $oldSettings + $settingsToBeSet;

        update_option('wdm_form_data', $newSettings);

        update_option('quoteup_settings_convert_old_checkboxes', 1);
    }
}

/**
 * THis function is used to toggle settings of previous pep.
 * Checks if it is fresh installed and upgrading from version below 4.1.0
 * If no then Dropdowns converted to checkbox.
 * Fetch the old settings and updated mpe option accordingly.
 */
function quoteupConvertMpeSettings()
{

    //Run only if not fresh installed and upgrading from version below 4.1.0
    if (!isFreshInstalledQuoteup() && isQuoteupLesserThanVersion('4.1.0')) {
        $oldSettings = get_option('wdm_form_data');
        //Check if Dropdowns in Settings are converted to Checkbox
        $settingsConversion = get_option('quoteup_settings_convert_to_checkbox', 0);

        if ($settingsConversion == 1) {
            return;
        }

        if (isset($oldSettings[ 'enable_disable_mpe' ]) && ($oldSettings[ 'enable_disable_mpe' ] == 'yes' || $oldSettings[ 'enable_disable_mpe' ] == 1)) {
            $oldSettings[ 'enable_disable_mpe' ] = 1;
        } else {
            $oldSettings[ 'enable_disable_mpe' ] = 0;
        }
        update_option('wdm_form_data', $oldSettings);

        //All Settings is converted to checkbox
        update_option('quoteup_settings_convert_to_checkbox', 1);
    }
}

/**
 * THis function creates the tables required by QuoteUp.
 *
 * First it checks if the free plugin product-enquiry-for-woocommerce is active or not , if
 * yes deactivate it.
 * Creates all the necessary tables required.
 * enquiry_detail_new,enquiry thread table,
 */
function quoteupCreateTables()
{
    $pe_plugin = 'product-enquiry-for-woocommerce/product-enquiry-for-woocommerce.php';
    if (is_plugin_active($pe_plugin)) {
        add_action('update_option_active_plugins', 'deactivateDependentProductEnquiry');
    }
    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    global $wpdb;
    $charset_collate = getCharsetCollate();
    $wdm_enq_table = getEnquiryDetailsTable();
    $wdm_reply_message = getEnquiryThreadTable();
    $check_if_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$wdm_enq_table'"); //Adding this statement so that dbDelta function can understand that structure of this table need to be scanned and redesigned. Otherwise it would throw the error that Table already exists
    $add_extra_column = false;
    $custom_column_in_db = apply_filters('pep_custom_column_in_quoteup_db', $add_extra_column);
    $custom_column_in_db = apply_filters('quoteup_custom_column_in_quoteup_db', $custom_column_in_db);
    $quoteup_query_elements = tableStructure($custom_column_in_db);
    $enq_sql = 'CREATE TABLE '.$wdm_enq_table." (
                    $quoteup_query_elements
        ) $charset_collate;";
    $enq_sql = apply_filters('pep_create_table_query', $enq_sql);
    $enq_sql = apply_filters('quoteup_create_table_query', $enq_sql);
    if (!$enq_sql) {
        return;
    }

    /**
     * Use hook before PEP creates the tables.
     */
    do_action('quoteup_before_creating_table_in_db');
    do_action_deprecated('pep_before_creating_table_in_db', array(), '6.5.0');

    dbDelta($enq_sql);

    /**
     * Use hook after PEP creates the tables.
     */
    do_action('quoteup_after_creating_table_in_db');
    do_action_deprecated('pep_after_creating_table_in_db', array(), '6.5.0');

    /**
     * Use hook before PEP creates the enquiry meta table.
     */
    do_action('quoteup_create_enquiry_meta_db');
    createEnquiryMetaTable();
    do_action_deprecated('pep_create_enquiry_meta_db', array(), '6.5.0');
    $trans_var = get_transient('wdm_quoteup_license_trans');
    deleteTrans($trans_var);

    set_transient('wdm_quoteup_license_trans', 'inactive', 0);
    $check_if_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$wdm_reply_message'");
    createEnquiryThread($check_if_table_exists, $wdm_reply_message, $charset_collate);

    global $wpdb;
    $table_name = getQuotationProductsTable();
    $enquiryTableName = getEnquiryDetailsTable();

    $sql = "CREATE TABLE $table_name (
      ID bigint(20) NOT NULL AUTO_INCREMENT,
      enquiry_id bigint(20) NOT NULL,
      product_id bigint(20) NOT NULL,
      product_title longtext,
      newprice float NOT NULL,
      quantity bigint(20) NOT NULL,
      oldprice float NOT NULL,
      variation_id bigint(20),
      variation longtext,
      show_price VARCHAR(5),
      show_price_diff VARCHAR(5) DEFAULT 'no',
      variation_index_in_enquiry bigint(20),
      PRIMARY KEY  (ID)
      ) $charset_collate;";

    dbDelta($sql);

    /* Create Enquiry History table */
    $history_table_name = getEnquiryHistoryTable();
    $sql = "CREATE TABLE $history_table_name (
      ID bigint(20) NOT NULL AUTO_INCREMENT,
      enquiry_id bigint(12) NOT NULL,
      date datetime NOT NULL,
      message longtext NOT NULL,
      status text NOT NULL,
      performed_by bigint(20) NULL,
      PRIMARY KEY  (ID), 
      KEY enquiry_id (enquiry_id)
      ) $charset_collate;";

    dbDelta($sql);

    /* Create Versions Table */
    $versionTableName = getVersionTable();

    $sql = "CREATE TABLE $versionTableName (
      ID bigint(20) NOT NULL AUTO_INCREMENT,
      enquiry_id bigint(20) NOT NULL,
      version bigint(20) NOT NULL,
      product_id bigint(20) NOT NULL,
      newprice float NOT NULL,
      quantity bigint(20) NOT NULL,
      oldprice float NOT NULL,
      variation_id bigint(20),
      variation longtext,
      show_price VARCHAR(5),
      show_price_diff VARCHAR(5) DEFAULT 'no',
      variation_index_in_enquiry bigint(20),
      version_date datetime NOT NULL,
      performed_by bigint(20) NULL,
      PRIMARY KEY  (ID)
      ) $charset_collate;";

    dbDelta($sql);

    /* Create enquiry products Table */
    $productsTableName = getEnquiryProductsTable();

    $sql = "CREATE TABLE $productsTableName (
      ID bigint(20) NOT NULL AUTO_INCREMENT,
      enquiry_id bigint(20) NOT NULL,
      product_id bigint(20) NOT NULL,
      product_title longtext,
      price float NOT NULL,
      quantity bigint(20) NOT NULL,
      remark longtext,
      variation_id bigint(20),
      variation longtext,
      product_hash VARCHAR(100),
      PRIMARY KEY  (ID)
      ) $charset_collate;";

    dbDelta($sql);

    //Alter Table to add a new column to store Enquiry Hash
    addEnquiryHash($enquiryTableName);

    //Alter Table to add a new column to store Enquiry Status
    addOrderID($enquiryTableName);

    //Alter Table to add a new column to store Total amount of the Quote
    addTotal($enquiryTableName);

    // Alter 'total' column data type to 'float' from 'int'.
    quoteupChangeTotalColDataType($enquiryTableName);

    //Alter quotation Table to add a new column to store Product title
    addProductTitle($table_name);

    //Alter table to add oldProducts Details
    addOldProductDetails($enquiryTableName);

    // Alter table to add 'show_diff_price' column in 'enquiry_quotation' table.
    quoteupAddShowPriceDiffColumn($table_name);

    // Alter table to add 'show_diff_price' column in 'enquiry_quotation_version' table.
    quoteupAddShowPriceDiffColumn($versionTableName);

    $upload_dir = wp_upload_dir();
    if (!file_exists($upload_dir[ 'basedir' ].'/QuoteUp_PDF')) {
        $success = wp_mkdir_p($upload_dir[ 'basedir' ].'/QuoteUp_PDF');
        if (!$success) {
            exit('Could not create directory to store PDF files');
        }
    }
    if (!file_exists($upload_dir['basedir'].'/QuoteUp_PDF/index.php')) {
        $file = fopen($upload_dir['basedir'].'/QuoteUp_PDF/index.php', 'w');
        $text = "Sorry you don't have access";
        fwrite($file, $text);
    }
    if (!file_exists($upload_dir[ 'basedir' ].'/QuoteUp_Files')) {
        $success = wp_mkdir_p($upload_dir[ 'basedir' ].'/QuoteUp_Files');
        if (!$success) {
            exit('Could not create directory to store files');
        }
    }
    if (!file_exists($upload_dir['basedir'].'/QuoteUp_Files/index.php')) {
        $file = fopen($upload_dir['basedir'].'/QuoteUp_Files/index.php', 'w');
        $text = "Sorry you don't have access";
        fwrite($file, $text);
    }
}

/**
 * This function is used to get the default character set or the Collate of the MySql.
 *
 * @return string $charset_collate charset for database
 */
function getCharsetCollate()
{
    global $wpdb;
    $charset_collate = '';
    if (!empty($wpdb->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    }
    if (!empty($wpdb->collate)) {
        $charset_collate .= " COLLATE $wpdb->collate";
    }

    return $charset_collate;
}

/**
 * This function is used to delete transient.
 *
 * @param string $trans_var transient variable in options table
 */
function deleteTrans($trans_var)
{
    if (isset($trans_var)) {
        delete_transient('wdm_quoteup_license_trans');
    }
}

/**
 * This function is used to create enquiry enquiry thread table.
 *
 * @param [int]    $check_if_table_exists [1 or 0]
 * @param [string] $wdm_reply_message     [enquiry thread table name]
 * @param [string] $charset_collate       [charset collate for database]
 */
function createEnquiryThread($check_if_table_exists, $wdm_reply_message, $charset_collate)
{
    if ($check_if_table_exists == null) {
        $message_tbl = "CREATE TABLE $wdm_reply_message
                        (id INT NOT NULL AUTO_INCREMENT,
                         enquiry_id int,
                         subject varchar(200),
                         message varchar(1000),
                         parent_thread int,
                         date datetime,
                          primary key  (id)) $charset_collate;";

        dbDelta($message_tbl);
    }
}

/**
 * This function is used to create enquiry meta table.
 *
 * @global $wpdb
 */
function createEnquiryMetaTable()
{
    global $wpdb;
    $table_name = getEnquiryMetaTable();
    $max_index_length = 191;
    $charset_collate = '';

    if (!empty($wpdb->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    }

    if (!empty($wpdb->collate)) {
        $charset_collate .= " COLLATE $wpdb->collate";
    }

    $sql = 'CREATE TABLE IF NOT EXISTS '.$table_name.' (
        meta_id INT NOT NULL AUTO_INCREMENT,
        enquiry_id int,
        meta_key varchar(500),
        meta_value varchar(500),
        PRIMARY KEY  (meta_id),
                KEY enquiry_id (enquiry_id),
                KEY meta_key (meta_key('.$max_index_length.'))
                )'.$charset_collate.';';
    dbDelta($sql);
}

/**
 * This function adds enquiry hash column in enquiry details table.
 *
 * @global $wpdb
 *
 * @param string $enquiryTableName enquiry table name
 */
function addEnquiryHash($enquiryTableName)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$enquiryTableName` LIKE 'enquiry_hash';")) {
        $wpdb->query("ALTER TABLE $enquiryTableName ADD enquiry_hash VARCHAR(75)");
        $wpdb->query("ALTER TABLE $enquiryTableName ADD UNIQUE INDEX `enquiry_hash` (`enquiry_hash`)");
    }
}

/**
 * This function adds total column in enquiry details table.
 *
 * @global $wpdb
 *
 * @param string $enquiryTableName enquiry table name
 */
function addTotal($enquiryTableName)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$enquiryTableName` LIKE 'total';")) {
        $wpdb->query("ALTER TABLE `$enquiryTableName` ADD `total` FLOAT");
    }

    //Update total of all Quotes Previously created
    $totalStatus = get_option('quoteup_total_updated');
    if (!$totalStatus) {
        updateTotal();
    }
}

/**
 * This function adds Product Title column in enquiry_quotation table.
 *
 * @global $wpdb
 *
 * @param string $quotationTableName quotation products table name
 */
function addProductTitle($quotationTableName)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$quotationTableName` LIKE 'product_title';")) {
        $wpdb->query("ALTER TABLE `$quotationTableName` ADD `product_title` longtext");
    }
}

/**
 * This function adds a column old_product_details in enquiry_quotation table.
 *
 * @global $wpdb
 *
 * @param string $enquiryTableName enquiry table name
 */
function addOldProductDetails($enquiryTableName)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$enquiryTableName` LIKE 'old_product_details';")) {
        $wpdb->query("ALTER TABLE `$enquiryTableName` ADD `old_product_details` longtext");
    }
}

/**
 * Add the 'show_price_diff' column in table.
 *
 * @since 6.5.0
 *
 * @return void
 */
function quoteupAddShowPriceDiffColumn($tableName)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$tableName` LIKE 'show_price_diff';")) {
        $wpdb->query("ALTER TABLE `$tableName` ADD `show_price_diff` VARCHAR(5) DEFAULT 'no'");
    }
}

/**
 * This function updates total in total column of previously created quotations.
 *
 * @global $wpdb
 */
function updateTotal()
{
    global $wpdb;
    $enquiryTableName = getEnquiryDetailsTable();
    $quotationTableName = getQuotationProductsTable();

    $sql = 'SELECT DISTINCT enquiry_id FROM '.$quotationTableName;
    $quotationIDs = $wpdb->get_col($sql);

    foreach ($quotationIDs as $quoteEnquiries) {
        $totalAmount = 0;
        $Quantity = array();
        $newprice = array();

        $sql = 'SELECT * FROM '.$quotationTableName.' WHERE enquiry_id ='.$quoteEnquiries;
        $result = $wpdb->get_results($sql, ARRAY_A);
        foreach ($result as $key) {
            $Quantity[] = $key[ 'quantity' ];
            $newprice[] = $key[ 'newprice' ];
        }

        $size = sizeof($newprice);
        for ($i = 0; $i < $size; ++$i) {
            if (isset($newprice) && isset($Quantity)) {
                $totalAmount = $totalAmount + ($newprice[ $i ] * $Quantity[ $i ]);
            }
        }

        $wpdb->update(
            $enquiryTableName,
            array(
            'total' => $totalAmount,
            ),
            array(
            'enquiry_id' => $quoteEnquiries,
            )
        );
    }
    update_option('quoteup_total_updated', 1);
}
/**
 * This function adds orderID column in enquiry details table.
 *
 * @global $wpdb
 *
 * @param string $enquiryTableName enquiry table name
 */
function addOrderID($enquiryTableName)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$enquiryTableName` LIKE 'order_id';")) {
        $wpdb->query("ALTER TABLE $enquiryTableName ADD order_id VARCHAR(20)");
    }
}
/**
 * This function adds show price column in enquiry details table.
 *
 * @global $wpdb
 *
 * @param string $quotationTableName quotation products table name
 */
function addShowPrice($table_name)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM `$table_name` LIKE 'show_price';")) {
        $wpdb->query("ALTER TABLE $table_name ADD show_price VARCHAR(5)");
    }
}
/**
 * This function returns table structure of enquiry details new
 * depending on the user whether to add custom column or not.
 *
 * @global $wpdb
 *
 * @param array $custom_column_in_db custom columns for fields
 *
 * @return array $quoteup_query_elements columns in quoteup enquiry details
 */
function tableStructure($custom_column_in_db)
{
    if (!$custom_column_in_db) {
        $quoteup_query_elements = '
                enquiry_id INT NOT NULL AUTO_INCREMENT,
        name varchar(100),
        email varchar(75),
        message varchar(500),
        phone_number varchar(16),
        subject varchar(50),
        enquiry_ip varchar(50),     
        product_details longtext,
        enquiry_date datetime,
        expiration_date datetime,
        pdf_deleted bigint(20),
        date_field datetime,
                PRIMARY KEY  (enquiry_id)
                ';
    } else {
        $quoteup_query_elements = "
                enquiry_id INT NOT NULL AUTO_INCREMENT,
        name varchar(100),
        email varchar(75),
        message varchar(500),
        phone_number varchar(16),
        subject varchar(50),
        enquiry_ip varchar(35),     
        product_details longtext,
        enquiry_date datetime,
        expiration_date datetime,
        pdf_deleted bigint(20),
        date_field datetime,
                PRIMARY KEY  (enquiry_id),
                {$custom_column_in_db}
                ";
    }

    return $quoteup_query_elements;
}

/**
 * This function adds a custom column in the table.
 *
 * @global $wpdb
 *
 * @param string $tableName     enquiry table name
 * @param string $colName       column name
 * @param string $colDataType   column data type
 * @param string $colSize       column size
 */
function quoteupAddColumnInTable($tableName, $colName, $colDataType, $colSize = null)
{
    global $wpdb;
    if (!$wpdb->get_var("SHOW COLUMNS FROM {$tableName} LIKE '{$colName}';")) {
        $col_structure = $colName.' '.$colDataType;

        if (!empty($colSize)) {
            $col_structure .= "({$colSize})";
        }

        $col_structure = apply_filters('quoteup_col_structure_to_add', $col_structure);
        /**
         * Use hook before adding columns to the table.
         */
        do_action('quoteup_before_adding_col', $tableName, $colName, $colDataType, $colSize);
        $wpdb->query("ALTER TABLE {$tableName} ADD {$col_structure}");
        /**
         * Use hook before adding columns to the table.
         */
        do_action('quoteup_after_adding_col', $tableName, $colName, $colDataType, $colSize);
    }
}

/**
 * Deactivate product enquiry free as well as pro version if activated.
 */
function deactivateDependentProductEnquiry()
{
    $pe_plugin = 'product-enquiry-for-woocommerce/product-enquiry-for-woocommerce.php';

    deactivate_plugins($pe_plugin);
}

/**
 * Returns true if it plugin is freshly installed and not yet configured i.e. if plugin
 * is activated for first time. NOT TO BE USED DIRECTLY.
 *
 * This function is used only during performing installation procedures. After installation
 * is complete, wdm_form_data will always have some value and hence this function will
 * return false.
 *
 * @static var array $settings
 *
 * @return bool true if fresh installed
 */
function isFreshInstalledQuoteup()
{
    static $settings;

    if (null !== $settings) {
        if (!$settings) {
            return true;
        }

        return false;
    }

    $settings = quoteupSettings();

    if (!$settings) {
        return true;
    }

    return false;
}

/**
 * This function updates the current version in database.
 */
function quoteupUpdateVersionInDb()
{
    $oldVersion = get_option('wdm_quoteup_version');
    $newVersion = QUOTEUP_VERSION;

    /**
     * Use action to execute custom manipulation before the plugin version is updated
     * in database.
     *
     * @since 6.5.0
     *
     * @param  string|bool  $oldVersion  Old version of the plugin.
     *                      If it fresh installtion of the plugin, this
     *                      parameter will contain boolean value false.
     * @param  string       $newVersion  New version of the plugin.
     */
    do_action('quoteup_before_version_updated_in_db', $oldVersion, $newVersion);

    update_option('wdm_quoteup_version', $newVersion);

    /**
     * Use action to execute custom manipulation after the plugin version is updated
     * in database.
     *
     * @since 6.5.0
     *
     * @param  string|bool  $oldVersion  Old version of the plugin.
     *                      If it fresh installtion of the plugin, this
     *                      parameter will contain boolean value false.
     * @param  string       $newVersion  New version of the plugin.
     */
    do_action('quoteup_after_version_updated_in_db', $oldVersion, $newVersion);
}

/**
 * Checks whether QuoteUp version is greater than or equal to specified version.
 *
 * NOT TO BE USED DIRECTLY
 *
 * This is used during installation procedure before updating new version number
 * in the database. That means this function returns true when last installed plugin's
 * version number is greater than specified $versionNumber variable
 *
 * @param string $versionNumber quoteup version
 *
 * @return bool true if current is greater than version number
 */
function isQuoteupGreaterThanVersion($versionNumber)
{
    $installedVersionNumber = get_option('wdm_quoteup_version');

    if (!$installedVersionNumber) {
        return false;
    }

    if (quoteupVersionCompare($installedVersionNumber, $versionNumber, '>=')) {
        return true;
    }

    return false;
}

/**
 * Checks whether QuoteUp version is lesser than specified version.
 *
 * NOT TO BE USED DIRECTLY
 *
 * This is used during installation procedure before updating new version number
 * in the database. That means this function returns true when last installed plugin's
 * version number is lesser than specified $versionNumber variable
 *
 * @param string $versionNumber quoteup version
 *
 * @return bool true if current is less than version number
 */
function isQuoteupLesserThanVersion($versionNumber)
{
    $installedVersionNumber = get_option('wdm_quoteup_version');

    if (!$installedVersionNumber) {
        return true;
    }

    if (quoteupVersionCompare($installedVersionNumber, $versionNumber, '<')) {
        return true;
    }

    return false;
}

/**
 * Compares version number which are php standardized as well non-php standardized.
 *
 * @param string $ver1     version 1
 * @param string $ver2     version 2
 * @param string $operator arithmetic operator
 *
 * @return bool
 */
function quoteupVersionCompare($ver1, $ver2, $operator = null)
{
    $pattern = '#(\.0+)+($|-)#';
    $ver1 = preg_replace($pattern, '', $ver1);
    $ver2 = preg_replace($pattern, '', $ver2);

    return isset($operator) ?
    version_compare($ver1, $ver2, $operator) :
    version_compare($ver1, $ver2);
}

/**
 * This function sets the default settings on installation.
 */
function quoteupSetDefaultSettings()
{
    $privacyPolicyText = __('I allow the Site owner to contact me via email/phone to discuss this enquiry. (If you want to know more about the way this site handles the data, then please go through our [privacy_policy])', QUOTEUP_TEXT_DOMAIN);
    $cookieConsentText = __('Save my Name and Email in this browser for my next Enquiry/Quote Request', QUOTEUP_TEXT_DOMAIN);
    $quotePdfEmailFooterDefaultText = quoteupQuotePDFEmailDefaultFooterText();

    $defaultSettings = array(
        'enable_disable_quote' => '0',
        'only_if_out_of_stock' => '0',
        'show_enquiry_on_shop' => '1',
        'show_enq_btn_to_users' => 'all', // @since 6.5.0
        'enable_disable_mpe' => '0',
        'custom_label' => 'Request a Quote',
        'cart_custom_label' => 'View Enquiry Cart',
        'ret_to_shop_btn_label' => quoteupReturnToShopBtnDefaultLabel(), // since 6.5.0
        'ret_to_shop_btn_url' => quoteupReturnToShopBtnDefaultURL(), // since 6.5.0
        'enable_price_col' => '1', // @since 6.5.0
        'enable_remarks_col' => '1', // @since 6.5.0
        'expected_price_remarks_label' => '', // @since 6.3.4
        'expected_price_remarks_col_phdr' => '', // @since 6.3.4
        'pos_radio' => 'after_add_cart',
        'show_button_as_link' => '0',
        'variation_id_selector' => '', // @since 6.3.4
        'hide_price_on_all_products' => '0', // @since 6.5.0
        'hide_add_to_cart_on_all_products' => '0', // @since 6.5.0
        'enable_send_mail_copy' => '1',
        'enable_telephone_no_txtbox' => '1',
        'make_phone_mandatory' => '0',
        'user_email' => get_option('admin_email'),
        'default_sub' => 'Enquiry/Quote Request for a product from '.get_bloginfo('name'),
        'disable_bootstrap' => '0', // @since 6.3.4
        'button_CSS' => 'theme_css',
        'company_name' => get_bloginfo('name'),
        'company_email' => get_option('admin_email'),
        'quote_pdf_email_footer_text' => $quotePdfEmailFooterDefaultText, // @since 6.5.0
        'send_mail_to_admin' => '1',
        'send_mail_to_author' => '1',
        'enable_disable_quote_pdf' => '1',
        'enquiry_form' => 'default',
        'enable_terms_conditions' => '1',
        'enquiry_privacy_policy_text' => $privacyPolicyText,
        'enable_cookie_consent' => '1',
        'cookie_consent_text' => $cookieConsentText,
        'enable_vendor_compatibilty' => '0',
    );

    if (isFreshInstalledQuoteup()) {
        update_option('wdm_form_data', $defaultSettings);
    } else {
        $currentSettings = get_option('wdm_form_data');

        //Adding this condition to solve the migration issue from any version to latest version
        if (!isset($currentSettings['show_enquiry_on_shop'])) {
            $currentSettings['show_enquiry_on_shop'] = 0;
        }

        //If upgraded from older version, check which all settings are not there in the settings
        // and add those settings
        $settingsToBeSet = array_diff_assoc($defaultSettings, $currentSettings);

        if (empty($settingsToBeSet)) {
            return;
        }

        $newSettings = $currentSettings + $settingsToBeSet;

        update_option('wdm_form_data', $newSettings);
    }
}

/**
 * This function creates a cron jobs.
 * Cron to delete PDF files every hour.
 * Cron to expire quote.
 */
function quoteupCreateCronJobs()
{
    wp_clear_scheduled_hook('quoteupDeletePdfs');
    wp_clear_scheduled_hook('quoteupExpireQuotes');
    // Make sure this event hasn't been scheduled
    if (!wp_next_scheduled('quoteupDeletePdfs')) {
        // Schedule the event
        wp_schedule_event(time(), 'hourly', 'quoteupDeletePdfs');
    }

    $timestamp = strtotime('tomorrow') - (get_option('gmt_offset') * 3600);
    // Make sure this event hasn't been scheduled
    if (!wp_next_scheduled('quoteupExpireQuotes')) {
        // Schedule the event
        wp_schedule_event($timestamp, 'daily', 'quoteupExpireQuotes');
    }
}

/**
 * This function migrates data from enquiry_details_new table to enquiry_products table
 * It migrates products details and converts serialized data from main table to seperate plain data in new table for better use.
 */
function quoteupMigrateProductDetails()
{
    if (isFreshInstalledQuoteup() || !isQuoteupLesserThanVersion('5.0.0')) {
        return;
    }
    global $wpdb;

    $enquiryDetailsTable = getEnquiryDetailsTable();
    $enquiryProductsTable = getEnquiryProductsTable();
    $quotationProductsTable = getQuotationProductsTable();

    //Take enquiry-id for admin quotes from database.
    $sql = "SELECT DISTINCT enquiry_id FROM $quotationProductsTable";
    $quotationIds = $wpdb->get_col($sql);

    $batch = 1;
    $recordsToFetch = 50;
    do {
        $offSet = ($batch - 1) *  $recordsToFetch;
        $sql = "SELECT enquiry_id, product_details, old_product_details FROM $enquiryDetailsTable LIMIT $recordsToFetch OFFSET $offSet";
        $enquiryData = $wpdb->get_results($sql, ARRAY_A);

        if (count($enquiryData) < 1) {
            break;
        }

        foreach ($enquiryData as $singleEnquiryData) {
            $updateQuotationTable = false;

            $productDetails = getProducts($singleEnquiryData);

            $enquiryID = $singleEnquiryData['enquiry_id'];
            if (in_array($enquiryID, $quotationIds)) {
                $updateQuotationTable = true;
            }
            foreach ($productDetails as $singleProduct) {
                $variationData = array(); // this will be variation for generating hash
                $productID = $singleProduct[0]['id'];
                $title = $singleProduct[0]['title'];
                $price = $singleProduct[0]['price'];
                $quantity = $singleProduct[0]['quant'];
                $remark = $singleProduct[0]['remark'];
                $variation_id = getVariationId($singleProduct);
                $variation = getVariation($singleProduct);

                // function to sanitize variations with prefix attribute_ for generating hash
                $variationData = sanitizeVariationLabel($variation_id, $variation);
                $product_hash = GenerateProductHash($productID, $variation_id, $variationData);

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

                updateTitleInQuotationTable($updateQuotationTable, $quotationProductsTable, $title, $enquiryID, $productID);
            }
        }

        // Increase batch so that we can get next enquiries to process
        ++$batch;
    } while (count($enquiryData) == 50);
}

/**
 * This function returns variation id of product in enquiry
 * @param  array $singleProduct single product details
 * @return int                variation id
 */
function getVariationId($singleProduct)
{
    return isset($singleProduct[0]['variation_id']) ? $singleProduct[0]['variation_id'] : '';
}

/**
 * This function returns variation details of product in enquiry
 * @param  array $singleProduct single product details
 * @return array                variation details
 */
function getVariation($singleProduct)
{
    return isset($singleProduct[0]['variation']) ? $singleProduct[0]['variation'] : '';
}

/**
 * This function returns the product Details from the database into a php variable.
 *
 * @param array $singleEnquiryData single enquiry data
 *
 * @return array $productDetails Product details for enquiry
 */
function getProducts($singleEnquiryData)
{
    if ($singleEnquiryData['old_product_details']) {
        $productDetails = unserialize($singleEnquiryData['old_product_details']);
    } else {
        $productDetails = unserialize($singleEnquiryData['product_details']);
    }

    return $productDetails;
}
/**
 * This function is used update Titles in the Quotation Table if $updateQuotationTable is set.
 *
 * @param bool   $updateQuotationTable   true if Quotation table needs to be updated
 * @param string $quotationProductsTable enquiry_quotation table name
 * @param string $title                  Product title
 * @param int    $enquiryID              enquiry Id to be updated
 * @param int    $productID              Product-Id to be updated
 *
 * @global $wpdb
 */
function updateTitleInQuotationTable($updateQuotationTable, $quotationProductsTable, $title, $enquiryID, $productID)
{
    global $wpdb;
    if ($updateQuotationTable) {
        $wpdb->update(
            $quotationProductsTable,
            array(
                'product_title' => $title,
                ),
            array(
                'enquiry_id' => $enquiryID,
                'product_id' => $productID,
                ),
            array(
                '%s',
                ),
            array('%d', '%d')
        );
    }
}

/**
 * Sets the default privacy anonymize settings.
 */
function quoteupSetPrivacyAnonymizeSettings()
{
    $isPrivacyDataExist = get_option('wdm_privacy_anonymize_data');
    if (false === $isPrivacyDataExist) {
        $privacyAnonymizeSettings['enquiry-detail'] = array(
            'name' => 'text',
            'email' => 'email'
        );
        $privacyAnonymizeSettings['enquiry-meta'] = array();

        update_option('wdm_privacy_anonymize_data', $privacyAnonymizeSettings);
    }
}

/**
 * Change the data type for 'Total' column to 'float' from 'int' in
 * 'enquiry_details_new' table when migrating to 6.5.0.
 *
 * @since 6.5.0
 *
 * @param  string  $enquiryDetailsTable  Table name 'enquiry_detail_new'.
 *
 * @return void
 */
function quoteupChangeTotalColDataType($enquiryDetailsTable)
{
    $quoteup_version = get_option('wdm_quoteup_version');
    if (false !== $quoteup_version && version_compare($quoteup_version, '6.5.0', '<')) {
        global $wpdb;
        
        // Change the data type for 'Total' column.
        $wpdb->query("ALTER TABLE $enquiryDetailsTable MODIFY COLUMN total FLOAT");
        delete_option('quoteup_total_updated');
        updateTotal();
    }
}

/**
 * Update the setting values for 'Price column' and 'Expected
 * Price or Remarks coumn'.
 * Change the following old settings values to new settings:
 *   disable_price_col   => enable_price_col
 *   disable_remarks_col => enable_remarks_col
 *
 * @since 6.5.0
 *
 * @return void
 */
function quoteupMigratePriceRemarksColSettings()
{
    $quoteupVersion = get_option('wdm_quoteup_version');
    if (version_compare($quoteupVersion, '6.5.0', '<')) {
        $oldNewSettingsKeyMap = array (
            'disable_price_col'   => 'enable_price_col',
            'disable_remarks_col' => 'enable_remarks_col',
        );
        $newSettings = get_option('wdm_form_data');

        foreach ($oldNewSettingsKeyMap as $oldKey => $newKey) {
            $newSettings[$newKey] = isset($newSettings[$oldKey]) && 1 == $newSettings[$oldKey] ? 0 : 1;
            unset($newSettings[$oldKey]);
        }

        update_option('wdm_form_data', $newSettings);
    }
}

/**
 * Change the old setting value 'show_enquiry_only_loggedin' to new setting value.
 * If 'show_enquiry_only_loggedin' was enabled in version prior to 6.5.0, store the
 * setting value "show_enq_btn_to_users => 'loggedin_users'", otherwise store
 * "show_enq_btn_to_users => 'all'".
 *
 * @since 6.5.0
 *
 * @return void
 */
function quoteupMigrateEnqBtnVisibilitySetting()
{
    $quoteupVersion = get_option('wdm_quoteup_version');

    if (version_compare($quoteupVersion, '6.5.0', '<')) {
        $quoteupSetting = get_option('wdm_form_data');

        if (empty($quoteupSetting['show_enquiry_only_loggedin'])) {
            $quoteupSetting['show_enq_btn_to_users'] = 'all';
        } elseif (1 == $quoteupSetting['show_enquiry_only_loggedin']) {
            $quoteupSetting['show_enq_btn_to_users'] = 'loggedin_users';
        }

        unset($quoteupSetting['show_enquiry_only_loggedin']);
        update_option('wdm_form_data', $quoteupSetting);
    }
}

