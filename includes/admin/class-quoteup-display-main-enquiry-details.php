<?php

namespace Admin\Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This class is used to create meta box for Main enquiry on enquiry details page and display Main enquiry in that box.
 * @static $instance Object of class
  * $enquiry_details array enquiry details
 */
class QuoteupDisplayMainEnquiry
{
    protected static $instance = null;
    public $enquiry_details = null;

    /**
     * Function to create a singleton instance of class and return the same.
     * @return object instance of the class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor is used to add action for main enquiry meta box.
     */
    public function __construct()
    {
        // Display the admin notification
        add_action('quoteup_after_product_details', array($this, 'mainEnquiryMeta'));
    }

    /**
     * Create Meta box with heading "Enquiry Details".
     * @param array $enquiry_details Enquiry details
     */
    public function mainEnquiryMeta($enquiry_details)
    {
        $this->enquiry_details = $enquiry_details;
        global $quoteup_admin_menu;
        $form_data = get_option('wdm_form_data');
        $showMainEnquiry = 1;
        if (isset($form_data[ 'enable_disable_quote' ]) && $form_data[ 'enable_disable_quote' ] == 1) {
            $showMainEnquiry = 0;
        }

        if ($showMainEnquiry == 1) {
            add_meta_box('editMainEnquiry', __('Enquiry Details', QUOTEUP_TEXT_DOMAIN), array(
            $this, 'editMainEnquiryFn', ), $quoteup_admin_menu, 'normal');
        }
    }

    /**
     * This function displays Products added in enquiry at time of enquiry.
     */
    public function editMainEnquiryFn()
    {
        global $quoteup;
        $quoteup->enquiryDetailsEdit->enquiryTable();
    }
}

$this->displayMainEnquiry = QuoteupDisplayMainEnquiry::getInstance();
