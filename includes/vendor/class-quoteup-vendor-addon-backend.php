<?php

namespace Includes\Admin\Vendor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * This class enables the vendors (for their products only) to view
 * and edit the enquiries made by the customer.
 */
class QuoteupVendorBackend
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
        $addOnInstance = quoteupGetVendorAddonInstance();

        $this->addRequiredColumnsInTables();

        // Return if user is admin and not a vendor.
        if (!quoteupIsCurrentLoggedInUserVendor() || !quoteupIsVendorCompEnabled()) {
            return;
        }

        // Modify capability, so that menus will be shown to the vendor
        add_filter('quoteup_quote_enquiry_capablity', array($addOnInstance, 'returnVendorCapabilty'));
        add_filter('quoteup_json_search_products_query', array($addOnInstance, 'updateProductSearchQuery'));
        // Change quote creation capability if current user is vendor
        add_filter('quoteup_quote_creation_capability', array($addOnInstance, 'returnVendorCapabilty'));
        add_filter('qupteup_reply_email_lists', array($addOnInstance, 'updateReplyEmailLists'));
        
        // security parameters check
        add_action('quoteup_validate_enquiry_edit_action', array($addOnInstance, 'validateEnquiryEditAction'));
        // add_filter('quoteup_inc_recipient_emails_in_reply', array($addOnInstance, 'shouldIncRecipientEmailsInReply'), 20);
        // add_action('quoteup_validate_enquiry_reply_action', array($addOnInstance, 'validateEnquiryReplyAction'));
    }

    public function adminInit()
    {
        $this->addRequiredColumnsInTables();
    }
   
    public function addRequiredColumnsInTables()
    {
        $this->addColumnsInEnquiryDetailsNewTable();
        $this->addColumnsInEnquiryProductsTable();
        $this->addColumnsInEnquiryQuotationTable();
    }

    /**
     * Add column 'quote_send_deadline' in the 'enquiry_detail_new' table.
     */
    public function addColumnsInEnquiryDetailsNewTable()
    {
        $enquiryDetailsNewTable = getEnquiryDetailsTable();
        quoteupAddColumnInTable($enquiryDetailsNewTable, 'quote_send_deadline', 'date');
    }
    
    /**
     * Add columns 'vendor_updated', 'removed in the
     * ‘enquiry_products’ table.
     */
    public function addColumnsInEnquiryProductsTable()
    {
        $enquiryProductsTable = getEnquiryProductsTable();
        quoteupAddColumnInTable($enquiryProductsTable, 'vendor_updated', 'varchar', 5);
        quoteupAddColumnInTable($enquiryProductsTable, 'removed', 'varchar', 5);
    }

    /**
     * Add column 'vendor_updated' in ‘enquiry_quotation’ table.
     */
    public function addColumnsInEnquiryQuotationTable()
    {
        $enquiryQuotationTable = getQuotationProductsTable();
        quoteupAddColumnInTable($enquiryQuotationTable, 'vendor_updated', 'varchar', 5);
    }
}

QuoteupVendorBackend::getInstance();
