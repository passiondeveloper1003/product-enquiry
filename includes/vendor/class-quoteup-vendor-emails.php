<?php

namespace Includes\Frontend\Vendor;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * This class sends the enquiry emails to the vendor of each product.
 * The enquiry emails contain only those products that belong to particular vendor.
 */
class QuoteupVendorEmails
{
    private static $instance;
    private $authorProducts;

    /**
    * Constructor for calling various functions for sending enquiry mail.
    * @param int $enquiryId Id of enquiry
    * @param array array of author emails
    * @param string subject for mail
    */
    private function __construct()
    {
        add_filter('quoteup_merge_author_emails_in_admin_email', array($this, 'shouldMergeAuthorEmailsInAdminEmail'));
        add_filter('quoteup_enquiry_mail_heading', array($this, 'modifyEnquiryMailHeading'), 20, 3);
        add_filter('quoteup_update_author_mail_header', array($this, 'modifyEnquiryMailHeader'));
        add_filter('quoteup_custom_form_fields_in_enq_email', array($this, 'returnAllowedCustomFormFieldsInEmail'), 20, 2);
        add_filter('quoteup_customer_email_in_tc_text', array($this, 'returnCustomerEmailForTCText'), 20, 2);
        add_filter('quoteup_include_field_in_cust_email_df', array($this, 'checkFieldShouldBeIncluded'), 20, 3);
        add_action('quoteup_after_product_table_enquiry_mail', array($this, 'checkAndSetQuoteSendDeadline'), 20, 3);
    }

    /**
    * This function sends the Singleton class Object.
    * @return static Object of the class.
    */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Determines whether authors emails should be added in recipients list.
     * Checks whether all author emails are same. If yes, then allow to
     * add author email in the recipients list.
     *
     * @param   array   $authorEmails   Contains authors emails.
     *
     * @return  bool    Returns true if all author emails are same, false
     *                  otherwise.
     */
    public function shouldAddAuthorEmailInEnquiry($authorEmails)
    {
        unset($authorEmails);
        return false;
    }

    public function updateAdminMailRecipient($recipients)
    {
        global $quoteup;
        $quoteupSettings = quoteupSettings();
        if (!quoteupIsMPEEnabled($quoteupSettings)) {
            return $recipients;
        }

        $this->authorProducts = array();
        $authorEmail = array();
        $prod = $quoteup->wcCartSession->get('wdm_product_info');
        foreach ($prod as $arr) {
            $email = $arr['author_email'];
            array_push($authorEmail, $email);
            $this->authorProducts[$email][] = $arr;
        }

        $recipients = array_diff($recipients, $authorEmail);
        add_action('quoteup_before_sending_email_to_customer', array($this, 'sendEmailToAuthors'), 20);

        return $recipients;
    }

    /**
     * Callback for 'quoteup_after_enquiry_email_sent' action.
     * Sends emails to different authors about their products.
     *
     * @param   object  $emailObject    Object of class 'SendEnquiryMail'.
     */
    public function sendEmailToAuthors($emailObject)
    {
        global $quoteup;
        $adminEmail = get_option('admin_email');
        add_filter('quoteup_enquiry_mail_args', array($this, 'updateEnquiryMailArgs'), 20, 2);
        add_filter('quoteup_product_data_in_enquiry_mail', array($this, 'updateProductDetailsForAuthorEmail'), 20, 2);
        
        $prod = $quoteup->wcCartSession->get('wdm_product_info');
        foreach ($prod as $arr) {
            $email = $arr['author_email'];

            if ($email == $adminEmail) {
                continue;
            }
            $this->authorProducts[$email][] = $arr;
        }

        if (count($this->authorProducts) >= 1) {
            $emailObject->setSource('author');
            $subject = $emailObject->get_subject();
            $headers = $emailObject->get_admin_headers();
            $attachments = $emailObject->get_attachments();
            foreach ($this->authorProducts as $authorEmail => $products) {
                $emailObject->recipient    = apply_filters('quoteup_update_author_mail_recipient', $authorEmail);
                $message                   = $emailObject->get_content();
                if (!empty($emailObject->recipient)) {
                    $emailObject->send($emailObject->recipient, $subject, $message, $headers, $attachments);
                }
                unset($products);
            }
        }

        remove_filter('quoteup_enquiry_mail_args', array($this, 'updateEnquiryMailArgs'), 20);
        remove_filter('quoteup_product_data_in_enquiry_mail', array($this, 'updateProductDetailsForAuthorEmail'));
    }

    /**
     * Callback for 'quoteup_enquiry_mail_args' function.
     * Adds 'author_copy' key element to the '$args' array.
     *
     * @param   array   $args   Data required for email template.
     *
     * @return  array   $args   Adds the 'author_copy' key element and
     *                          returns $args array.
     */
    public function updateEnquiryMailArgs($args, $emailObject)
    {
        $args['vendorEmail'] = $emailObject->recipient;
        return $args;
    }

    public function updateProductDetailsForAuthorEmail($prod, $authorEmail)
    {
        if (null !== $authorEmail) {
            $productDetails = $this->authorProducts[$authorEmail];
        }

        unset($prod);
        return $productDetails;
    }

    /**
     * Callback function for 'quoteup_merge_author_emails_in_admin_email' filter hook.
     *
     * Return false to not to allow vendor emails to be merged with admin
     * email.
     *
     * @return  bool    Return false.
     */
    public function shouldMergeAuthorEmailsInAdminEmail()
    {
        /*
          If want to include vendor email when an enquiry is sent to the admin,
          return true.
        */
        return false;
    }

    public function modifyEnquiryMailHeading($heading, $dataObtainedFromName, $source)
    {
        if ('author' == $source) {
            $defaultFields = quoteupGetVendorAccessibleDefaultFields();
            if (!array_key_exists('name', $defaultFields)) {
                $heading = isQuotationModuleEnabled(quoteupSettings()) ? __('Quote Request from customer', QUOTEUP_TEXT_DOMAIN) : __('Enquiry from customer', QUOTEUP_TEXT_DOMAIN);
            }
        }
        unset($dataObtainedFromName);
        return $heading;
    }

    /**
     * Callback for 'quoteup_update_author_mail_header' filter hook.
     *
     * Remove the customer email from the 'Reply-to:' inside headers if
     * customer email is not allowed to the vendors.
     */
    public function modifyEnquiryMailHeader($headers)
    {
        $defaultFields = quoteupGetVendorAccessibleDefaultFields();
        if (!array_key_exists('email', $defaultFields)) {
            $headers = "Content-Type: text/html\r\n";
        }

        return $headers;
    }

    public function returnAllowedCustomFormFieldsInEmail($data, $source)
    {
        if ('author' == $source) {
            $defaultFields = quoteupGetVendorAccessibleDefaultFields();
            $metaFields    = quoteupGetVendorAccessibleMetaFields();
           
            if (array_key_exists('name', $defaultFields)) {
                $metaFields['custname'] = '';
            }

            if (array_key_exists('email', $defaultFields)) {
                $metaFields['txtemail'] = '';
            }

            $data = array_intersect_key($data, $metaFields);
        }

        return $data;
    }

    public function returnCustomerEmailForTCText($customerEmail, $source)
    {
        $defaultFields = quoteupGetVendorAccessibleDefaultFields();
        
        if ('author' == $source && !array_key_exists('email', $defaultFields)) {
            $customerEmail = 'Customer';
        }
        return $customerEmail;
    }

    public function checkFieldShouldBeIncluded($inlcudeField, $fieldData, $source)
    {
        if ('author' != $source) {
            return $inlcudeField;
        }

        $defaultFields = quoteupGetVendorAccessibleDefaultFields();
        $fieldKey      = '';

        switch ($fieldData['id']) {
            case 'custname':
                $fieldKey = 'name';
                break;
            case 'txtemail':
                $fieldKey = 'email';
                break;
            case 'txtphone':
                $fieldKey = 'phone_number';
                break;
            case 'txtdate':
                $fieldKey = 'date_field';
                break;
            // case 'wdmFileUpload':
            //     $fieldKey = 'phone_number';
            //     break;
            case 'txtsubject':
                $fieldKey = 'subject';
                break;
            case 'txtmsg':
                $fieldKey = 'message';
                break;
        }

        if (array_key_exists($fieldKey, $defaultFields)) {
            $inlcudeField = true;
        } else {
            $inlcudeField = false;
        }
        return $inlcudeField;
    }

    /**
     * Callback for 'quoteup_after_product_table_enquiry_mail' action hook.
     * Check whether auto set deadline is enabled.
     * If enabled, set the crons.
     */
    public function checkAndSetQuoteSendDeadline($formDataForMail, $source, $args)
    {
        if (isQuotationModuleEnabled() && quoteupIsVendorCompEnabled() && quoteupIsAutoSetDeadlineEnabled() && 'cc' != $source) {
            global $wpdb;
            static $deadline     = null;
            $enquiryId           = $args['enquiryId'];
            $enquiryDetailsTable = getEnquiryDetailsTable();

            if (!class_exists('\Includes\Admin\Vendor\QuoteupVendorCrons')) {
                require_once QUOTEUP_VENDOR_DIR . '/class-quoteup-vendor-crons.php';
            }

            /*
               If source is admin, set deadline. This same deadline variable is
               used in admin email as well as in the author email. Hence, it
               has been kept static. So, there is no need to fetch the deadline
               from the database when adding a deadline information in the
               author email.
             */
            if ('admin' == $source) {
                $autoSetDeadlineDays = quoteupGetAutoSetDlAfterDays();
                $autoSetDeadlineDays = '+' . $autoSetDeadlineDays. ' days';
                $deadline  = date('Y-m-d', strtotime($autoSetDeadlineDays));
                $deadline  = apply_filters('quoteup_dt_for_auto_set_deadline', $deadline, $enquiryId);
                \Includes\Admin\Vendor\QuoteupVendorCrons::getInstance()->setCrons($enquiryId, $deadline);
    
                $wpdb->update(
                    $enquiryDetailsTable,
                    array(
                        'quote_send_deadline' => $deadline,
                    ),
                    array(
                        'enquiry_id' => $enquiryId,
                    )
                );
            }
            
            // This same deadline will be used in admin as well as author email.
            $deadlineText = sprintf(__('The deadline for the quotation is %s', QUOTEUP_TEXT_DOMAIN), $deadline);
            $deadlineText = apply_filters('quoteup_auto_deadline_text_email', $deadlineText, $deadline, $enquiryId);
            echo '<p>'.$deadlineText.'</p>';
        }
        unset($formDataForMail);
    }
}

QuoteupVendorEmails::getInstance();
