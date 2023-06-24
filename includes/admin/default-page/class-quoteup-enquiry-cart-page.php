<?php

namespace Includes\Admin\DefaultPage;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('\Includes\Admin\Abstracts\QuoteupDefaultPage')) {
    // This file handles the edit quote page functions.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/abstracts/class-quoteup-default-page.php';
}

/**
 * This class is used to create the Enquiry Cart page during
 * Setup Wizard.
 */
class QuoteupEnquiryCartPage extends \Includes\Admin\Abstracts\QuoteupDefaultPage
{
    /**
     * Instance of the class QuoteupEnquiryCartPage.
     *
     * @var  QuoteupEnquiryCartPage
     */
    private static $instance = null;

    /**
     * Enquiry Cart page setting name.
     *
     * @var  string
     */
    public $settingName;

    /**
     * Return the *Singleton* instance of class.
     *
     * @return  QuoteupEnquiryCartPage  Return the instance of the class.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->settingName = 'mpe_cart_page';
    }

    /**
     * Set up Enquiry Cart page during the Setup Wizard.
     *
     * @since 6.5.0
     *
     * @return void
     */
    public function setUpPage()
    {
        $pageId = $this->createPage();
        $this->setPageIdInSetting($pageId);
        
        // update_option('is_ec_page_set', '1');
    }

    /**
     * Create an enquiry cart page.
     *
     * @return int  Return page Id.
     */
    public function createPage()
    {
        $pageTitle   = _x('Enquiry Cart', 'Page Title', QUOTEUP_TEXT_DOMAIN);
        $pageContent = '<!-- wp:shortcode -->[ENQUIRY_CART]<!-- /wp:shortcode -->';
 
        $pageData = array(
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'post_name'      => 'enquiry-cart',
            'post_title'     => $pageTitle,
            'post_content'   => $pageContent,
            'post_parent'    => 0,
            'comment_status' => 'closed',
        );
        $pageId = wp_insert_post($pageData);

        return $pageId;
    }

    /**
     * Set the page Id for PEP setting 'Approval/Rejection Page'.
     * Product Enquiry Pro > Settings > Quotation > Approval/Rejection Page.
     *
     * @param   int     $pageId     Page Id to set for the PEP setting
     *                              'Approval/Rejection Page'.
     *
     * @return void
     */
    public function setPageIdInSetting($pageId)
    {
        $quoteupSettings                     = quoteupSettings();
        $quoteupSettings[$this->settingName] = $pageId;

        update_option('wdm_form_data', $quoteupSettings);
    }
}
