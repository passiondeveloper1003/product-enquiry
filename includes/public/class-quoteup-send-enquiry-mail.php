<?php

namespace Includes\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

WC()->mailer();
/**
 * This class is used to send mail to customer.
 * Mail includes pdf and the unique link by which customer can approve or reject quote.
 *
 * @static instance Object of class
 * @var    $enquiryID Enquiry Id
 * @var    $source source of email
 */
class SendEnquiryMail extends \WC_Email
{
    private static $instance;
    private $enquiryID;
    private $source;
    protected $data;

    /**
     * This function sends the Singleton emailObject.
     *
     * @param  int    $enquiryId   current enquiry id.
     * @param  int    $authorEmail Author Email
     * @param  string $subject     subject to be sent in email.
     * @return static instance of Email Object
     */
    public static function getInstance($enquiryID, $authorEmail, $subject, $data)
    {
        if (null === static::$instance) {
            static::$instance = new static($enquiryID, $authorEmail, $subject, $data);
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
    public function __construct($enquiryID, $authorEmail, $subject, $data)
    {
        add_filter('woocommerce_email_styles', array($this,'addCSS'), 10, 1);
        $this->enquiryID = $enquiryID;
        $this->data = $data;
        $admin_emails = array();

        $email_data = quoteupSettings();
        if ($email_data[ 'user_email' ] != '') {
            $admin_emails = explode(',', $email_data[ 'user_email' ]);
        }
        $admin_emails = array_map('trim', $admin_emails);

        //Send email to admin only if 'Send mail to Admin' settings is checked
        $admin_emails = $this->addAdminMail($email_data, $admin_emails);

        //Send email to author only if 'Send mail to Author' settings is checked
        $admin_emails = $this->addAuthorMail($email_data, $authorEmail, $admin_emails);

        $admin_emails = array_unique($admin_emails);

        $wdm_sitename = '['.trim(wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)).'] ';
        $subject = $this->getMailSubject($subject, $email_data, $wdm_sitename);

        $this->email_type = 'text/html';

        $this->heading = __('Enquiry/Quote', QUOTEUP_TEXT_DOMAIN);
        $this->subject = $subject;

        // Triggers for this email
        add_action('quoteup_send_enquiry_email', array( $this, 'trigger' ), 15, 1);

        parent::__construct();

        $this->recipient = $admin_emails;
    }

     /**
      * This returns the CSS for Email.
      *
      * @return string $css css for email
      */
    public function addCSS($css)
    {
        $stylesheet = file_get_contents(QUOTEUP_PLUGIN_DIR.'/css/public/enquiry-mail.css');
        return $css.$stylesheet;
    }

    /**
     * Sends the email after getting all the required details.
     */
    public function trigger()
    {
        $this->source = 'admin';
        $subject = $this->get_subject();
        $message = $this->get_content();
        $headers = $this->get_admin_headers();
        $attachments = $this->get_attachments();
        $this->recipient = apply_filters('quoteup_update_admin_mail_recipient', $this->recipient, $this->data);
        if (!empty($this->recipient)) {
            $this->send($this->recipient, $subject, $message, $headers, $attachments);
        }

        // Send Email to authors if vendor compatibility is enabled.
        $this->sendEmailtoAuthorsForProducts($subject, $headers, $attachments);

        $cc = apply_filters('quoteup_change_send_copy', isset($this->data['cc'])? $this->data['cc'] : '');
        if ($cc == 'checked') {
            $this->source = 'cc';
            $message = $this->get_content();
            $headers = "Reply-to: " . get_option('admin_email') . "\r\n";
            $headers =apply_filters('quoteup_send_copy_mail_header', $headers);
            $this->recipient = filter_var($this->data[ 'txtemail' ], FILTER_SANITIZE_EMAIL);
            $this->recipient = apply_filters('quoteup_update_customer_mail_recipient', $this->recipient, $this->data);
            
            $this->send($this->recipient, $subject, $message, $headers, $attachments);
        }
    }

     /**
      * Gets the content in HTML for the mail to be sent.
      * Template for customer details.
      * Gets the Products for the enquiry from database.
      *
      * @return HTML $message content of email
      */
    public function get_content_html()
    {
        $optionData = quoteupSettings();
        $customerName = $this->data['custname'];
        $data_obtained_from_form = $this->data;
        $form_data_for_mail = json_encode($data_obtained_from_form);
        $enable_price_flag = false;
        ob_start();
        $args = array(
            'enquiryId'  => $this->enquiryID,
            'optionData' => $optionData,
            'customerName' => $customerName,
            'form_data_for_mail' => $form_data_for_mail,
            'enable_price_flag' => $enable_price_flag,
            'data_obtained_from_form' => $data_obtained_from_form,
            'source' => $this->source,
            'form_data' => $optionData,
            'emailObject' => $this,
            'author_email' => '',
        );

        $args = apply_filters('quoteup_enquiry_mail_args', $args);

        //This loads the template for Enquiry Mail
        quoteupGetPublicTemplatePart('enquiry-mail/enquiry-mail', '', $args);

        /**
         * This action is documented in /product-enquiry-pro/includes/admin/class-quoteup-create-quotation-from-dashboard.php.
         */
        do_action('quoteup_reset_lang');
        $message = ob_get_clean();
        $email_data = quoteupSettings();

        /**
         * Use this filter to decide whether to include the company logo in an
         * enquiry email or not.
         *
         * @since 6.4.4
         *
         * @param   bool    Boolean value indicating whether to include the company logo in enquiry email.
         *
         */
        $should_add_logo = apply_filters('quoteup_should_add_comp_logo_in_enq_email', true);
        if (isset($email_data['company_logo']) && !empty($email_data['company_logo']) && $should_add_logo) {
            $img = '<div id="template_header_image"><p style="margin-top:0;"><img src="' . esc_url($email_data['company_logo']) . '" alt="' . esc_attr(get_bloginfo('name', 'display')) . '" /></p></div>';

            $message = preg_replace('/<div id="template_header_image">[\s\S]*?(<\/div>)/', $img, $message);
        }

        return wp_specialchars_decode($message, ENT_QUOTES);
    }

    /**
     * Get admin headers for admin email
     *
     * @access public
     * @return string email headers
     */
    public function get_admin_headers()
    {
        $email = filter_var($this->data[ 'txtemail' ], FILTER_SANITIZE_EMAIL);
        $header = "Reply-to: " . $email . "\r\n";
        return apply_filters('quoteup_admin_mail_headers', $header);
    }

    /**
     * Gets the generated pdf from the uploads directory to set in the attachments for mail.
     *
     * @return array $attachments
     */
    public function get_attachments()
    {
        $upload_dir = wp_upload_dir();
        $path = $upload_dir[ 'basedir' ].'/QuoteUp_Files/';
        $attachments = array();
        if (file_exists($path.$this->enquiryID)) {
            $folder_path = $path.$this->enquiryID.'/';
            $files = scandir($path.$this->enquiryID);
            foreach ($files as $file) {
                if ($file == '.' || $file == "..") {
                    continue;
                }
                array_push($attachments, $folder_path.$file);
            }
        }
        return $attachments;
    }

    /**
     * This function is used to add author mail in admin_emails.
     *
     * @param [array] $email_data   [Settings stored in database]
     * @param [array] $admin_emails [Total mail ids on which mail needs to be sent]
     */
    private function addAdminMail($email_data, $admin_emails)
    {
        if (isset($email_data[ 'send_mail_to_admin' ]) && $email_data[ 'send_mail_to_admin' ] == 1) {
            $admin = get_option('admin_email');
            if (!in_array($admin, $admin_emails)) {
                $admin_emails[] = $admin;
            }
        }

        return $admin_emails;
    }

    /**
     * Send Email to authors only for their products if multi-product enquiry is enabled and
     * customer has made an enquiry for products of different authors.
     */
    function sendEmailtoAuthorsForProducts($subject, $headers, $attachments)
    {
        global $quoteup;
        $quoteupSettings = get_option('wdm_form_data');
       
        // Check if vendor compatibility setting is enabled.
        if (!quoteupIsVendorCompEnabled()) {
            return;
        }

        if (quoteupIsMPEEnabled($quoteupSettings)) {
            $authorEmails   = array();
            $prod           = $quoteup->wcCartSession->get('wdm_product_info');
            foreach ($prod as $arr) {
                $email = $arr['author_email'];
                $authorEmails[] = $email;
            }
        } else {
            $dataObtainedFromForm = $this->data;
            $authorEmails[]       = $dataObtainedFromForm['uemail'];
        }

        $this->source   = 'author';
        $adminEmail     = get_option('admin_email');
        $authorEmails   = array_diff($authorEmails, array($adminEmail));
        $authorEmails   = array_unique($authorEmails);
        $subject        = apply_filters('quoteup_update_author_mail_subject', $subject);
        $headers        = apply_filters('quoteup_update_author_mail_header', $headers);
        add_filter('quoteup_enquiry_mail_args', array($this, 'addAuthorDataInEnquiryArgs'), 20);

        foreach ($authorEmails as $email) {
            $this->recipient    = $email;
            $this->recipient    = apply_filters('quoteup_update_author_mail_recipient', $this->recipient, $this->data);
            $message            = $this->get_content();
            $this->send($this->recipient, $subject, $message, $headers, $attachments);
        }
        remove_filter('quoteup_enquiry_mail_args', array($this, 'addAuthorDataInEnquiryArgs'), 20);
    }

    /**
     * Add author email and author product data in enquiry email args.
     */
    function addAuthorDataInEnquiryArgs($args)
    {
        $quoteupSettings = get_option('wdm_form_data');

        if (quoteupIsMPEEnabled($quoteupSettings)) {
            global $quoteup;
            $authorEmail = $this->recipient;
            $args['author_email'] = $authorEmail;
    
            $products = $quoteup->wcCartSession->get('wdm_product_info');
            foreach ($products as $product) {
                $email = $product['author_email'];
                
                if ($email == $authorEmail) {
                    $authorProducts[] = $product;
                }
            }
            $args['author_products'] = $authorProducts;
        }

        return $args;
    }

    /**
     * This function is used to add author mail in admin_emails.
     *
     * @param  [array]  $email_data   [Settings stored in database]
     * @param  [string] $authorEmail  [Author email id]
     * @param  [array]  $admin_emails [Total mail ids on which mail needs to be sent]
     * @return array $admin_emails Admin emails specified in backend
     */
    private function addAuthorMail($email_data, $authorEmail, $admin_emails)
    {
        if (isset($email_data['send_mail_to_author']) && $email_data['send_mail_to_author'] == 1 && !empty($authorEmail) && apply_filters('quoteup_merge_author_emails_in_admin_email', true, $email_data)) {
            $admin_emails = array_merge($admin_emails, $authorEmail);
        }

        return $admin_emails;
    }

    /**
     * This function is used to set default subject for mail if subject is blank
     * or set the subject entered by customer.
     *
     * @param [string] $subject      [subject for mail]
     * @param [array]  $email_data   [Seetings of Quoteup]
     * @param [string] $wdm_sitename [Site name]
     *
     * @return [string] $admin_subject admin subject for mail
     */
    private function getMailSubject($subject, $email_data, $wdm_sitename)
    {
        if ($subject == '') {
            $admin_subject = $wdm_sitename.$email_data[ 'default_sub' ];
            if ($email_data[ 'default_sub' ] == '') {
                $admin_subject = $wdm_sitename.__('Enquiry or Quote Request for Products from  ', QUOTEUP_TEXT_DOMAIN).get_bloginfo('name');
            }
        } else {
            $admin_subject = $wdm_sitename.$subject;
        }

        return apply_filters('quoteup_enquiry_mail_subject', $admin_subject);
    }

    /**
     * THis function is used to get variation string.
     *
     * @param  [array] $variation_detail [Variation details]
     * @param  int     $variation_id     Variation id
     * @return string $variationString string appended variation details
     */
    public static function getVatiationString($variation_detail, $variation_id)
    {
        $variationString = '';
        $variableProduct = wc_get_product($variation_id);
        $product_attributes = $variableProduct->get_attributes();
        foreach ($variation_detail as $attributeName => $attributeValue) {
            if (!empty($variationString)) {
                $variationString .= ',';
            }

            $taxonomy = wc_attribute_taxonomy_name(str_replace('pa_', '', urldecode($attributeName)));

            // If this is a term slug, get the term's nice name
            if (taxonomy_exists($taxonomy)) {
                $term = get_term_by('slug', $attributeValue, $taxonomy);
                if (!is_wp_error($term) && $term && $term->name) {
                    $attributeValue = $term->name;
                }
                $label = wc_attribute_label($taxonomy);

            // If this is a custom option slug, get the options name
            } else {
                $label = quoteupVariationAttributeLabel($variableProduct, $attributeName, $product_attributes);
            }

            $variationString .= '<b> '.$label.'</b> : '.$attributeValue;
        }

        return $variationString;
    }
}
