<?php
/**
 * Class SampleTest
 *
 * @package Product_Enquiry_Pro
 */

/**
 * Sample test case.
 */
class TestQuoteupHelperFunctions extends WP_UnitTestCase
{
    // public function setUp()
    // {
    //     // unset()
    //     // $this->testsub = new TestSubject();
    // }
    
    // public function tearDown()
    // {
    //     // unset($this->testsub);
    //     echo "inside tear down";
    //     global $wp_object_cache;
    //     echo " 1 > " . var_export();
    //     $wp_object_cache->set('wdm_form_data', array() , 'options');
    //     echo " > " . var_export($wp_object_cache->get('wdm_form_data', 'options'), true);
    // }


    // /**
    //  * A single example test.
    //  */
    // public function test_sample() {
    //  // Replace this with some actual testing code.
    //  $this->assertTrue( true );
    // }
    
    // public function test_sample2() {
    //  // Replace this with some actual testing code.
    //  $this->assertTrue( true );
    // }

    public function test_quoteupReturnSumOfProductPrices()
    {
        $productDetails = array(
            0 => array(
                'price'    => '49.8',
                'quantity' => '6',
            ),
            1 => array(
                'price'    => '45.4',
                'quantity' => '4'
            ),
        );
        $this->assertEquals(480.4, quoteupReturnSumOfProductPrices($productDetails));

        // When product price is not set or product price set to 0.
        $productDetails = array(
            0 => array(
                'price'    => '0',
                'quantity' => '5',
            ),
            1 => array(
                'price'    => '',
                'quantity' => '1'
            ),
        );
        $this->assertEquals(0, quoteupReturnSumOfProductPrices($productDetails));
    }

    function test_quoteupReturnOldNewPriceChangeInPerc() {
        $this->assertEquals(0.0000, quoteupReturnOldNewPriceChangeInPerc(500, 500));

        $this->assertEquals(20, quoteupReturnOldNewPriceChangeInPerc(100, 80));
        $this->assertEquals(-20, quoteupReturnOldNewPriceChangeInPerc(100, 120));

        $this->assertEquals(0.50000, quoteupReturnOldNewPriceChangeInPerc(200, 199));
        $this->assertEquals(-0.50000, quoteupReturnOldNewPriceChangeInPerc(200, 201));
    }

    function test_quoteupReturnClassForCostChange()
    {
        $this->assertEquals('no-cost-change', quoteupReturnClassForCostChange(0));
        $this->assertEquals('no-cost-change', quoteupReturnClassForCostChange(0.0));

        $this->assertEquals('positive-cost-change', quoteupReturnClassForCostChange(0.1));
        $this->assertEquals('negative-cost-change', quoteupReturnClassForCostChange(-0.1));

        $this->assertEquals('positive-cost-change', quoteupReturnClassForCostChange(20.2));
        $this->assertEquals('negative-cost-change', quoteupReturnClassForCostChange(-20));
    }

    /**
     ******************************************************************************
     * PHPUnit Testing for  Quoteup settings functions.
     ******************************************************************************
     */

    public function test_isQuotationModuleEnabled()
    {
        $emptyArray           = array();
        $arraySettingDisabled = array(
            'enable_disable_quote' => '0'
        );
        $arraySettingEnabled  = array(
            'enable_disable_quote' => '1'
        );

        $this->assertEquals(true, isQuotationModuleEnabled(array()));
        $this->assertEquals(true, isQuotationModuleEnabled($arraySettingDisabled));
        $this->assertEquals(false, isQuotationModuleEnabled($arraySettingEnabled));
    }

    public function test_quoteupIsOutOfStockSettingEnabled()
    {
        $emptyArray           = array();
        $arraySettingDisabled = array(
            'only_if_out_of_stock' => '0'
        );
        $arraySettingEnabled  = array(
            'only_if_out_of_stock' => '1'
        );

        $this->assertEquals(false, quoteupIsOutOfStockSettingEnabled($emptyArray));
        $this->assertEquals(false, quoteupIsOutOfStockSettingEnabled($arraySettingDisabled));
        $this->assertEquals(true, quoteupIsOutOfStockSettingEnabled($arraySettingEnabled));
    }

    function test_quoteupReturnEnqBtnVisibilitySetting()
    {
        $emptySettings    = array();
        $allValueSettings = array (
            'show_enq_btn_to_users' => 'all',
        );
        $loggedinValueSettings = array (
            'show_enq_btn_to_users' => 'loggedin_users',
        );
        $guestValueSettings = array (
            'show_enq_btn_to_users' => 'loggedin_users',
        );

        $this->assertEquals('all', quoteupReturnEnqBtnVisibilitySetting($emptySettings));
        $this->assertEquals('all', quoteupReturnEnqBtnVisibilitySetting($allValueSettings));
        $this->assertEquals('loggedin_users', quoteupReturnEnqBtnVisibilitySetting($loggedinValueSettings));
        $this->assertEquals('loggedin_users', quoteupReturnEnqBtnVisibilitySetting($guestValueSettings));
    }

    public function test_quoteupReturnToShopBtnLabel()
    {
        $quoteupSettingsEmptyArray = array();
        $quoteupSettingsEmptyValue = array(
            'ret_to_shop_btn_label' => '',
        );
        $quoteupSettingsWithValue  = array(
            'ret_to_shop_btn_label' => 'Go to Shop page',
        );

        $this->assertEquals(quoteupReturnToShopBtnDefaultLabel(), quoteupReturnToShopBtnLabel($quoteupSettingsEmptyArray));
        $this->assertEquals(quoteupReturnToShopBtnDefaultLabel(), quoteupReturnToShopBtnLabel($quoteupSettingsEmptyValue));
        $this->assertEquals('Go to Shop page', quoteupReturnToShopBtnLabel($quoteupSettingsWithValue));
    }

    public function test_quoteupReturnToShopBtnURL()
    {
        $quoteupSettingsEmptyArray = array();
        $quoteupSettingsEmptyValue = array(
            'ret_to_shop_btn_url' => '',
        );
        $quoteupSettingsWithValue = array(
            'ret_to_shop_btn_url' => 'https://localhost/wordpress'
        );

        $this->assertEquals(quoteupReturnToShopBtnDefaultURL(), quoteupReturnToShopBtnURL($quoteupSettingsEmptyArray));
        $this->assertEquals(quoteupReturnToShopBtnDefaultURL(), quoteupReturnToShopBtnURL($quoteupSettingsEmptyValue));
        $this->assertEquals('https://localhost/wordpress', quoteupReturnToShopBtnURL($quoteupSettingsWithValue));
    }

    public function test_quoteupIsQuantityFieldEnabled()
    {
        $arraySettingEnabled  = array(
            'enable_qty_field' => true
        );

        $this->assertEquals(false, quoteupIsQuantityFieldEnabled());

        update_option('wdm_form_data', $arraySettingEnabled);
        $this->assertEquals(true, quoteupIsQuantityFieldEnabled());
    }

    public function test_quoteupIsHidePriceSettingEnabled()
    {
        $quoteupSettings = array();
        $this->assertEquals(false, quoteupIsHidePriceSettingEnabled($quoteupSettings));

        $quoteupSettings = array(
            'hide_price_on_all_products' => '0'
        );
        $this->assertEquals(false, quoteupIsHidePriceSettingEnabled($quoteupSettings));

        $quoteupSettings = array(
            'hide_price_on_all_products' => '1'
        );
        $this->assertEquals(true, quoteupIsHidePriceSettingEnabled($quoteupSettings));
    }

    public function test_quoteupIsHideAddToCartSettingEnabled()
    {
        $quoteupSettings = array();
        $this->assertEquals(false, quoteupIsHideAddToCartSettingEnabled($quoteupSettings));

        $quoteupSettings = array(
            'hide_add_to_cart_on_all_products' => '0'
        );
        $this->assertEquals(false, quoteupIsHideAddToCartSettingEnabled($quoteupSettings));

        $quoteupSettings = array(
            'hide_add_to_cart_on_all_products' => '1'
        );
        $this->assertEquals(true, quoteupIsHideAddToCartSettingEnabled($quoteupSettings));
    }

    public function test_quoteupSettingReturnQuotePDFEmailFooterText()
    {
        $quoteupSettings = array();
        $this->assertEquals(quoteupQuotePDFEmailDefaultFooterText(), quoteupSettingReturnQuotePDFEmailFooterText($quoteupSettings));

        $quoteupSettings = array(
            'quote_pdf_email_footer_text' => '',
        );
        $this->assertEquals('', quoteupSettingReturnQuotePDFEmailFooterText($quoteupSettings));

        $quoteupSettings = array(
            'quote_pdf_email_footer_text' => 'Quote includes 10% tax',
        );
        $this->assertEquals('Quote includes 10% tax', quoteupSettingReturnQuotePDFEmailFooterText($quoteupSettings));
    }

    /**
     ******************************************************************************
     * End for PHPUnit Testing for Quoteup settings functions.
     ******************************************************************************
     */
}


function quoteupDropTables($tableNames = array())
{
    if (empty($tableName)) {
        $tableName = array(
            'enquiry_detail_new'
        );
    }

    foreach ($tableNames as $tableName) {
        global $wpdb;
        $sql        = "DROP TABLE IF EXISTS {$wpdb->prefix}{$tableName}";
        $wpdb->query($sql);
    }   
}
