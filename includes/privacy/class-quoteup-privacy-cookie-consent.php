<?php
namespace Includes\Form\Privacy;

/**
 * Privacy Cookie Consent related functionality
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * QuoteupPrivacyCookieConsent Class.
 */
class QuoteupPrivacyCookieConsent
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
        // cookie consent field will be displayed after captcha field but
        // before terms and conditions field
        // quoteup_after_all_form_fields hook - default form and single product enquiry
        add_action('quoteup_after_all_form_fields', array($this, 'displayCookieConsentFieldSPEForm'), 20);
        // quoteup_after_fields_in_form hook - default form and multi product enquiry
        add_action('quoteup_after_fields_in_form', array($this, 'displayCookieConsentFieldMPEForm'), 12);
        // quoteup_wisdmform_alter_send_copy hook - custom form
        add_filter('quoteup_wisdmform_alter_send_copy', array($this, 'appendCookieConsentField'), 40, 2);

        if (QuoteupPrivacyCookieConsent::isCookieConsentFieldEnabled() && !is_user_logged_in()) {
            add_filter("quoteup_get_custom_field", array($this, 'defaultFormCCValidations'), 40);

            // when hook is fired and cookie consent field is checked, it is
            // stored in database in case of default form
            if (!quoteupIsCustomFormEnabled()) {
                add_action('quoteup_add_custom_field_in_db', array($this, 'addCookieConsentFieldInDb'), 50, 2);
            }
        }
    }

    public function defaultFormCCValidations($fields)
    {
        $fields[] = array(
            'id' => 'cookie-consent-cb',
            'class' => 'cc-checkbox',
            'type' => 'checkbox',
            'placeholder' => '',
            'required' => 'no',
            'required_message' => '',
            'validation' => '',
            'validation_message' => '',
            'include_in_admin_mail' => 'no',
            'include_in_customer_mail' => 'no',
            'include_in_quote_form' => 'no',
            'label' => 'Cookie Consent',
            'value' => 'yes'
        );

        return $fields;
    }


    /**
     * Returns or echo the HTML markup for Cookie Consent Field. If cookie field
     * is disabled, it returns false. When it echo the HTML markup, returns true.
     * Otherwise, returns the HTML markup as string if first parameter is set to
     * false.
     * @param   bool     $echo          Optional. True if you want to echo the
     *                                  Cookie Consent Field, false otherwise.
     *                                  Default true.
     * @param   string   $class         Optional. classname to add to Cookie
     *                                  Consent Field wrapper. Default ''.
     * @param   bool     $defaultForm   Optional. True if current form is
     *                                  'default' form, false otherwise
     *
     * @return  mixed                   Returns false, if Cookie field is
     *                                  disabled or returns HTML markup for
     *                                  Cookie field as string if first
     *                                  parameter is set to false or returns
     *                                  true and echo the Cookie field if Cookie
     *                                  field is enabled and first paramter is
     *                                  set to true.
     */
    public static function displayCookieConsentField($echo = true, $class = '', $defaultForm = true)
    {
        // check cookie consent field is enabled
        if (!QuoteupPrivacyCookieConsent::isCookieConsentFieldEnabled() || is_user_logged_in()) {
            return false;
        }

        $cookieConsentFieldText = QuoteupPrivacyCookieConsent::getCookieConsentFieldText();

        if (false === $echo) {
            ob_start();
        }
        ?>
        <div class="quoteup-cookie-consent-field-wrapper <?php echo $class; ?>">
            <label>
               <input type="checkbox" class="cc-checkbox" id="cookie-consent-cb" name="<?php
                echo (true === $defaultForm) ? 'cookie-consent-cb[]' : 'submitform[cookie consent]';
                ?>" value="yes" >
                <?php
                   echo $cookieConsentFieldText;
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
     * Displays the cookie consent field text with checkbox, if single product
     * enquiry and default form settings are enabled
     */
    public function displayCookieConsentFieldSPEForm()
    {
        QuoteupPrivacyCookieConsent::displayCookieConsentField(true, 'form_input');
    }
    
    /**
     * Displays the cookie consent field text with checkbox, if multi product
     * enquiry and default form settings are enabled
     */
    public function displayCookieConsentFieldMPEForm()
    {
        if (!QuoteupPrivacyCookieConsent::isCookieConsentFieldEnabled()  || is_user_logged_in()) {
            return;
        }
        ?>
        <div class="cookie-consent-field-mpe mpe_form_input">
            <div class="mpe-left" style="height: 1px;">
            </div>
            <?php
            QuoteupPrivacyCookieConsent::displayCookieConsentField(true, 'mpe-right');
            ?>
        </div>
        <?php
    }
    
    /**
     * Appends the cookie consent field text with checkbox to $sendCopy, if custom form is
     * activated irrespective of enquiry type
     */
    public function appendCookieConsentField($sendCopy, $isQuoteCreatePage)
    {
        // return if current page is quote create page
        if ($isQuoteCreatePage) {
            return $sendCopy;
        }

        $cookieConsentField = QuoteupPrivacyCookieConsent::displayCookieConsentField(false, 'form-group', false);
        if (false !== $cookieConsentField) {
            $sendCopy .= $cookieConsentField;
        }
        return $sendCopy;
    }

    /**
     * Save the 'cookie consent' as key and its value as meta value in enquiry meta
     * table which indicates that the customer has allowed to save 'Name' and 'Email'
     * in browser while submitting this particular enquiry
     *
     * @param   int     $enquiryID      enquiry id
     * @parma   array   $data           enquiry form data
     */
    public function addCookieConsentFieldInDb($enquiryID, $data)
    {
        if (!isset($data['cookie-consent-cb']) || empty($data['cookie-consent-cb'])) {
            return;
        }

        global $wpdb;
        $tbl = getEnquiryMetaTable();

        $wpdb->insert(
            $tbl,
            array(
            'enquiry_id' => $enquiryID,
            'meta_key' => 'cookie consent',
            'meta_value' => $data['cookie-consent-cb'],
            ),
            array(
            '%d',
            '%s',
            '%s',
            )
        );
    }

    /**
     * Get the cookie consent field text, if set, which will be displayed in the enquiry form.
     *
     * @return string
     */
    public static function getCookieConsentFieldText()
    {
        $settings = get_option('wdm_form_data');
        $text = isset($settings['cookie_consent_text']) && !(empty($settings['cookie_consent_text'])) ? quoteupReturnWPMLVariableStrTranslation($settings['cookie_consent_text']) : \Includes\Form\Privacy\QuoteupPrivacyCookieConsent::defaultCookieConsentFieldText();

        return trim(apply_filters('quoteup_get_cookie_consent_field_text', $text));
    }
    
    /**
     * Returns the default cookie consent field text.
     *
     * @return string default cookie consent field
     */
    public static function defaultCookieConsentFieldText()
    {
        $text = __('Save my Name and Email in this browser for my next Enquiry/Quote Request', QUOTEUP_TEXT_DOMAIN);
        return apply_filters('quoteup_default_cookie_consent_field_text', $text);
    }
    
    public static function isCookieConsentFieldEnabled($quoteupSettings = null)
    {
        if (empty($quoteupSettings)) {
            $quoteupSettings = get_option('wdm_form_data');
        }
        return isset($quoteupSettings['enable_cookie_consent']) ? $quoteupSettings['enable_cookie_consent'] : true;
    }
}

QuoteupPrivacyCookieConsent::getInstance();
