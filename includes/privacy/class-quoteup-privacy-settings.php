<?php
namespace Includes\Admin\Privacy;

/**
 * Privacy related functionality which ties into WordPress functionality.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * WC_Privacy Class.
 */
class QuoteupPrivacySettings
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
        add_action('admin_init', array($this, 'registerSetting'));
        add_filter('quoteup_settings_tab', array($this, 'addPrivacyTab'), 10, 1);
        add_action('quoteup_add_product_enquiry_tab_content', array($this, 'addPrivacyTabContent'));
    }

    /**
    * Register the setting having some handle
    */
    public function registerSetting()
    {
        register_setting('wdm_form_options', 'wdm_privacy_anonymize_data', array('sanitize_callback' => array('\Includes\Admin\Privacy\QuoteupPrivacySettings', 'quoteupSanitizePrivacySettings')));
    }

    public function addPrivacyTab($settingsTabs)
    {
        $settingsTabs['wdm_privacy'] = array(
            'name' => __('Privacy', QUOTEUP_TEXT_DOMAIN),
            'id' => 'privacy-settings',
            'class' => 'tab nav-tab'
            );

        return $settingsTabs;
    }

    public function addPrivacyTabContent($form_data)
    {
        ?>
        <div id='wdm_privacy'>
            <?php
            $this->displayTermsConditionsSection($form_data);
            $this->displayCookieSettingsSection($form_data);
            do_action('quoteup_after_cookie_setting_section', $form_data);
            global $wp_version;
            if (version_compare($wp_version, '4.9.6', '<')) {
                ?>
                <div class="overlay-parent">
                    <div class="overlay">Available on WordPress 4.9.6 and Above</div>
                <?php
            }

            $this->displayErasureRequest($form_data);
            $this->displayFileUploadsSection($form_data);
            do_action('quoteup_before_enquiry_anonymize_fields', $form_data);
            $this->displayEnquiryFields();
            if (version_compare($wp_version, '4.9.6', '<')) {
                ?>
                </div>
                <?php
            }
            do_action('quoteup_privacy_settings', $form_data);
            ?>
        </div>
        <?php
    }

    /**
     * Quoteup new settings for the privacy fields
     * @param array $privacyAnonymizeSettings new value of privacy fields
     */
    public static function quoteupSanitizePrivacySettings($privacyAnonymizeSettings, $processEnqDetail = true)
    {
       // get all enquiry fields
        $allEnquiryFields = QuoteupPrivacy::quoteupAllFieldsToAnonymize();

        if (true === $processEnqDetail) {
            // process primary data
            if (isset($privacyAnonymizeSettings['enquiry-detail'])) {
                // remove those fields which are not selected
                $privacyAnonymizeSettings['enquiry-detail'] = array_filter($privacyAnonymizeSettings['enquiry-detail']);
                if (!empty($privacyAnonymizeSettings['enquiry-detail'])) {
                    // replace array value with data type
                    $privacyAnonymizeSettings['enquiry-detail'] = array_intersect_key($allEnquiryFields['enquiry-detail'], $privacyAnonymizeSettings['enquiry-detail']);
                }
            } else {
                $privacyAnonymizeSettings['enquiry-detail'] = array();
            }
        }

        // process meta data
        if (isset($privacyAnonymizeSettings['enquiry-meta'])) {
            // remove those fields which are not selected
            $privacyAnonymizeSettings['enquiry-meta'] = array_filter($privacyAnonymizeSettings['enquiry-meta']);
            if (!empty($privacyAnonymizeSettings['enquiry-meta'])) {
                // replace array value with data type
                $privacyAnonymizeSettings['enquiry-meta'] = array_intersect_key($allEnquiryFields['enquiry-meta'], $privacyAnonymizeSettings['enquiry-meta']);
            }
        } else {
            $privacyAnonymizeSettings['enquiry-meta'] = array();
        }
        return $privacyAnonymizeSettings;
    }

    /**
     * This function is used to display enquiry fields checkboxes to select to be
     * anonymized
     */
    public function displayEnquiryFields()
    {
        $enquiryDataFields = QuoteupPrivacy::quoteupAllFieldsToAnonymize();
        $selectedFields = get_option("wdm_privacy_anonymize_data", false);

        ?>
        <fieldset class="anonymize-form-fields-fieldset">
            <legend><?php echo __('Anonymize Form Fields', QUOTEUP_TEXT_DOMAIN) ?><?php echo \quoteupHelpTip(__('Anonymization is a process of removing personal information from the data. Fields which are going to contain personal information should be ticked below to remove data associated with them during <br>1. \'Complete Personal Data Erasure\' process<br>2. \'Order Specific Personal Data Erasure\' process (applicable when Quotation System is enabled)<br>3. \'Enquiry/Quote Specific Personal Data Erasure\' process.', 'quoteup'), true); ?></legend>
            <?php
            $common_fields_checkboxes = $default_form_fields_checkboxes = $custom_form_fields_checkboxes = '';
            // loop through all enqiury fields stored in $enquiryDataFields
            foreach ($enquiryDataFields['enquiry-detail'] as $key => $value) {
                if (quoteupIsCommonField($key)) {
                    //These Fields are common for Default as well as Custom Form
                    $common_fields_checkboxes .= $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-detail'][$key]), true, false);
                } else {
                    // These Fields are only available in Default Form
                    $default_form_fields_checkboxes .= $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-detail'][$key]), true, false);
                }
            }

            if (isset($enquiryDataFields['enquiry-meta'])) {
                foreach ($enquiryDataFields['enquiry-meta'] as $key => $value) {
                    // These are Custom Form Fields and Programmatically created Custom Fields
                    $custom_form_fields_checkboxes .= $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-meta'][$key]), false, false);
                }
            }

            $this->displayFieldsInAccordion($common_fields_checkboxes, $default_form_fields_checkboxes, $custom_form_fields_checkboxes);
            ?>
        </fieldset>
        <?php
    }

    public function displayFieldsInAccordion($common_fields_checkboxes, $default_form_fields_checkboxes, $custom_form_fields_checkboxes)
    {
        ?>
        <div class="anonymize-form-fields-accordion">
            <?php if (!empty($common_fields_checkboxes)) : ?>
                <h3 class="wdm-accordion"> <?php _e('Common Fields', QUOTEUP_TEXT_DOMAIN); ?> </h3>
                <div class="wdm-accordion-panel"><?php echo $common_fields_checkboxes; ?></div>
            <?php endif; ?>
            <?php if (!empty($default_form_fields_checkboxes)) : ?>
                <h3 class="wdm-accordion"> <?php _e('Default Form Fields', QUOTEUP_TEXT_DOMAIN); ?> </h3>
                <div class="wdm-accordion-panel"><?php echo $default_form_fields_checkboxes; ?></div>
            <?php endif; ?>
            <?php if (!empty($custom_form_fields_checkboxes)) : ?>
                <h3 class="wdm-accordion"> <?php _e('Custom Form Fields', QUOTEUP_TEXT_DOMAIN); ?> </h3>
                <div class="wdm-accordion-panel"><?php echo $custom_form_fields_checkboxes; ?></div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * This function is used to display the checkbox setting
     * @param [array] $enquiryField Enquiry field and value whether it has been
     *                              checked or not
     */
    public function renderCheckboxSettings($enquiryField, $isSelected, $enquiryTable = false, $echo = true)
    {
        if ($echo != true) {
            ob_start();
        }
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="primary_<?php echo esc_html($enquiryField); ?>">
                    <?php
                    if ($enquiryTable) {
                        echo esc_html(quoteupAnonymizeFieldsLabel($enquiryField));
                    } else {
                        echo esc_html($enquiryField);
                    }
                    ?>
                </label>

            </div>
            <div class='right_div'>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, $isSelected); ?> id="primary_<?php echo esc_html($enquiryField); ?>" />
                <?php
                if ($enquiryTable) {
                    ?>
                    <input type="hidden" name="wdm_privacy_anonymize_data[enquiry-detail][<?php echo esc_html($enquiryField); ?>]" value="<?php echo $isSelected; ?>" />
                    <?php
                } else {
                    ?>
                    <input type="hidden" name="wdm_privacy_anonymize_data[enquiry-meta][<?php echo esc_html($enquiryField); ?>]" value="<?php echo $isSelected; ?>" />
                    <?php
                }
                ?>
            </div>
            <div class="clear"></div>
        </div>
        <?php
        if ($echo != true) {
            return ob_get_clean();
        }
    }

    /**
     * This function is used to display terms and conditions options section in
     * settings
     * @param [array] $form_data [Settings stored previously in database]
     */
    public function displayTermsConditionsSection($form_data)
    {
        ?>
        <fieldset>
            <legend><?php echo __('Enquiry Terms and Conditions', QUOTEUP_TEXT_DOMAIN) ?></legend>
            <?php
            $this->renderTermsConditions($form_data);
            $this->renderEnquiryPrivacyPolicy($form_data);
            ?>
        </fieldset>
        <?php
    }

    public function displayErasureRequest($form_data)
    {
        ?>
        <fieldset>
                <legend><?php echo __('Account Erasure Request', QUOTEUP_TEXT_DOMAIN) ?></legend>
                <?php
                $this->renderEnquiryErasureRequest($form_data);
                $this->renderOrderErasureRequest($form_data);
                ?>
        </fieldset>
        <?php
    }

    public function displayFileUploadsSection($form_data)
    {
        ?>
        <fieldset>
                <legend><?php echo __('File Uploads', QUOTEUP_TEXT_DOMAIN) ?></legend>
                <?php $this->renderDeleteUploadedFiles($form_data); ?>
        </fieldset>
        <?php
    }

    public function renderEnquiryErasureRequest($form_data)
    {
        $selectedFields = get_option("wdm_privacy_anonymize_data", false);
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="delete_enquiry_data_during_anonymization">
                    <?php _e("Remove Personal data from Enquiry/quotes during personal data erasure", QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('While handling an account erasure request, should personal data within enquiry/quotes be retained or removed.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($selectedFields[ 'delete_enquiry_data_during_anonymization' ]) ? $selectedFields[ 'delete_enquiry_data_during_anonymization' ] : 0); ?> id="delete_enquiry_data_during_anonymization" />
                <input type="hidden" name="wdm_privacy_anonymize_data[delete_enquiry_data_during_anonymization]" value="<?php echo isset($selectedFields[ 'delete_enquiry_data_during_anonymization' ]) && $selectedFields[ 'delete_enquiry_data_during_anonymization' ] == 1 ? $selectedFields[ 'delete_enquiry_data_during_anonymization' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
        unset($form_data);
    }

    public function renderOrderErasureRequest($form_data)
    {
        $selectedFields = get_option("wdm_privacy_anonymize_data", false);
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="delete_enquiry_data_during_order_anonymization">
                    <?php _e("Remove personal data from enquiry/quotes when order is anonymized", QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('While handling an order erasure request, should personal data within enquiry/quotes be retained or removed (applicable when Quotation System is enabled).', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($selectedFields[ 'delete_enquiry_data_during_order_anonymization' ]) ? $selectedFields[ 'delete_enquiry_data_during_order_anonymization' ] : 0); ?> id="delete_enquiry_data_during_order_anonymization" />
                <input type="hidden" name="wdm_privacy_anonymize_data[delete_enquiry_data_during_order_anonymization]" value="<?php echo isset($selectedFields[ 'delete_enquiry_data_during_order_anonymization' ]) && $selectedFields[ 'delete_enquiry_data_during_order_anonymization' ] == 1 ? $selectedFields[ 'delete_enquiry_data_during_order_anonymization' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
        unset($form_data);
    }

    public function renderDeleteUploadedFiles($form_data)
    {
        $selectedFields = get_option("wdm_privacy_anonymize_data", false);
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="delete_file_uploads_during_anonymization">
                    <?php _e("Delete Uploaded Files During Anonymization", QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('Anonymization is the process of removing personal data. If files attached by your customers during enquiry contain personal information, then check this field to remove those files during `Personal Data Removal` process.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($selectedFields[ 'delete_file_uploads_during_anonymization' ]) ? $selectedFields[ 'delete_file_uploads_during_anonymization' ] : 0); ?> id="delete_file_uploads_during_anonymization" />
                <input type="hidden" name="wdm_privacy_anonymize_data[delete_file_uploads_during_anonymization]" value="<?php echo isset($selectedFields[ 'delete_file_uploads_during_anonymization' ]) && $selectedFields[ 'delete_file_uploads_during_anonymization' ] == 1 ? $selectedFields[ 'delete_file_uploads_during_anonymization' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
        unset($form_data);
    }
    /**
     * This function will display checkbox to enable and disable terms and
     * conditions on enquiry form
     * @param [array] $form_data [Settings stored previously in database]
     */
    public function renderTermsConditions($form_data)
    {
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="enable_terms_conditions">
                    <?php _e("Display Terms and Conditions", QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('To display \'Terms and Conditions\' checkbox on Enquiry or Quote request form, you must enable this.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_terms_conditions' ]) ? $form_data[ 'enable_terms_conditions' ] : 0); ?> id="enable_terms_conditions" />
                <input type="hidden" name="wdm_form_data[enable_terms_conditions]" value="<?php echo isset($form_data[ 'enable_terms_conditions' ]) && $form_data[ 'enable_terms_conditions' ] == 1 ? $form_data[ 'enable_terms_conditions' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }


    public function renderEnquiryPrivacyPolicy($form_data)
    {
        $enquiryPrivacyPolicy = isset($form_data['enquiry_privacy_policy_text']) ? esc_html($form_data['enquiry_privacy_policy_text']) : defaultTermsCondFieldText();
        ?>
        <div class='fd enquiry-privacy-policy'>
            <div class='left_div'>
                <label for="enquiry-privacy-policy-text">
                    <?php _e('Enquiry Privacy Policy', QUOTEUP_TEXT_DOMAIN) ?>
                </label>
            </div>
            <div class='right_div textarea-wrapper privacy-policy-textarea-wrapper'>
                <?php
                $helptip = sprintf(__('For WP 4.9.6 and above %s will get automatically replaced with Privacy Policy Page link. If your site runs on WP below 4.9.6, then replace %s here with link to your Privacy Policy Page.', QUOTEUP_TEXT_DOMAIN), '[privacy_policy]', '[privacy_policy]');
                // $helptip = __('For WP 4.9.6 and above [privacy_policy] will get automatically replaced with Privacy Policy Page link. If your site runs on WP below 4.9.6, then replace [privacy_policy] here with link to your Privacy Policy Page.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <textarea name="wdm_form_data[enquiry_privacy_policy_text]" id="enquiry-privacy-policy-text" class="no_wc_help_tip" rows="5" cols="54"><?php echo $enquiryPrivacyPolicy ?></textarea>
            </div>
        </div>
        <?php
    }

    // Cookies settings
    /**
     * This function is used to display section related to cookies settings
     * @param [array] $form_data [Settings stored previously in database]
     */
    public function displayCookieSettingsSection($form_data)
    {
        ?>
        <fieldset>
            <legend><?php echo __('Cookies Consent Settings', QUOTEUP_TEXT_DOMAIN) ?></legend>
            <?php
            $this->renderCookieConsentCheckbox($form_data);
            $this->renderCookieTextSetting($form_data);
            ?>
        </fieldset>
        <?php
    }

     /**
     * This function will display checkbox to enable and disable cookie consent
     * in the enquiry form
     * @param [array] $form_data [Settings stored previously in database]
     */
    public function renderCookieConsentCheckbox($form_data)
    {
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="enable_cookie_consent">
                    <?php _e("Display Cookie Consent Text", QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('To display \'Cookie Consent Text\' on Enquiry or Quote request form, you must enable this. This Consent is displayed only for logged out users', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'enable_cookie_consent' ]) ? $form_data[ 'enable_cookie_consent' ] : 0); ?> id="enable_cookie_consent" />
                <input type="hidden" name="wdm_form_data[enable_cookie_consent]" value="<?php echo isset($form_data[ 'enable_cookie_consent' ]) && $form_data[ 'enable_cookie_consent' ] == 1 ? $form_data[ 'enable_cookie_consent' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function displays text field which will be shown in the enquiry
     * form
     * @param [array] $form_data [Settings stored previously in database]
     */
    public function renderCookieTextSetting($form_data)
    {
        $cookieConsentText = isset($form_data['cookie_consent_text']) ? esc_html($form_data['cookie_consent_text']) : __('Save my Name and Email in this browser for my next Enquiry/Quote Request. (Needs page refresh after submitting a form to take effect)', QUOTEUP_TEXT_DOMAIN);
        ?>
        <div class='fd cookie-consent-text'>
            <div class='left_div'>
                <label for="cookie-consent-text">
                    <?php _e('Cookie Consent Text', QUOTEUP_TEXT_DOMAIN) ?>
                </label>
            </div>
            <div class='right_div textarea-wrapper cookie-consent-text-wrapper'>
                <?php
                $helptip = __('This text will be displayed in the enquiry form to ask for the consent', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <textarea name="wdm_form_data[cookie_consent_text]" id="cookie-consent-text" class="wdm_wpi_input wdm_wpi_textarea" rows="5" cols="54" ><?php echo $cookieConsentText ?></textarea>
            </div>
        </div>
        <?php
    }
}

QuoteupPrivacySettings::getInstance();
