<?php
/**
 * Privacy related functionality which ties into WordPress functionality.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

if (!defined('DOING_AJAX')) {
    return; // Exit if accessed directly
}

//Ajax to save enquiry fields settings from form edit page
add_action('wp_ajax_quoteup_save_enq_stg', 'saveEnquiryFieldStgModal');

function saveEnquiryFieldStgModal()
{
    $allEnquiryFields = \Includes\Admin\Privacy\QuoteupPrivacy::quoteupAllFieldsToAnonymize();

    $post_id = intval($_POST['post_id']);
    $formData = get_post_meta($post_id, 'form_data', true);

    $anonymizeData = get_option('wdm_privacy_anonymize_data', false);

    // processing enquiry meta fields
    /**
     * suppose, message2 and subject2 fields were set using the modal.
     * Later, these fields name got changed as 'message1' and 'subject1'.
     * To remove these fields 'message2' and 'subject2' from the settings, this
     * code is used
     */
    if (isset($anonymizeData['enquiry-meta']) && !empty($anonymizeData['enquiry-meta'])) {
        $allEnquiryFields['enquiry-meta'] = array_intersect_key($allEnquiryFields['enquiry-meta'], $anonymizeData['enquiry-meta']);
    } else {
        $allEnquiryFields['enquiry-meta'] = array();
    }

    $formDataMetaFields = quoteupAppendSuffixToField($formData['fieldsinfo']);
    $enquiryAnonymizeFields['enquiry-meta'] = array_diff_key($allEnquiryFields['enquiry-meta'], $formDataMetaFields);

    parse_str($_POST['form_data'], $formDataEnqStgAjax);

    if (isset($formDataEnqStgAjax['wdm_privacy_anonymize_data']['enquiry-meta'])
        && !empty($formDataEnqStgAjax['wdm_privacy_anonymize_data']['enquiry-meta'])) {
        $enquiryAnonymizeFields['enquiry-meta'] = array_merge($enquiryAnonymizeFields['enquiry-meta'], $formDataEnqStgAjax['wdm_privacy_anonymize_data']['enquiry-meta']);
    }

    // processing enquiry-detail/ primary fields
    $enquiryAnonymizeFields['enquiry-detail'] = array();
    if (isset($anonymizeData['enquiry-detail']) && !empty($anonymizeData['enquiry-detail'])) {
        $enquiryAnonymizeFields['enquiry-detail'] = array_diff_key($anonymizeData['enquiry-detail'], defaultFieldsAndLabelsInCustomForm(0, true));
    }
    if (isset($formDataEnqStgAjax['wdm_privacy_anonymize_data']['enquiry-detail'])
        && !empty($formDataEnqStgAjax['wdm_privacy_anonymize_data']['enquiry-detail'])) {
        $enquiryAnonymizeFields['enquiry-detail'] = array_merge($enquiryAnonymizeFields['enquiry-detail'], $formDataEnqStgAjax['wdm_privacy_anonymize_data']['enquiry-detail']);
    }

    $enquiryAnonymizeFields = \Includes\Admin\Privacy\QuoteupPrivacySettings::quoteupSanitizePrivacySettings($enquiryAnonymizeFields);

    update_option('wdm_privacy_anonymize_data', $enquiryAnonymizeFields);

    die(1);
}
