<?php

namespace Includes\Admin\DefaultPage;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('\Includes\Admin\Abstracts\QuoteupDefaultPage')) {
    //This file handles the edit quote page functions.
    require_once QUOTEUP_PLUGIN_DIR.'/includes/admin/abstracts/class-quoteup-default-page.php';
}

/**
 * This class is used to create the Quotation Approval/ Rejection page on
 * fresh installation of PEP plugin for version 6.5.0 or above.
 */
class QuoteupApprovalRejectionPage extends \Includes\Admin\Abstracts\QuoteupDefaultPage
{
    /**
     * Instance of the class QuoteupApprovalRejectionPage.
     *
     * @var QuoteupApprovalRejectionPage
     */
    private static $instance = null;

    /**
     * Quote Approval/ Rejection page setting name.
     *
     * @var  string
     */
    public $settingName;

    /**
     * Return the *Singleton* instance of class.
     *
     * @return QuoteupApprovalRejectionPage Return the instance of the class.
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
        $this->settingName = 'approval_rejection_page';
    }

    /**
     * Set up Quotation Approval/ Rejection page during the Setup Wizard.
     *
     * @since 6.5.0
     *
     * @return void
     */
    public function setUpPage()
    {
        $pageId = $this->createPage();
        $this->setPageIdInSetting($pageId);
        
        // update_option('is_approval_rejection_page_set', '1');
    }

    /**
     * Create Quotation Approval/ Rejection page.
     *
     * @return int  Return page Id.
     */
    public function createPage()
    {
        $pageTitle   = _x('Quote Approval & Rejection', 'Page title', QUOTEUP_TEXT_DOMAIN);
        $pageContent = '<!-- wp:shortcode -->[APPROVAL_REJECTION_CHOICE]<!-- /wp:shortcode -->';
 
        $pageData = array(
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'post_name'      => 'approval-rejection',
            'post_title'     => $pageTitle,
            'post_content'   => $pageContent,
            'post_parent'    => 0,
            'comment_status' => 'closed',
        );
        $pageId  = wp_insert_post($pageData);

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
