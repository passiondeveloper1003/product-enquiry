<?php

namespace Includes\Vendor\Abstracts;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once QUOTEUP_VENDOR_DIR . '/quoteup-vendor-functions.php';

/**
 * This abstract class enables the vendors (for their products only) to create
 * the quotes, edit the enquiries, reply to enquiries made by the customer.
 */
abstract class QuoteupAbstractVendor
{
    /**
     * @var vendorRole The role name of vendor set by the multi vendor plugin
     */
    protected $vendorRole;

    /**
     * @var vendorCap  The capability of vendor set by the multi vendor plugin
     */
    protected $vendorCap;

    public function __construct()
    {
        if (defined('DOING_CRON') && DOING_CRON) {
            add_action('init', array($this, 'includeCronFiles'), 9);
        }
            
        add_action('init', array($this, 'includeFiles'));
    }

    public function includeCronFiles()
    {
        // Return if vendor compatibility not enabled.
        if (!quoteupIsVendorCompEnabled()) {
            return;
        }

        // Include cron manipulaion class.
        require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-crons.php';
                    
        // Include email classes.
        require_once QUOTEUP_VENDOR_DIR . '/emails/class-abstract-quoteup-reminder-emails.php';
        require_once QUOTEUP_VENDOR_DIR . '/emails/class-quoteup-vendor-reminder-email.php';
        require_once QUOTEUP_VENDOR_DIR . '/emails/class-quoteup-admin-reminder-email.php';
    }

    public function includeFiles()
    {
        if (is_admin()) {
            require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-addon-backend.php';
            require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-settings.php';
        }

        // Return if vendor compatibility not enabled.
        if (!quoteupIsVendorCompEnabled()) {
            return;
        }

        // Files for backend functionalities.
        if (is_admin()) {
            require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-enquiry-edit.php';
            require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-enquiry-list.php';
            
            // Inlcude email classes.
            require_once QUOTEUP_VENDOR_DIR . '/emails/class-abstract-quoteup-reminder-emails.php';
            require_once QUOTEUP_VENDOR_DIR . '/emails/class-quoteup-vendor-reminder-email.php';
            require_once QUOTEUP_VENDOR_DIR . '/emails/class-quoteup-admin-reminder-email.php';
            
            // Include cron manipulation class.
            require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-crons.php';
        }
        
        // Files for frontend functionalities.
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Include a file to make changes in the email sent to the vendor.
            require_once 'class-quoteup-vendor-emails.php';
        }
    }

    /**
     * This function is used to filter the products from $foundProducts
     * parameter with author_products
     *
     * @param array $foundProducts     product data
     * @param array $author_products   author products
     *
     * @return array $foundProducts    Return the products after performing filter.
     */
    public function filterVendorProducts($foundProducts)
    {
        if (empty($foundProducts) || empty(get_current_user_id())) {
            return $foundProducts;
        }

        $foundProducts = array_filter($foundProducts, 'quoteupIsProductOfAuthor', ARRAY_FILTER_USE_BOTH);
        return $foundProducts;
    }

    /**
     * Validate whether enquiryId belongs to current logged in vendor whenever
     * a vendor tries to edit any enquiry. Stop execution if enquiryId does not
     * belong to current logged in user.
     */
    public function validateEnquiryEditAction($enquiryId)
    {
        $isCurrVendorEnquiry = quoteupIsEnquiryOfVendor($enquiryId, get_current_user_id());
        if (false === $isCurrVendorEnquiry) {
            wp_die('<br><br><strong>'.__('This enquiry does not belong to you. Contact administrator!', QUOTEUP_TEXT_DOMAIN).'</strong>');
        }
    }

    public function validateEnquiryReplyAction($enquiryId)
    {
        $isCurrVendorEnq = quoteupIsEnquiryOfVendor($enquiryId, get_current_user_id());

        if (!$isCurrVendorEnq) {
            die(sprintf(__('The enquiry (%d) does not belong to you. Contact administrator!', QUOTEUP_TEXT_DOMAIN), $enquiryId));
        }
    }

    /**
     * Get vendor role and capability set earlier.
     */
    public function getVendorRoleCap()
    {
        return array(
            'role' => $this->vendorRole,
            'cap'  => $this->vendorCap
        );
    }

    /**
     * Set vendor role and capabilty by which user can be identified as vendor
     */
    protected function setRoleCap($role, $cap)
    {
        $this->vendorRole = $role;
        $this->vendorCap = $cap;
    }

    /**
     * Override this method in extended class (if needed) because each vendor
     * plugins may have different ways to check current user is vendor or not.
     *
     * Why not using the Vendor plugins function to check whether the user is
     * vendor or not => The vendor plugins may check current user is vendor or
     * nor on the basis of capability. But in the case of users such as admin,
     * that function will still return true. But we want that it should return
     * true only if current user is vendor and not any higher level user.
     *
     * @return boolean true if vendor, false otherwise
     */
    public function isVendor($userData = null)
    {
        if (empty($userData)) {
            $userData = wp_get_current_user();
        }

        $isVendor      = false;
        $vendorRoleCap = $this->getVendorRoleCap();

        if (user_can($userData->ID, $vendorRoleCap['cap'])) {
            $isVendor = true;
        }

        return apply_filters('quoteup_is_user_vendor', $isVendor, $userData);
    }

    /**
     * Add vendor email in the 'Reply-to' header of the email sent to the
     * customer, when vendor directly sends reply from the dashboard.
     *
     * @param   array $admin_emails Array containing email lists
     *
     * @return  array               Add vendor email in the 'admin_emails' email
     *                              list.
     */
    public function updateReplyEmailLists($admin_emails)
    {
        $vendor_email = wp_get_current_user()->user_email;
        $admin_emails[] = $vendor_email;
        return $admin_emails;
    }

    /**
     * Returns whether to include recipient emails in reply sent by the vendor.
     *
     * @return bool false
     */
    public function shouldIncRecipientEmailsInReply()
    {
        return false;
    }

    public function updateProductSearchQuery($query)
    {
        $productIds = getProductIdsByAuthor();

        if (!empty($productIds)) {
            $append = " AND posts.ID IN ('".implode("','", $productIds) ."') ";
            $query .= $append;
        }

        return $query;
    }

    /**
     * Whenever vendor user is created, specific capability is added to vendor.
     * For example, dokan vendor plugin adds 'dokandar' as capability whenever
     * new vendor is created.
     *
     * @param  $capability string This method is also used in filter, so need to
     *                            accept parameter
     * @return string             Vendor specific capability
     */
    abstract public function returnVendorCapabilty($capability = null);
}
