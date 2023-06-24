<?php
namespace Includes\Admin\Privacy;

/**
 * Privacy related functionality which ties into WordPress functionality.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * This class displays the enquiry anonymize fields in the modal if current page
 * is form edit page
 */
class QuoteupPrivacySettingsModal extends QuoteupPrivacySettings
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

    /**
      * Init - hook into events.
      */
    private function __construct()
    {
        add_action('admin_init', array($this, 'enquiryAnonymizeFieldsInitialization'));
        add_action('pre_post_update', array($this, 'saveOldFormFields'), 20);
        add_action('quoteup_remove_hooks_before_form_status_update', array($this, 'removeHooksBeforeFormUpdate'));
        add_action('quoteup_add_hooks_after_form_status_update', array($this, 'addHooksAfterFormUpdate'));
    }

    /**
     * Remove 'saveOldFormFields' from the 'pre_post_update' action hook.
     * Otherwise, it will be called twice if the form status is
     * changed to 'publish' and will prevent modal from displaying.
     */
    public function removeHooksBeforeFormUpdate()
    {
        remove_action('pre_post_update', array($this, 'saveOldFormFields'), 20);
    }

    /**
     * Add 'saveOldFormFields' to 'pre_post_update' action hook.
     */
    public function addHooksAfterFormUpdate()
    {
        add_action('pre_post_update', array($this, 'saveOldFormFields'), 20);
    }

    /**
     * This function enqueues scripts and add function to hook to render
     * modal containing the Enquiry Fields with checkboxes on Form edit page
     */
    public function enquiryAnonymizeFieldsInitialization()
    {
        global $wp_version;
        if (version_compare($wp_version, '4.9.6', '<')) {
            return;
        }
        if ($this->hasFormUpdatedOrPublished()) {
            add_action('admin_enqueue_scripts', array($this, 'enqueueScriptsFormEditPage'));
            add_action('admin_footer', array( $this, 'renderEnquiryAnonymizeFieldsModal'));

            // if form has been updated
            if ('1' == $_GET['message']) {
                add_filter('should_display_anonymize_fields_modal', array($this, 'shouldDisplayModalOnFormUpdate'), 15, 2);
            } elseif ('6' == $_GET['message']) { // form has been published
                add_filter('should_display_anonymize_fields_modal', array($this, 'shouldDisplayModalOnFormPublished'), 15, 2);
            }

            // functions hooked for modal
            add_action('quoteup_wdm_enquiry_fields_modal_content', array($this, 'enquiryFieldsModalFormHeader'), 10);
            add_action('quoteup_wdm_enquiry_fields_modal_content', array($this, 'enquiryFieldsModalFormBody'), 20);
            add_action('quoteup_wdm_enquiry_fields_modal_content', array($this, 'enquiryFieldsModalFormFooter'), 30);
        }
    }

    /**
     * Check if form has been upated or published
     *
     * @return bool Returns true if form has been updated or published, false
     *              otherwise
     */
    public function hasFormUpdatedOrPublished()
    {
        global $pagenow;
        $formUpdatedPublished = false;

        if (is_admin() && 'post.php' === $pagenow && isset($_GET['post']) && 'form' === get_post_type($_GET['post']) && isset($_GET['message']) && ('1' == $_GET['message'] || '6' == $_GET['message'])) {
            $formUpdatedPublished = true;
        }

        return apply_filters('quoteup_has_form_updated_published', $formUpdatedPublished);
    }

    /**
     * Checks whether anonymize fields settings modal should be displayed when
     * form is updated
     * @param bool $display
     *
     * @return bool true if modal should be displayed, false otherwise
     */
    public function shouldDisplayModalOnFormUpdate($display, $post)
    {
        unset($display);
        $formData = get_post_meta($post->ID, 'form_data', true);

        @session_start();
        $oldFormData = false;
        // $oldFormData = isset($_SESSION['quoteup_old_form_data_'.$post->ID])?$_SESSION['quoteup_old_form_data_'.$post->ID] : false;

        if (isset($_SESSION['quoteup_old_form_data_'.$post->ID])) {
            $oldFormData = $_SESSION['quoteup_old_form_data_'.$post->ID];
            unset($_SESSION['quoteup_old_form_data_'.$post->ID]);
        }

        if (false === $oldFormData || !$this->isNewFormDiffOldForm($oldFormData, $formData)) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether anonymize fields settings modal should be displayed when
     * form is published
     * @param bool $display
     *
     * @return bool true if modal should be displayed, false otherwise
     */
    public function shouldDisplayModalOnFormPublished($display, $post)
    {
        unset($display);
        unset($post);
        // $formData = get_post_meta($post->ID, 'form_data', true);
        // if (sizeof($formData['fields']) > 2) {
        //     return true;
        // }
        // return false;
        return true;
    }


    public function saveOldFormFields($post_ID)
    {
        if ('form' === get_post_type($post_ID)) {
            $formData = get_post_meta($post_ID, 'form_data', true);
            @session_start();
            $_SESSION[ 'quoteup_old_form_data_'.$post_ID ] = $formData;
        }
    }

    public function enqueueScriptsFormEditPage()
    {
        if (!wp_script_is('bootstrap-modal', 'enqueued')) {
            wp_enqueue_script('bootstrap-modal', QUOTEUP_PLUGIN_URL.'/js/admin/bootstrap-modal.js', array('jquery'), false, true);
        }

        if (!wp_script_is('privacy-settings-js', 'enqueued')) {
            wp_enqueue_script('privacy-settings-js', QUOTEUP_PLUGIN_URL.'/js/privacy/privacy-settings.js', array('jquery'), false, true);
        }

        if (!wp_style_is('modal_css1', 'enqueued')) {
            wp_enqueue_style('modal_css1', QUOTEUP_PLUGIN_URL.'/css/wdm-bootstrap.css', false, false);
        }

        if (!wp_style_is('privacy-settings-css', 'enqueued')) {
            wp_enqueue_style('privacy-settings-css', QUOTEUP_PLUGIN_URL.'/css/privacy/privacy-settings.css', false, false);
        }

        wp_enqueue_style('woocommerce_admin_styles', plugin_dir_url(dirname(dirname(dirname(__FILE__)))).'woocommerce/assets/css/admin.css');

        wp_enqueue_script('jquery-tiptip', array('jquery'));
    }

    /**
     * This function renders modal HTML containing the Enquiry Fields with
     * checkboxes
     */
    public function renderEnquiryAnonymizeFieldsModal()
    {
        global $post;
        if (!apply_filters("should_display_anonymize_fields_modal", false, $post)) {
            return;
        }

        do_action('quoteup_before_enquiry_fields_modal');
        ?>
        <div class="wdm-modal wdm-fade" id="wdm-quoteup-enquiry-fields-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none">
            <div class="wdm-modal-dialog">
                <?php
                    do_action('quoteup_before_enquiry_fields_modal_content');
                ?>
                <div class="wdm-modal-content" >
                    <?php

                    /**
                     * quoteup_wdm_enquiry_fields_modal_content hook.
                     *
                     * @hooked enquiryFieldsModalFormHeader - 10 (outputs Modal header)
                     * @hooked enquiryFieldsModalFormBody - 20 (outputs Modal body)
                     * @hooked enquiryFieldsModalFormFooter - 30 (outputs Modal footer)
                     */
                    do_action('quoteup_wdm_enquiry_fields_modal_content');

                    ?>
                </div> <!--/modal-content-->
                <?php
                do_action('quoteup_after_enquiry_fields_modal_content');
                ?>
            </div> <!--/modal-dialog-->
        </div> <!--/modal-->

        <?php
        do_action('quoteup_after_enquiry_fields_modal');
    }

    /**
     * Returns true if enquiry form fields are not same
     * It returns true if any of the following conditions are met:
     *   > new field has been added
     *   > existing field has been removed
     *   > field label or type has been changed
     * @param array $oldFormData old form data
     * @param array $newFormData new form data
     *
     * @return bool true if form field has been modified as mentioned above,
     *              otherwise false. It also returns false if one of arguments
     *              is not array
     */
    public function isNewFormDiffOldForm($oldFormData, $newFormData)
    {
        if (!is_array($oldFormData) || !is_array($newFormData)) {
            return false;
        }

        // check if number of fields are different
        if (sizeof($oldFormData['fields']) != sizeof($newFormData['fields'])) {
            return true;
        }

        // count number of each fields in both form
        $oldFormCountValues = array_count_values($oldFormData['fields']);
        $newFormCountValues = array_count_values($newFormData['fields']);

        $displayModal = false;
        // check number of each fields are equal
        if ($oldFormCountValues == $newFormCountValues) {
            // for now, these stores each fields' labels and validation types values
            $oldFormKeyValues = $newFormKeyValues = array();

            $keysToRetrieve = array('label', 'validation');

            $oldFormKeyValues = getValuesFromField($oldFormData['fieldsinfo'], $keysToRetrieve);
            $newFormKeyValues = getValuesFromField($newFormData['fieldsinfo'], $keysToRetrieve);

            if ($oldFormKeyValues === false || $newFormKeyValues === false) {
                return $displayModal;
            }

            foreach ($keysToRetrieve as $keyToRetrieve) {
                $oldFormCountValues = $newFormCountValues = array();
                if (isset($oldFormKeyValues[$keyToRetrieve.'_data'])) {
                    $oldFormCountValues = array_count_values($oldFormKeyValues[$keyToRetrieve.'_data']);
                }
                if (isset($newFormKeyValues[$keyToRetrieve.'_data'])) {
                    $newFormCountValues = array_count_values($newFormKeyValues[$keyToRetrieve.'_data']);
                }
                if ($oldFormCountValues != $newFormCountValues) {
                    $displayModal = true;
                    break;
                }
            }
        } else {
            $displayModal = true;
        }
        return $displayModal;
    }

    public function enquiryFieldsModalFormHeader()
    {
        ?>
        <!--/modal header-->
        <div class="wdm-modal-header">
            <button type="button" class="close" data-dismiss="wdm-modal" aria-hidden="true">
                &times;
            </button>
            <h4 class="wdm-modal-title" id="myModalLabel">
                <span>
                    <?php
                    _e('Select fields to be anonymized', QUOTEUP_TEXT_DOMAIN);
                    echo \quoteupHelpTip(__('Anonymization is a process of removing personal information from the data. Fields which are going to contain personal information should be ticked below to remove data associated with them during <br>1. \'Complete Personal Data Erasure\' process<br>2. \'Order Specific Personal Data Erasure\' process (applicable when Quotation System is enabled)<br>3. \'Enquiry/Quote Specific Personal Data Erasure\' process.', 'quoteup'), true);
                    ?>
                </span>
            </h4>
        </div>
        <!--/modal header-->
        <?php
    }

    public function enquiryFieldsModalFormBody()
    {
        $settings = quoteupSettings();
        ?>
        <!--/modal body-->
        <div class="wdm-modal-body">
            <form id="enquiry-fields-form" name="enquiry-fields-form" method="post">
                <div class="quoteup-update-message">
                    <b><p>Form Saved Successfully.<br>
                    Select Fields to be Anonymized Below.</p></b>
                </div>
                <div class="fd">
                <?php
                if ($settings['enquiry_form'] == 'default' || ($settings['enquiry_form'] == 'custom' && $settings['custom_formId'] != $_GET['post'])) {
                    ?>
                    <p><b>Note : </b>We have detected that the form you are editing is not set as the active form in PEP settings. <b>Modify below section only if you intend to make this form active because changes made here take effect globally and may affect anonimization configuration set for the current active form.</b></p>
                    <?php
                }
                ?>
                    
                </div>
                <?php
                do_action('quoteup_before_enquiry_fields_modal_body');
                $this->displayEnquiryFields();
                do_action('quoteup_after_enquiry_fields_modal_body');

                $privacySettingTabURL = admin_url().'admin.php?page=quoteup-for-woocommerce#wdm_privacy';
                ?>
                <div class="fd privacy-settings-wrapper settings-tab-link">
                    <div class='left_div'>
                        <a href="<?php echo esc_url($privacySettingTabURL); ?>"><?php echo __("Go To Privacy Tab in Settings", QUOTEUP_TEXT_DOMAIN) ?></a>
                    </div>
                </div>
                <div class="fd privacy-settings-wrapper settings-tab-link">
                    <div class='right_div'>
                        <button type="submit" id="btnEnquiryFieldsStgsSave" name="btnEnquiryFieldsStgsSave" class="save-settings button-primary"><?php echo __("Save", QUOTEUP_TEXT_DOMAIN) ?></button>
                        <button type="button" class="save-settings button-secondary" data-dismiss="wdm-modal" aria-hidden="true">Close</button>
                    </div>
                </div>
            </form>
        </div>
        <!--/modal body-->
        <?php
    }

    public function enquiryFieldsModalFormFooter()
    {
        ?>
        <!--/modal footer-->
        <div class="wdm-modal-footer">
            <a href='https://wisdmlabs.com/' class="wdm-poweredby">Powered by &copy; WisdmLabs
            </a>
        </div>
        <!--/modal footer-->
        <?php
    }

    /**
     * This function is used to display enquiry fields checkboxes to select to
     * be anonymized
     */
    public function displayEnquiryFields()
    {
        global $post;
        $formData = get_post_meta($post->ID, 'form_data', true);

        $currentFormFields = quoteupAppendSuffixToField($formData['fieldsinfo']);


        $selectedFields = get_option("wdm_privacy_anonymize_data", false);

        ?>
            <?php
            $defaultFields = defaultFieldsAndLabelsInCustomForm();
            // loop through default fields shown in the custom form
            foreach ($defaultFields as $key => $label) {
                $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-detail'][$key]), true, true, $label);
            }

            // loop through form enqiury fields
            foreach ($currentFormFields as $key => $value) {
                $this->renderCheckboxSettings($key, isset($selectedFields['enquiry-meta'][$key]));
                unset($value);
            }
            $this->localizeScript();
            ?>
        <?php
    }

    /**
     * This function is used to display the checkbox setting
     * @param array     $enquiryField       Enquiry field and value whether it
     *                                      has been checked or not
     * @param bool      $isSelected         is field selected
     * @param bool      $enquiryTable does  field belongs to enquiry table
     * @param bool      $echo               echo or return
     * @param string    $primaryFieldLabel  label of primary field, it works
     *                                      only if $enquiryTable is true
     */
    public function renderCheckboxSettings($enquiryField, $isSelected, $enquiryTable = false, $echo = true, $primaryFieldLabel = '')
    {
        if ($echo != true) {
            ob_start();
        }
        ?>
        <div class="fd privacy-settings-wrapper">
            <div class='left_div privacy-settings-label'>
                <label for="enquiry-field">
                    <?php
                    if ($enquiryTable) {
                        echo esc_html(quoteupAnonymizeFieldsLabel($enquiryField));
                        if (!empty($primaryFieldLabel)) {
                            echo ' ('.esc_html($primaryFieldLabel).') ';
                        }
                    } else {
                        echo esc_html($enquiryField);
                    }
                    ?>
                </label>

            </div>
            <div class='right_div privacy-settings-checkbox'>
            <?php
            if ($enquiryTable) {
                ?>
                <input type="checkbox" class="checkbox" name="wdm_privacy_anonymize_data[enquiry-detail][<?php echo esc_html($enquiryField); ?>]" <?php checked(1, $isSelected); ?> value="1" id="primary_<?php echo esc_html($enquiryField); ?>">
                <?php
            } else {
                ?>
                <input type="checkbox" class="checkbox" name="wdm_privacy_anonymize_data[enquiry-meta][<?php echo esc_html($enquiryField); ?>]" <?php checked(1, $isSelected); ?> value="1" id="<?php echo esc_html($enquiryField); ?>">
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

    public function localizeScript()
    {
        global $post;
        $savedText  = __('Saved', QUOTEUP_TEXT_DOMAIN);
        $savingText = __('Saving', QUOTEUP_TEXT_DOMAIN);
        $errText    =  __('Error Occurred', QUOTEUP_TEXT_DOMAIN);
        wp_localize_script(
            'privacy-settings-js',
            'quoteup_modal',
            array(
                'show'    => 'yes',
                'ajaxurl'    => admin_url('admin-ajax.php'),
                'post_id'    => $post->ID,
                'savedText'  => $savedText,
                'savingText' => $savingText,
                'errText'    => $errText
            )
        );
    }
}

QuoteupPrivacySettingsModal::getInstance();
