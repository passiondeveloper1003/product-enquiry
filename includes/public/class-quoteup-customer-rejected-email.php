<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

WC()->mailer();

/**
 * This class is used to send mail to admin when a customer rejects the quote.
 *
 * @since 6.5.0
 */
class QuoteupCustomerRejectedEmail extends \WC_Email
{
    /**
     * The reference to *Singleton* instance of the class.
     *
     * @var QuoteupCustomerRejectedEmail 
     */
    private static $instance = null;

    /**
	 * Enquiry Id.
	 *
	 * @var int
	 */
    public $enquiryId;

    /**
	 * Quote rejected message.
	 *
	 * @var string
	 */
    public $rejectedMessage;

    /**
	 * Email header.
	 *
	 * @var string
	 */
    public $headers;

    /**
	 * Email type.
	 *
	 * @var string
	 */
    public $emailType;

    /**
	 * Quotation products data.
     *
	 * @var array
	 */
    public $quotationData;

    /**
	 * Customer name.
     *
	 * @var string
	 */
    public $customerName;

    /**
	 * Customer email.
     *
	 * @var string
	 */
    public $customerEmail;

    /**
     * Returns the *Singleton* instance of class.
     *
     * @return QuoteupCustomerRejectedEmail Return the instance of the class.
     */
    public static function getInstance($enquiryId, $message)
    {
        if (null === static::$instance) {
            static::$instance = new static($enquiryId, $message);
        }
        
        return static::$instance;
    }

    /**
     * Constructor for calling various functions for sending enquiry mail.
     *
     * @param int   $enquiryId Id of enquiry
     * @param array array of author emails
     * @param string subject for mail
     */
    public function __construct($enquiryId, $message)
    {
        parent::__construct();

        $this->enquiryId        = (int) $enquiryId;
        $this->rejectedMessage  = sanitize_textarea_field($message);
        $this->quotationData    = $this->getQuotationData();
        $this->recipient        = $this->getRecipient();
        $this->customerName     = $this->getCustomerName();
        $this->customerEmail    = $this->getCustomerEmail();
        $this->subject          = $this->getMailSubject();
        $this->headers          = $this->getAdminHeaders();
        $this->emailType        = 'text/html';

        // Add stylesheet content
        add_filter('woocommerce_email_styles', array($this, 'addCSS'));
        // Trigger for sending the email
        add_action('send_quote_rejected_email', array( $this, 'trigger' ), 15);
    }

    /**
     * This returns the CSS for Email.
     *
     * Callback for WooCommerce filter 'woocommerce_email_styles'.
     */
    public function addCSS($css)
    {
        $stylesheet = file_get_contents(QUOTEUP_PLUGIN_DIR.'/css/public/quote-rejected-email.css');
        $stylesheet.= file_get_contents(QUOTEUP_PLUGIN_DIR.'/css/admin/price-change.css');
        return $css.$stylesheet;
    }

    /**
     * Send the quotation rejected email to the admin.
     *
     * Callback for action hook 'send_quote_rejected_email'.
     */
    public function trigger()
    {
        $this->emailContent = $this->get_content();
        $this->send($this->recipient, $this->subject, $this->emailContent, $this->headers, array());
    }

    /**
     * Gets the content in HTML for the mail to be sent.
     *
     * @return HTML Content of email.
     */
    public function get_content_html()
    {
        $quoteupSettings = quoteupSettings();
        $showPrice       = isset($this->quotationData[0]) ? $this->quotationData[0]['show_price'] : 'no';

        ob_start();
        $args = array(
            'enquiry_id'        => $this->enquiryId,
            'customer_name'     => $this->customerName,
            'customer_email'    => $this->customerEmail,
            'rejected_message'  => $this->rejectedMessage,
            'quotation_details' => $this->quotationData,
            'show_price'        => $showPrice,
        );
        $args = apply_filters('rejected_quote_email_args', $args);

        // This loads the template for Rejected Quote Email
        quoteupGetPublicTemplatePart('quote-rejected-mail/quote-rejected-mail', '', $args);

        $emailContent = ob_get_clean();

        /**
         * Use this filter to decide whether to include the company logo in an
         * quote rejected email or not.
         *
         * @since 6.5.0
         *
         * @param   bool    Boolean value indicating whether to include the
         *                  company logo in quote rejected email.
         */
        $should_add_logo = apply_filters('should_add_comp_logo_in_quote_rejected_email', true);
        if (isset($quoteupSettings['company_logo']) && !empty($quoteupSettings['company_logo']) && $should_add_logo) {
            $img = '<div id="template_header_image"><p style="margin-top:0;"><img src="' . esc_url($quoteupSettings['company_logo']) . '" alt="' . esc_attr(get_bloginfo('name', 'display')) . '" /></p></div>';

            $emailContent = preg_replace('/<div id="template_header_image">[\s\S]*?(<\/div>)/', $img, $emailContent);
        }

        return wp_specialchars_decode($emailContent, ENT_QUOTES);
    }

    /**
     * Get headers for the email.
     *
     * @return string Return headers for the email.
     */
    public function getAdminHeaders()
    {
        $email   = $this->customerEmail;
        $headers = "Reply-to: " . $email . "\r\n";

        /**
         * Filter to modify the rejected quote email headers.
         *
         * @since 6.5.0
         *
         * @param   string  Rejected quote email headers.
         */
        return apply_filters('rejected_quote_email_headers', $headers);
    }

    /**
	 * Get valid recipients.
	 *
	 * @return  string  Return the recipients.
	 */
	public function getRecipient()
    {
        $recipient         = array();
        $adminEmail        = array();
        $authorEmails      = array();
        $quoteupRecipients = array();
        $quoteupSettings   = quoteupSettings();

        // Retrieve admin email
        if (isset($quoteupSettings['send_mail_to_admin']) && '1' == $quoteupSettings['send_mail_to_admin']) {
            $adminEmail = get_option('admin_email');
            array_push($recipient, $adminEmail);
        }

        // Retrieve author emails
        if (isset($quoteupSettings['send_mail_to_author']) && '1' == $quoteupSettings['send_mail_to_author']) {
            global $wpdb;
            $productIds = array();
            foreach ($this->quotationData as $quoteProduct) {
                array_push($productIds, $quoteProduct['product_id']);
            }

            $productIds   = implode(',', $productIds);
            $query        = "SELECT DISTINCT user_email FROM {$wpdb->posts} AS p JOIN {$wpdb->users} AS u ON p.post_author = u.ID WHERE p.id IN ($productIds)";
            $authorEmails = $wpdb->get_col($query);
            $recipient    = array_merge($recipient, $authorEmails);
        }

        // Retrieve emails specified in the 'Recipient Email Addresses' setting
        if (!empty($quoteupSettings['user_email'])) {
            $quoteupRecipients = explode(',', $quoteupSettings['user_email']);
            $recipient         = array_merge($recipient, $quoteupRecipients);
        }

        $recipient = array_map('trim', $recipient);
        $recipient = array_unique($recipient);

        /**
         * Use filter to modify the recipients for the quote rejected email.
         *
         * @since 6.5.0
         *
         * @param array  $recipient         Recipient email addresses.
         * @param string $adminEmail        Admin email address.
         * @param array  $authorEmails      Product author email address.
         * @param array  $quoteupRecipients Email addresses specified in PEP setting.
         */
        $recipient = apply_filters('quote_rejected_email_recipients', $recipient, $adminEmail, $authorEmails, $quoteupRecipients);

		return implode( ', ', $recipient);
	}

    /**
     * This function is used to set subject for rejected email.
     *
     * @return  string  Return the subject for the quote rejected email.
     */
    public function getMailSubject()
    {
        $subject = sprintf(__('Quote (#%1$d) Rejected by %2$s - %3$s'), $this->enquiryId,$this->customerName, get_bloginfo());

        /**
         * Filter to modify the rejected quote email subject.
         *
         * @since 6.5.0
         *
         * @param   string  Rejected quote email subject.
         * @param   int     Enquiry/ Quote Id.
         */
        return apply_filters('quote_rejected_mail_subject', $subject, $this->enquiryId);
    }


    /**
     * Return the customer email address associated with the rejected quote.
     *
     * @param   string  Return the customer email address.
     */
    public function getCustomerName()
    {
        global $wpdb;
        $tableName = getEnquiryDetailsTable();
        $name     = $wpdb->get_var($wpdb->prepare("SELECT name FROM $tableName WHERE enquiry_id = %d", $this->enquiryId));

        /**
         * Filter to modify the customer name.
         *
         * @since 6.5.0
         *
         * @param   string  Customer name.
         * @param   int     Enquiry/ Quote Id which has been rejected by the customer.
         */
        $name = apply_filters('quote_rejected_customer_name', $name, $this->enquiryId);
        return $name;
    }

    /**
     * Return the customer email address associated with the rejected quote.
     *
     * @param   string  Return the customer email address.
     */
    public function getCustomerEmail()
    {
        global $wpdb;
        $tableName = getEnquiryDetailsTable();
        $email     = $wpdb->get_var($wpdb->prepare("SELECT email FROM $tableName WHERE enquiry_id = %d", $this->enquiryId));

        /**
         * Filter to modify the customer email address.
         *
         * @since 6.5.0
         *
         * @param   string  Customer Email address.
         * @param   int     Enquiry/ Quote Id which has been rejected by the customer.
         */
        $email = apply_filters('quote_rejected_customer_email', $email, $this->enquiryId);
        return $email;
    }

    /**
     * Return the products data associated with the quotation.
     *
     * @param   array  Return the quotation products data.
     */
    public function getQuotationData()
    {
        global $wpdb;
        $quotationTbl = getQuotationProductsTable();
        // Get data of Quotation (Updated price and Quantity)
        $quotation = $wpdb->get_results($wpdb->prepare("SELECT * FROM $quotationTbl WHERE enquiry_id = %d ORDER BY variation_index_in_enquiry ASC", $this->enquiryId), ARRAY_A);

        return $quotation;
    }
}
