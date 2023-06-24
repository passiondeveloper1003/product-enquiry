<?php

namespace Includes\MailChimp\Frontend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * MailChimp Checkbox
 *
 * Render the MailChimp subscription checkbox on the enquiry form.
 * Process the subscription when the enquiry form is submitted.
 *
 * @since 6.5.0
 */
class QuoteupMailChimpCheckbox
{
    /**
     * The reference to *Singleton* instance of this class.
     *
     * @var Singleton
     */
    private static $instance;
   
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }

    /**
     * Add the functions to the hooks.
     */
    private function __construct()
    {
        if (!quoteupIsMailChimpSubscriptionEnabled()) {
            return;
        }

        // Hooks to render subscription checkbox on the PEP enquiry forms.
        add_action('quoteup_after_all_form_fields', array($this, 'renderSubscriptionCheckboxHTML'), 15); // Default Form > SPE mode
        add_action('quoteup_after_fields_in_form', array($this, 'renderSubscriptionCheckboxHTML'), 11); // Default Form > MPE mode
        add_filter('quoteup_wisdmform_alter_send_copy', array($this, 'renderSubscriptionCheckboxHTMLOnCF'), 30, 2); // Custom Form > SPE and MPE modes

        // Add the user to MailChimp service.
        add_action('quoteup_add_custom_field_in_db', array($this, 'addUserToSubscriptionList'), 10, 2);
        // Add the first name, last name in the MailChimp API Request data.
        add_filter('quoteup_mailchimp_api_req_data', array($this, 'addFirstLastName'), 10, 2);
    }

    /**
     * Add email address in the MailChimp subscription list.
     *
     * Callback for 'quoteup_add_custom_field_in_db' action hook.
     *
     * @since 6.5.0
     */
    public function addUserToSubscriptionList($enquiryId, $data)
    {
        // Check if subscription checkbox is selected.
        if (!isset($data['mc-subscription-checkbox']) || "false" == $data['mc-subscription-checkbox']) {
            return;
        }

        if (!class_exists('\Includes\MailChimp\API\QuoteupMailChimpAPI')) {
            require_once QUOTEUP_PLUGIN_DIR .'/includes/mailchimp/api/class-quoteup-mailchimp-api.php';
        }

        $email = isset($data['txtemail']) ? trim(sanitize_email($data['txtemail'])) : '';
        if (empty($email)) {
            return;
        }

        $shouldAddUpdateUser = true;
        $apiKey              = mailchimp_get_api_key();
        $listId              = mailchimp_get_list_id();
        $quoteupMailChimpAPI = \Includes\MailChimp\API\QuoteupMailChimpAPI::getInstance($apiKey);

        // Get the user details if exists.
        $response = $quoteupMailChimpAPI->member($listId, $email);
      
        /**
         * Filter to determine whether the user data should be added/ updated in the MailChimp service.
         *
         * @since 6.5.0
         *
         * @param  bool  $shouldAddUpdateUser  Default true.
         * @param  array|null  $response  API response.
         * @param  string  $email  User email address.
         */
        $shouldAddUpdateUser = apply_filters('quoteup_should_update_mc_user_data', $shouldAddUpdateUser, $response, $email, $data, $enquiryId);

        if ($shouldAddUpdateUser) {
            $status = 'subscribed';
            if (!empty($response) && isset($response['status']) && 'pending' == $response['status']) {
                $status = 'pending';
            }

            $mcRequestData = array(
                'list_id'        => $listId,
                'email'          => $email,
                'status'         => $status,
                'merge_fields'   => array(),
                'list_interests' => array(),
                'language'       => null,
            );
        
            /**
             * Filter to modify the MailChimp API Request data.
             *
             * @since 6.5.0
             *
             * @param  string  $mcRequestData  API Request data.
             */
            $mcRequestData = apply_filters('quoteup_mailchimp_api_req_data', $mcRequestData, $data, $enquiryId);

            $response = $quoteupMailChimpAPI->updateOrCreate($mcRequestData['list_id'], $mcRequestData['email'], $mcRequestData['status'], $mcRequestData['merge_fields'], $mcRequestData['list_interests'], $mcRequestData['language']);
            /**
             * After adding/ updating the user to MailChimp service using API.
             *
             * @since 6.5.0
             *
             * @param  array  $response  API Reponse from the MailChimp service.
             * @param  string  $email  User email address.
             * @param  int  $enquiryId  Enquiry Id.
             * @param  array  $data  Array containing the submitted enquiry form
             *                       data.
             */
            do_action('quoteup_after_add_update_mc_user', $response, $email, $data, $enquiryId);
        }
    }
    
    /**
     * Render the HTML for subscription box.
     *
     * @since 6.5.0
     *
     * @return  void
     */
    public function renderSubscriptionCheckboxHTML()
    {
        $this->enqueueScriptsStyles();

        if (quoteupIsMPEEnabled()) {
            $wrapperClass = 'mc-subscription-field-wrapper mpe_form_input form-group';
            $fieldClass   = 'mc-subscription-field mpe-field mpe-right';
        } else {
            $wrapperClass = 'mc-subscription-field-wrapper spe_form_input form-group';
            $fieldClass   = 'mc-subscription-field spe-field';
        }

        $checkboxNameAttr = quoteupIsCustomFormEnabled() ? 'submitform[mc-subscription-checkbox]' : 'mc-subscription-checkbox';

        /**
         * Use the filter to add/ modify the class attributes for the subscription wrapper element.
         *
         * @since 6.5.0
         *
         * @param  string  $wrapperClass  Class attribute.
         */
        $wrapperClass = apply_filters('quoteup_mc_subs_field_wrapper_class', $wrapperClass);
        ?>
        <div class="<?php echo esc_attr($wrapperClass); ?>">
            <?php
            if (quoteupIsMPEEnabled() && !quoteupIsCustomFormEnabled()):
                ?>
                <div class="mpe-left" style="height: 1px;">
                </div>
                <?php
            endif;
            ?>
            <div class="<?php echo esc_attr($fieldClass); ?>">
                <label>
                <input type="checkbox" name="<?php echo esc_attr($checkboxNameAttr); ?>" id="quoteup-mc-subscription-checkbox" value="1" class="quoteup-mc-subscription-checkbox" />
                    <span> <?php esc_html_e(quoteupGetMailChimpCBLabel(), QUOTEUP_TEXT_DOMAIN); ?> </span>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Render the subscription checkbox on the custom form.
     *
     * Callback for action 'quoteup_wisdmform_alter_send_copy'.
     *
     * @since 6.5.0
     *
     * @return  void
     */
    public function renderSubscriptionCheckboxHTMLOnCF($sendCopy, $isQuoteCreatePage)
    {
        // Return if current page is backend quote creation page
        if ($isQuoteCreatePage) {
            return $sendCopy;
        }

        ob_start();
        $this->renderSubscriptionCheckboxHTML();
        $subscriptionCheckboxHTML = ob_get_clean();

        $sendCopy .= $subscriptionCheckboxHTML;
        return $sendCopy;
    }
    
    /**
     * Add the first name, last name in the MailChimp API Request data.
     *
     * Callback for 'quoteup_mailchimp_api_req_data' filter hook.
     *
     * @since 6.5.0
     */
    public function addFirstLastName($mcRequestData, $data)
    {
        $custName = isset($data['custname']) ? trim(sanitize_text_field($data['custname'])) : '';
		$strpos   = strpos($custName, ' ');

        if (false !== $strpos) {
            $mcRequestData['merge_fields']['FNAME'] = trim(substr($custName, 0, $strpos));
            $mcRequestData['merge_fields']['LNAME'] = trim(substr($custName, $strpos));
        } else {
            $mcRequestData['merge_fields']['FNAME'] = $custName;
        }
        
        return $mcRequestData;
    }
    /**
     * Enqueue the scripts and styles required on frontend.
     *
     * @since 6.5.0
     *
     * @return void
     */
    public function enqueueScriptsStyles()
    {
        wp_enqueue_style('quoteup-mailchimp-css', QUOTEUP_PLUGIN_URL . '/css/mailchimp/quoteup-mailchimp.css', array(), filemtime(QUOTEUP_PLUGIN_DIR . '/css/mailchimp/quoteup-mailchimp.css'));
    }
}

QuoteupMailChimpCheckbox::getInstance();
