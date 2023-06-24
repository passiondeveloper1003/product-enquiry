<?php
namespace Includes\Admin\Vendor;

/**
 * Adds vendor related settings.
 */
defined('ABSPATH') || exit;

/**
 * QuoteupVendorSettings Class.
 */
class QuoteupVendorSettings
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
            static::$instance = new self();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action('admin_init', array($this, 'registerSetting'));
        add_filter('quoteup_settings_tab', array($this, 'addVendorTab'), 12, 1);
        add_action('quoteup_add_product_enquiry_tab_content', array($this, 'addVendorTabContent'));
    }

    /**
     * Register the setting having some handle.
     */
    public function registerSetting()
    {
        register_setting('wdm_form_options', 'wdm_vendor_visible_customer_data', array('sanitize_callback' => array('\Includes\Admin\Vendor\QuoteupVendorSettings', 'quoteupSanitizeVendorVisibleFields')));
    }

    /**
     * Sanitize Vendor visible form fields when customer saves the Quoteup
     * settings.
     * @param array $vendorVisibleFormFields New value of visible form fields.
     * @param bool  $processEnqDetail        True if primary fields and default fields
     *                                       should be processed, false otherwise.
     */
    public static function quoteupSanitizeVendorVisibleFields($vendorVisibleFormFields, $processEnqDetail = true)
    {
       // get all enquiry fields
        $allEnquiryFields = quoteupGetAllFormFields();

        if (true === $processEnqDetail) {
            // process primary data
            if (isset($vendorVisibleFormFields['enquiry-detail'])) {
                // remove those fields which are not selected
                $vendorVisibleFormFields['enquiry-detail'] = array_filter($vendorVisibleFormFields['enquiry-detail']);
                if (!empty($vendorVisibleFormFields['enquiry-detail'])) {
                    // replace array value with data type
                    $vendorVisibleFormFields['enquiry-detail'] = array_intersect_key($allEnquiryFields['enquiry-detail'], $vendorVisibleFormFields['enquiry-detail']);
                }
            } else {
                $vendorVisibleFormFields['enquiry-detail'] = array();
            }
        }

        // process meta data
        if (isset($vendorVisibleFormFields['enquiry-meta'])) {
            // remove those fields which are not selected
            $vendorVisibleFormFields['enquiry-meta'] = array_filter($vendorVisibleFormFields['enquiry-meta']);
            if (!empty($vendorVisibleFormFields['enquiry-meta'])) {
                // replace array value with data type
                $vendorVisibleFormFields['enquiry-meta'] = array_intersect_key($allEnquiryFields['enquiry-meta'], $vendorVisibleFormFields['enquiry-meta']);
            }
        } else {
            $vendorVisibleFormFields['enquiry-meta'] = array();
        }

        return $vendorVisibleFormFields;
    }

    /**
     * Add vendor tab in the PEP settings page.
     */
    public function addVendorTab($settingsTabs)
    {
        $settingsTabs['wdm_vendor'] = array(
            'name' => __('Vendor', QUOTEUP_TEXT_DOMAIN),
            'id' => 'vendor-settings',
            'class' => 'tab nav-tab'
        );

        return $settingsTabs;
    }

    /**
     * Add content inside the vendor tab.
     */
    public function addVendorTabContent($formData)
    {
        ?>
        <div id='wdm_vendor'>
            <?php
            $this->displayVendorOptionsSection($formData);

            do_action('quoteup_before_vendor_visible_form_fields', $formData);
            $this->displayEnquiryFields();

            do_action('quoteup_vendor_settings', $formData);
            ?>
        </div>
        <?php
    }

    /**
     * This function is used to display vendor options section inside
     * 'Vendor' tab
     * @param [array] $formData [Settings stored previously in database]
     */
    public function displayVendorOptionsSection($formData)
    {
        ?>
        <fieldset>
            <legend><?php echo __('Vendor Options', QUOTEUP_TEXT_DOMAIN) ?></legend>
            <?php
            $this->renderCompatibilitySetting($formData);
            $this->renderExportCapabilitySetting($formData);
            $this->renderSendQuotationSetting($formData);
            $this->renderEnableVendorReminderEmailSetting($formData);
            $this->renderEnableAdminReminderEmailSetting($formData);
            $this->renderAutoSetDeadlineSetting($formData);
            $this->renderSetDeadlineAfterDaysSetting($formData);
            ?>
        </fieldset>
        <?php
    }

    /**
     * This function will display checkbox to enable and disable compatibility
     * with vendors.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderCompatibilitySetting($formData)
    {
        ?>
        <div class="fd">
            <div class='left_div'>
                <label for="enable_comp_vendors">
                    <?php _e('Enable Compatibility with vendors', QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class='right_div'>
                <?php
                $helptip = __('To enable compatibility with vendors, enable this setting.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($formData['enable_comp_vendors']) ? $formData[ 'enable_comp_vendors' ] : 0); ?> id="enable_comp_vendors" />
                <input type="hidden" name="wdm_form_data[enable_comp_vendors]" value="<?php echo isset($formData[ 'enable_comp_vendors' ]) && $formData[ 'enable_comp_vendors' ] == 1 ? $formData[ 'enable_comp_vendors' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function will display checkbox to enable and disable auto send
     * quotation functionality.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderSendQuotationSetting($formData)
    {
        ?>
        <div class="fd enable-comp-vendors-depended">
            <div class="left_div">
                <label for="enable_auto_send_quotation_vendor">
                    <?php _e('Enable Auto Send Quotation', QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class="right_div">
                <?php
                $helptip = __('Enable this setting if quotation should be sent automatically after all vendors have updated the products\' prices or after admin has generated the quote. The quote would be sent on the \'Quote Send Deadline\' date.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($formData['enable_auto_send_quotation_vendor']) ? $formData[ 'enable_auto_send_quotation_vendor' ] : 0); ?> id="enable_auto_send_quotation_vendor" />
                <input type="hidden" name="wdm_form_data[enable_auto_send_quotation_vendor]" value="<?php echo isset($formData[ 'enable_auto_send_quotation_vendor' ]) && $formData[ 'enable_auto_send_quotation_vendor' ] == 1 ? $formData[ 'enable_auto_send_quotation_vendor' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function will render setting (checkbox) to enable and disable
     * the 'Export capability' to vendors.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderExportCapabilitySetting($formData)
    {
        ?>
        <div class="fd enable-comp-vendors-depended">
            <div class="left_div">
                <label for="enable_export_capability_vendor">
                    <?php _e('Enable Export Capability', QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class="right_div">
                <?php
                $helptip = __('Enable this setting if vendor should be able to export enquiries data.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($formData['enable_export_capability_vendor']) ? $formData[ 'enable_export_capability_vendor' ] : 0); ?> id="enable_export_capability_vendor" />
                <input type="hidden" name="wdm_form_data[enable_export_capability_vendor]" value="<?php echo isset($formData[ 'enable_export_capability_vendor' ]) && $formData[ 'enable_export_capability_vendor' ] == 1 ? $formData[ 'enable_export_capability_vendor' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function will render setting to enable/ disable vendor reminder
     * email.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderEnableVendorReminderEmailSetting($formData)
    {
        ?>
        <div class="fd enable-comp-vendors-depended">
            <div class="left_div">
                <label for="enable_vendor_reminder_email">
                    <?php _e('Enable Vendor Reminder Email', QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class="right_div">
                <?php
                $helptip = __('Enable this setting if reminder email should be sent to the vendor. The vendor reminder email is sent two days before to \'Quote Send Deadline\' date.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($formData['enable_vendor_reminder_email']) ? $formData[ 'enable_vendor_reminder_email' ] : 1); ?> id="enable_vendor_reminder_email" />
                <input type="hidden" name="wdm_form_data[enable_vendor_reminder_email]" value="<?php echo isset($formData[ 'enable_vendor_reminder_email' ]) ? $formData[ 'enable_vendor_reminder_email' ] : 1 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function will render setting to enable/ disable admin reminder
     * email.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderEnableAdminReminderEmailSetting($formData)
    {
        ?>
        <div class="fd enable-comp-vendors-depended">
            <div class="left_div">
                <label for="enable_admin_reminder_email">
                    <?php _e('Enable Admin Reminder Email', QUOTEUP_TEXT_DOMAIN) ?>
                </label>

            </div>
            <div class="right_div">
                <?php
                $helptip = __('Enable this setting if reminder email should be sent to the admin. The admin reminder email is sent one day before to \'Quote Send Deadline\' date.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($formData['enable_admin_reminder_email']) ? $formData[ 'enable_admin_reminder_email' ] : 1); ?> id="enable_admin_reminder_email" />
                <input type="hidden" name="wdm_form_data[enable_admin_reminder_email]" value="<?php echo isset($formData[ 'enable_admin_reminder_email' ]) ? $formData[ 'enable_admin_reminder_email' ] : 1 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function will render setting (checkbox) to enable and disable
     * the 'Auto Set Deadline' functionality.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderAutoSetDeadlineSetting($formData)
    {
        ?>
        <div class="fd enable-comp-vendors-depended">
            <div class="left_div">
                <label for="enable_auto_set_deadline_vendor">
                    <?php _e('Auto Set Quote Send Deadline', QUOTEUP_TEXT_DOMAIN); ?>
                </label>

            </div>
            <div class="right_div">
                <?php
                $helptip = __('Enable this setting if deadline should be set automatically whenever customer makes an enquiry about the product(s).', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="checkbox" class="wdm_wpi_input wdm_wpi_checkbox" value="1" <?php checked(1, isset($formData['enable_auto_set_deadline_vendor']) ? $formData[ 'enable_auto_set_deadline_vendor' ] : 0); ?> id="enable_auto_set_deadline_vendor" />
                <input type="hidden" name="wdm_form_data[enable_auto_set_deadline_vendor]" value="<?php echo isset($formData[ 'enable_auto_set_deadline_vendor' ]) && $formData[ 'enable_auto_set_deadline_vendor' ] == 1 ? $formData[ 'enable_auto_set_deadline_vendor' ] : 0 ?>" />
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function will render setting (number) to set/ configure
     * auto set quote send deadline period in terms of days.
     * @param [array] $formData [Settings stored previously in database]
     */
    public function renderSetDeadlineAfterDaysSetting($formData)
    {
        ?>
        <div class="fd enable-comp-vendors-depended enable-auto-set-dl-depended">
            <div class="left_div">
                <label for="set_deadline_after_days">
                    <?php _e('Auto Set Quotation Deadline After', QUOTEUP_TEXT_DOMAIN); ?>
                </label>

            </div>
            <div class="right_div">
                <?php
                $helptip = __('Quote should be sent before set days.', QUOTEUP_TEXT_DOMAIN);
                echo \quoteupHelpTip($helptip, true);
                ?>
                <input type="number" class="wdm_wpi_input wdm_wpi_number" name="wdm_form_data[set_deadline_after_days]" id="set_deadline_after_days" value="<?php echo empty($formData[ 'set_deadline_after_days' ]) ? 4 : $formData[ 'set_deadline_after_days' ]; ?>" />
                <?php echo '<em>'.__('Days', QUOTEUP_TEXT_DOMAIN).'</em>'; ?>
            </div>
            <div class="clear"></div>
        </div>
        <?php
    }

    /**
     * This function is used to display enquiry fields checkboxes to select to be
     * anonymized
     */
    public function displayEnquiryFields()
    {
        $enquiryDataFields = quoteupGetAllFormFields();
        $selectedFields    = get_option('wdm_vendor_visible_customer_data', false);
        ?>
        <fieldset class="vendor-visible-form-fields-fieldset">
            <legend><?php echo __('Vendor Form Fields', QUOTEUP_TEXT_DOMAIN) ?><?php echo \quoteupHelpTip(__('Select form fields which should be visible to vendors.', QUOTEUP_TEXT_DOMAIN), true); ?></legend>

            <?php
            $common_fields_checkboxes = $default_form_fields_checkboxes = $custom_form_fields_checkboxes = '';
            // loop through all enqiury fields stored in $enquiryDataFields
            foreach ($enquiryDataFields['enquiry-detail'] as $key => $value) {
                if ('subject' == $key) {
                    continue;
                }
                if (quoteupIsCommonField($key)) {
                    // These Fields are common for Default as well as Custom Form
                    $common_fields_checkboxes .= $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-detail'][$key]), true, false);
                } else {
                    // These Fields are only available in Default Form
                    $default_form_fields_checkboxes .= $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-detail'][$key]), true, false);
                }
            }

            if (isset($enquiryDataFields['enquiry-meta'])) {
                foreach ($enquiryDataFields['enquiry-meta'] as $key => $value) {
                    // These are Custom Form Fields.
                    $custom_form_fields_checkboxes .= $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-meta'][$key]), false, false);
                }
            }

            $this->displayFieldsInAccordion($common_fields_checkboxes, $default_form_fields_checkboxes, $custom_form_fields_checkboxes);
            ?>
        </fieldset>
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
                    <input type="hidden" name="wdm_vendor_visible_customer_data[enquiry-detail][<?php echo esc_html($enquiryField); ?>]" value="<?php echo $isSelected; ?>" />
                    <?php
                } else {
                    ?>
                    <input type="hidden" name="wdm_vendor_visible_customer_data[enquiry-meta][<?php echo esc_html($enquiryField); ?>]" value="<?php echo $isSelected; ?>" />
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

    public function displayFieldsInAccordion($common_fields_checkboxes, $default_form_fields_checkboxes, $custom_form_fields_checkboxes)
    {
        ?>
        <div class="vendor-visible-form-fields-accordion">
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
}

QuoteupVendorSettings::getInstance();

