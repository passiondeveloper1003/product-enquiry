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
class QuoteupPrivacy extends QuoteupAbstractPrivacy
{
    /**
     * Init - hook into events.
     */
    public function __construct()
    {
        parent::__construct(__('QuoteUp', QUOTEUP_TEXT_DOMAIN));

        $this->includeFiles();

        // This hook registers QuoteUp data exporters.
        $this->addExporter('quoteup-enquiry-data', __('Enquiries', QUOTEUP_TEXT_DOMAIN), array('Includes\Admin\Privacy\QuoteupPrivacyExporters', 'quoteupGetEnquiryData'));

        // This hook registers QuoteUp data erasers.
        $this->addEraser('quoteup-enquiries', __('Enquiries', 'woocommerce'), array('Includes\Admin\Privacy\QuoteupPrivacyErasers', 'enquiryDataEraser'));

        //Add bulk action for Making data anonymous
        add_filter('quoteup_get_bulk_actions', array($this, 'addBulkActions'));

        // When this is fired, data is removed in a given enquiry. Called from bulk actions.
        add_action('quoteup_process_bulk_action', array($this, 'removeEnquiryPersonalData'), 10, 2);

        // When this is fired, notice is displayed on remove personal deta completion.
        add_action('quoteup_bulk_action_notices', array($this, 'removeEnquiryPersonalDataNotice'), 10, 2);

        // When this is fired, enquiry data is removed for the corresponding order.
        add_action('woocommerce_privacy_remove_order_personal_data', array('Includes\Admin\Privacy\QuoteupPrivacyErasers', 'removeOrderEnquiryData'), 10, 1);

        add_filter('quoteup_meta_key_header_in_table', array($this, 'modifyCustomerDataLabel'), 10, 1);
    }

    public function includeFiles()
    {
        // Include functions
        include_once 'quoteup-privacy-functions.php';

        // Include supporting classes.
        include_once 'class-quoteup-privacy-expoters.php';
        include_once 'class-quoteup-privacy-erasers.php';
        include_once 'class-quoteup-privacy-settings.php';
        include_once 'class-quoteup-privacy-cookie-consent.php';
        include_once 'class-quoteup-privacy-terms-conditions.php';
        include_once 'class-quoteup-privacy-settings-modal.php';
        include_once 'privacy-ajax.php';
    }

    public function modifyCustomerDataLabel($metaKeyLabel)
    {
        if ('terms and conditions' == $metaKeyLabel) {
            $metaKeyLabel = __('Terms and conditions accepted', QUOTEUP_TEXT_DOMAIN);
        }

        return $metaKeyLabel;
    }

    /**
     * Add privacy policy content for the privacy policy page.
     *
     * @since 6.2.0
     */
    public function getPrivacyMessage()
    {
        ob_start();
        ?>
        <div>
            <p>This is sample text for the policies your Privacy Policy should include, with respect to Product Enquiry Pro for WooCommerce. Depending on what settings are enabled, the specific information shared by your store will vary. </p>

            <p>We recommend consulting a lawyer when deciding what information to disclose in your Privacy Policy. You are free to make edits as necessary and add to the content below.</p>

            <p>Note: Depending upon the use case of your website, you can either use the word ‘Enquiry’, or ‘Quote’, or both.</p>

            <p>We collect user information whenever you submit an Enquiry/Quote form on our store. When you submit an Enquiry/Quote Request form, we’ll ask you to provide following information<sup>1</sup>:</p>
            <ol>
                <li>Your Name</li>
                <li>Your Email Address</li>
            </ol>
            <p>[1] Note to Site Admin - In case you decide to acquire more information, add it to the list above.</p>

            <p>When an Enquiry/Quote form is submitted, the IP address of the user is collected, in addition to the data in the form. This information may be used to identify the country from which Enquiry/Quote request is submitted.<sup>2</sup></p>
            <p>[2] Note to Admin - As a site owner, if you are using IP address for any other purpose, then mention it here.</p>

            <p>The information provided while submitting Enquiry/Quote Request form will be used to<sup>3</sup>:</p>
            <ul>
                <li>Gain complete understanding of your requirements</li>
                <li>Generate an accurate quote</li>
                <li>Ensure unimpeded communication with respect to your enquiry</li>
            </ul>
            <p>[3] Note to Admin - You can add any other relevant reasons that justify your collection of user information.</p>

            <p>By submitting the Enquiry/Quote Request form, you allow Site Owner to contact you via email for further discussion. Quote created against your Quote Request is sent to you by email. This is the same email address provided during Enquiry/Quote Request.<sup>4</sup></p>
            <p>[4] Note to Admin - Add details here about how long you intend to store  the information submitted in an Enquiry/Quote Request form.</p>

            <p><b>Data Access :</b>The data you supply is shared with members on email. It is stored on our server too and can be accessed by our team members, for the purpose of processing your Quote Request, creating a Quote, and communicating with you. We do not share your personal information with any third-party application.<sup>5</sup></p>
            <p>[5] Note to Admin - Add details of any third-parties with whom you might share non-sensitive information and the reasons for doing so. In case you do not share this information with any third-party application, mention that the store maintains complete confidentiality with respect to all user information.</p>
        </div>
        <?php
        $content = ob_get_clean();
        // $content = '<div><b>QUOTEUP PRIVACY MESSAGE ON PRIVACY PAGE</b></div>';

        return apply_filters('quoteup_privacy_policy_content', $content);
    }

    /**
     * This function is used to add remove personal data option in bulk options
     * @param array $actions previous actions
     */
    public function addBulkActions($actions)
    {
        global $wp_version;
        if (version_compare($wp_version, '4.9.6', '<')) {
            return $actions;
        }
        $privacyActions = array(
                'remove-personal-data' => __('Remove personal data', QUOTEUP_TEXT_DOMAIN),
            );

        return array_merge($actions, $privacyActions);
    }

    /**
     * This function is used to remove personal data from enquiry when bulk
     * action is selected
     * @param  int $currentPage Page id
     * @param  string $sendback url of page
     */
    public function removeEnquiryPersonalData($currentPage, $sendback)
    {
        // If the remove personal data bulk action is triggered
        if ((isset($_POST[ 'action' ]) && $_POST[ 'action' ] == 'remove-personal-data') || (isset($_POST[ 'action2' ]) && $_POST[ 'action2' ] == 'remove-personal-data')
            ) {
            check_admin_referer('bulk-enquiries');

            if (isset($_POST[ 'bulk-select' ])) {
                $anonimizedIds = esc_sql($_POST[ 'bulk-select' ]);

                // loop over the array of record IDs and anonimize them
                foreach ($anonimizedIds as $singleId) {
                    $singleId = strip_tags($singleId);
                    QuoteupPrivacyErasers::removeEnquiryPersonalData($singleId);
                }
                $count = count($anonimizedIds);
                $sendback = add_query_arg('remove-personal-data', $count, $sendback);
            } else {
                $sendback = add_query_arg('noenquiryselected', 'yes', $sendback);
            }

            wp_redirect($sendback);
            unset($currentPage);
            exit;
        }
    }

    /**
     * This function displays notice when remove personal data is triggerd
     * from bulk action
     * @param  string $action Name of action triggered
     * @param  int $value  number of enquiries updated
     */
    public function removeEnquiryPersonalDataNotice($action, $value)
    {
        if ($action == 'remove-personal-data') {
            echo "<div class='updated'><p> $value ".__('enquiries are Anonimized', QUOTEUP_TEXT_DOMAIN).'</p></div>';
        } elseif ($action == 'noenquiryselected') {
            echo "<div class='error'><p>".__('Select Enquiries to Anonimize', QUOTEUP_TEXT_DOMAIN).'</p></div>';
        }
    }

    /**
     * This function returns an array containing all fields from
     * 'enquiry_details_new' table, 'enquiry_meta' table and all custom
     * forms to list
     *
     * @return array containing fields to anonymize
     */
    public static function quoteupAllFieldsToAnonymize()
    {
        global $wpdb;
        $privacyFieldsData = array();

        // retrieveing fields from enquiry_details_new table
        $privacyFieldsData['enquiry-detail'] = quoteupEnquiryDtlFieldsToAnonymize();

        // retrieveing fields from enquiry_meta table
        $metaFields = quoteupEnquiryMetaFieldsToAnonymize($wpdb);

        // retrieveing fields from custom form
        $customFormFields = quoteupGetCustomFormFields();

        $privacyFieldsData['enquiry-meta'] = array_merge($metaFields, $customFormFields);
        return $privacyFieldsData;
    }
}

new QuoteupPrivacy();
