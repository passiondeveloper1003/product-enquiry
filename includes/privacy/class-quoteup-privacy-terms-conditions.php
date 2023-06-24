<?php
namespace Includes\Form\Privacy;

/**
 * Privacy related functionality which ties into WordPress functionality.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * QuoteupPrivacyTermsCond Class.
 */
class QuoteupPrivacyTermsCond
{
    /**
     * @var Singleton The reference to *Singleton* instance of this class
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

    // /**
    //  * Init - hook into events.
    //  */
    private function __construct()
    {
        // terms and conditions text will be displayed after cookie consent field but
        // before send button
        add_action('quoteup_after_all_form_fields', array($this, 'displayTermsCondTextSPEForm'), 30);
        add_action('quoteup_after_fields_in_form', array($this, 'displayTermsCondTextMPEForm'), 14);
        add_filter('quoteup_wisdmform_alter_send_copy', array($this, 'displayTermsCondTextCustomForm'), 50, 2);

        // when this hook runs, a text describing that 'customer has selected the
        // enquiry terms and conditions' is added in the email sent to the admin // and customer
        add_action('quoteup_after_product_table_enquiry_mail', array($this, 'addTermsCondTextInEmail'), 20, 2);

        if (QuoteupPrivacyTermsCond::isTermsCondEnabled()) {
            add_filter("quoteup_get_custom_field", array($this, 'defaultFormTCValidations'), 50);

            if (!quoteupIsCustomFormEnabled()) {
                add_action('quoteup_add_custom_field_in_db', array($this, 'addTermsCondFieldInDb'), 50, 2);
            }
        }
    }

    public function defaultFormTCValidations($fields)
    {
        $requiredMessage = __('Please select terms and conditions', QUOTEUP_TEXT_DOMAIN);
        $requiredMessage = apply_filters('quoteup_alter_required_message', $requiredMessage);

        $fields[] = array(
            'id' => 'term-cond-cc',
            'class' => 'tc-checkbox',
            'type' => 'checkbox',
            'placeholder' => '',
            'required' => 'yes',
            'required_message' => $requiredMessage,
            'validation' => '',
            'validation_message' => '',
            'include_in_admin_mail' => 'no',
            'include_in_customer_mail' => 'no',
            'include_in_quote_form' => 'no',
            'label' => 'Terms and Conditions',
            'value' => 'yes'
        );

        return $fields;
    }

    /**
     * Returns or echo the HTML markup for Terms and Conditions Field. If terms
     * and conditions field is disabled, it returns false.
     * When it echo the HTML markup, returns true. Otherwise, returns the HTML
     * markup as string if first parameter is set to false.
     *
     * @param bool   $echo        Optional. True if you want to echo the
     *                            Terms and Conditions field, false
     *                            otherwise. Default true.
     * @param string $class       Optional. classname to add to Terms and
     *                            Conditions Field wrapper. Default ''.
     * @param bool   $defaultForm Optional. True if current form is
     *                            'default' form, false otherwise.
     *
     * @return mixed                    Returns false, if Terms and Conditions
     *                                  field is disabled or returns HTML markup
     *                                  for Terms and Conditions field as string
     *                                  if first parameter is set to false or
     *                                  returns true and echo the Terms and
     *                                  Conditions field if field is enabled and
     *                                  first paramter is set to true.
     */
    public static function displayTermsConditions($echo = true, $class = '', $defaultForm = true)
    {
        if (!QuoteupPrivacyTermsCond::isTermsCondEnabled()) {
            return false;
        }

        $requiredMessage = __('Please select terms and conditions', QUOTEUP_TEXT_DOMAIN);
        $requiredMessage = apply_filters('quoteup_alter_required_message', $requiredMessage);

        $termsConditionsText = quoteupGetPrivacyPolicyText();

        if (false === $echo) {
            ob_start();
        }
        ?>
        <div class="quoteup-privacy-policy-wrapper <?php echo $class; ?>">
            <label>
                <input type="checkbox" class="tc-checkbox" id="term-cond-cc" name="<?php
                echo (true === $defaultForm) ? 'term-cond-cc[]' : 'submitform[terms and conditions]';
                ?>" value="yes" data-msg-required="<?php echo $requiredMessage; ?>" required="required">
                <?php
                    echo quoteupReplacePolicyPageLinkPlaceholders($termsConditionsText);
                ?>
            </label>
        </div>
        <?php
        if (false === $echo) {
            return ob_get_clean();
        }
        return true;
    }

    /**
     * Returns true if terms and conditions settings is enabled or not set, false otherwise
     *
     * @param array $quoteupSettings array of quoteup settings
     *
     * @return array bool true if terms and conditions settings is enabled or not set, false otherwise
     */
    public static function isTermsCondEnabled($quoteupSettings = null)
    {
        if (empty($quoteupSettings)) {
            $quoteupSettings = get_option('wdm_form_data');
        }
        return isset($quoteupSettings['enable_terms_conditions'])? $quoteupSettings['enable_terms_conditions'] : true;
    }

    /**
     * Appends the terms and condtions text with checkbox to $sendCopy, if custom form is
     * activated irrespective of enquiry type
     *
     * @param string $sendCopy HTML markup of 'Send Me Copy' field
     *                         displayed in the enquiry form
     *
     * @return string               Returns 'Terms and Conditions' field
     *                              Markup added to 'Send Me Copy' field markup
     *                              or 'Send Me Copy' field markup if privacy
     *                              policy page is not set or terms and
     *                              conditions is disabled
     */
    public function displayTermsCondTextCustomForm($sendCopy, $isQuoteCreatePage)
    {
        // return if current page is quote create page
        if ($isQuoteCreatePage) {
            return $sendCopy;
        }

        $termsCondText = QuoteupPrivacyTermsCond::displayTermsConditions(false, 'form-group', false);
        if (false === $termsCondText) {
            return $sendCopy;
        }
        $sendCopy .= $termsCondText;
        return $sendCopy;
    }

    /**
     * Displays the terms and condtions text with checkbox, if single product
     * enquiry and default form settings are active
     */
    public function displayTermsCondTextSPEForm()
    {
        QuoteupPrivacyTermsCond::displayTermsConditions(true, 'form_input');
    }

    /**
     * Displays the terms and condtions text with checkbox, if multi product
     * enquiry and default form settings are active
     */
    public function displayTermsCondTextMPEForm()
    {
        if (!QuoteupPrivacyTermsCond::isTermsCondEnabled()) {
            return;
        }
        ?>
        <div class="terms-cond-mpe mpe_form_input">
            <div class="mpe-left" style="height: 1px;">
            </div>
            <?php
            QuoteupPrivacyTermsCond::displayTermsConditions(true, 'mpe-right');
            ?>
        </div>
        <?php
    }
    
    /**
     * Save the 'terms and conditions' as key and its value as meta value in enquiry meta
     * table which indicates that the customer has selected the terms and conditions for
     * this particular enquiry.
     *
     * @param   int     $enquiryID      enquiry id
     * @parma   array   $data           enquiry form data
     */
    public function addTermsCondFieldInDb($enquiryID, $data)
    {
        if (!isset($data['term-cond-cc']) || empty($data['term-cond-cc'])) {
            return;
        }

        global $wpdb;
        $tbl = getEnquiryMetaTable();

        $wpdb->insert(
            $tbl,
            array(
            'enquiry_id' => $enquiryID,
            'meta_key' => 'terms and conditions',
            'meta_value' => $data['term-cond-cc'],
            ),
            array(
            '%d',
            '%s',
            '%s',
            )
        );
    }

    public function addTermsCondTextInEmail($form_data_for_mail, $source)
    {
        $form_data = json_decode($form_data_for_mail, true);

        if ((isset($form_data['term-cond-cc']) && 'yes' === $form_data['term-cond-cc']) || (isset($form_data['terms and conditions']) && 'yes' === $form_data['terms and conditions'])) {
            $customerEmail = $form_data['txtemail'];
            $customerEmail = apply_filters('quoteup_customer_email_in_tc_text', $customerEmail, $source);
            $tcText        = sprintf(__('%s accepted the enquiry terms and conditions.', QUOTEUP_TEXT_DOMAIN), $customerEmail);
            $tcText        = '<br /><br /><p>' . $tcText . '</p>';
            $tcText        = apply_filters('quoteup_modify_tc_text_in_email', $tcText, $customerEmail, $source, $form_data);

            echo esc_html($tcText);
        }
    }
}

QuoteupPrivacyTermsCond::getInstance();
