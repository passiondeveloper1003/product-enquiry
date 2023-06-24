<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * This function returns an array containing all fields from
 * 'enquiry_details_new' table to be anonymized indepedent of fields have been
 * selected or not to anonymize
 *
 * @return array fields to anonymize from 'enquiry_details_new' table
 */
function quoteupEnquiryDtlFieldsToAnonymize()
{
    $privacyFields = array(
        'name' => 'text',
        'email' => 'email',
        'enquiry_ip' => 'ip',
        'message' => 'longtext',
        'subject' => 'text',
        'phone_number' => 'text',
        'date_field' => 'date',
    );

    return apply_filters('quoteup_enquiry_tbl_privacy_fields', $privacyFields);
}

function quoteupAnonymizeFieldsLabel($field)
{

    $privacyFields = array(
        'name' => __('Primary Name', QUOTEUP_TEXT_DOMAIN),
        'email' => __('Primary Email', QUOTEUP_TEXT_DOMAIN),
        'enquiry_ip' => __('Visitor IP Address', QUOTEUP_TEXT_DOMAIN),
        'message' => __('Default Form Message', QUOTEUP_TEXT_DOMAIN),
        'subject' => __('Default Form Subject', QUOTEUP_TEXT_DOMAIN),
        'phone_number' => __('Default Form Phone Number', QUOTEUP_TEXT_DOMAIN),
        'date_field' => __('Default Form Date Field', QUOTEUP_TEXT_DOMAIN)
    );

    $privacyFields = apply_filters('quoteup_enquiry_tbl_privacy_fields_label', $privacyFields);

    if (array_key_exists($field, $privacyFields)) {
        return $privacyFields[$field];
    }

    return $field;
}

function quoteupIsCommonField($field)
{
    $commonFields = array(
        'name',
        'email',
        'enquiry_ip'
    );

    $commonFields = apply_filters('quoteup_enquiry_tbl_privacy_common_fields', $commonFields);

    return in_array($field, $commonFields);
}

/**
 * Returns defaults fields and their labels added in the custom form. The
 * default fields are the fields which can't be removed from the custom enquiry
 * form and has the 'primary' key value as 'yes' in database.
 * @param int       $formId     form id
 * @param bool      $onlyFields true if need only fields without their labels
 *                              added to particular form
 *
 * @return array Returns defaults fields and their labels.
 */

function defaultFieldsAndLabelsInCustomForm($formId = 0, $onlyFields = false)
{
    // key   => same as enquiry table column name
    // value => default label name when custom form is created
    $defaultFieldsInForm = array(
        'name' => 'Name',
        'email' => 'Email',
    );

    if (!$onlyFields) {
        if (0 === $formId) {
            global $post;
            $formId = $post->ID;
        }
        $formData   = get_post_meta($formId, 'form_data', true);
        foreach ($formData['fieldsinfo'] as $key => $value) {
            if (isset($value['primary']) && 'yes' === $value['primary']) {
                $fieldType = explode("_", $key)[0];
                $fieldType = strtolower($fieldType);
                if (array_key_exists($fieldType, $defaultFieldsInForm)) {
                    $defaultLabel = $defaultFieldsInForm[$fieldType];
                    $defaultFieldsInForm[$fieldType] = trim($value['label']) !== $defaultLabel ? $value['label'] : '';
                }
            }
        }
    }

    return apply_filters("quoteup_default_fields_in_custom_form", $defaultFieldsInForm);
}

/**
 * This function returns an array containing all fields from 'enquiry_meta'
 * table to be anonymized indepedent of fields have been selected or not to
 * anonymize
 *
 * @return array fields to anonymize from 'enquiry_meta' table
 */
function quoteupEnquiryMetaFieldsToAnonymize($wpdb = null)
{
    if (empty($wpdb)) {
        global $wpdb;
    }

    $enquiryMetaTable   = getEnquiryMetaTable(); // fetch enquiry meta table name
    $metaFieldsToIgnore = array( 'enquiry_lang_code', 'quotation_lang_code', '_unread_enquiry', '_admin_quote_created', 'terms and conditions', 'cookie consent', 'mc-subscription-checkbox' );

    /**
     * Filter to modify the list of the fields which should be ignored.
     *
     * @param  array  $metaFieldsToIgnore  Fields to be ignored.
     */
    $metaFieldsToIgnore = apply_filters('quoteup_ignore_fields_enquiry_meta_tbl', $metaFieldsToIgnore);

    $fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$enquiryMetaTable} WHERE META_KEY NOT IN ('".implode("', '", $metaFieldsToIgnore)."')");

    $privacyFields = array();
    foreach ($fields as $field) {
        $privacyFields[$field] = "longtext";
    }

    return $privacyFields;
}

/**
 * Get enquiry fields from all custom forms
 *
 * @return array enquiry fields
 */
function quoteupGetCustomFormFields()
{
    $formFields = array();
    foreach (quoteGetAllFormIds() as $formId) {
        $formData   = get_post_meta($formId, 'form_data', true);
        $currentFormFields = quoteupAppendSuffixToField($formData['fieldsinfo']);
        $formFields = array_merge($currentFormFields, $formFields);
    }

    return $formFields;
}

/**
 * Add suffix to the end of the label name and return the fields from the form
 * data with datatype
 *
 * @param array $formFieldsInfo custom form retrieved from the post meta table
 * @param array $fieldsToIgnore Optional. This parameter accepts fields name in
 *                              lowercase which should not be returned in the
 *                              resulting array. Default array('file').
 *                              Example,
 *                              array('file', 'daterange', 'url', 'rating')
 * @return array Returns an associative array containing the fields name as key
 *               and their data type as value. It ignores the primary fields
 *               such as 'Name' and 'Email', along with fields specified in the
 *               parameter $fieldsToIgnore
 */
function quoteupAppendSuffixToField($formFieldsInfo, $fieldsToIgnore = array('file'))
{
    $fields = array();
    foreach ($formFieldsInfo as $key => $value) {
        // if the field is primary, don't add it since it is the part of the
        // enquiry-detail table
        if (isset($value['primary']) && 'yes' === $value['primary']) {
            continue;
        }
        $fieldType = explode("_", $key)[0];
        $fieldType = strtolower($fieldType);
        
        $fieldsToIgnore = apply_filters("quoteup_fields_to_ignore_in_form_data", $fieldsToIgnore);
        // ignore fields which are not required
        if (in_array($fieldType, $fieldsToIgnore)) {
            continue;
        }
        

        $dataType = isset($value['validation'])? $value['validation'] : $fieldType;

        switch ($fieldType) {
            case 'daterange':
                $fields[$value['label']. ' from'] = $dataType;
                $fields[$value['label']. ' to'] = $dataType;
                break;
            case 'fullname':
                $fields[$value['label']. ' title'] = $dataType;
                $fields[$value['label']. ' first name'] = $dataType;
                $fields[$value['label']. ' last name'] = $dataType;
                break;
            case 'phone':
                $fields[$value['label']. ' country code'] = $dataType;
                $fields[$value['label']. ' area code'] = $dataType;
                $fields[$value['label']. ' dial code'] = $dataType;
                break;
            default:
                $fields[$value['label']] = $dataType;
                break;
        }
    }
    return $fields;
}

// return all form ids
function quoteGetAllFormIds()
{
    $args = array( 'post_type' => 'form', 'fields' => 'ids');

    $formIds = new WP_Query($args);
    return $formIds->posts;
}

/**
 * Returns the keys' value from the fields array
 * @param array $fieldsArray    array containing all fields
 * @param array $paramname description
 *
 * @return array|bool returns array containing each field's particular key-
 *                    value or empty array or false if one of the arguments
 *                    is not array
 */
function getValuesFromField($fieldsArray, $keysToRetrieve)
{
    if (!is_array($fieldsArray) || !is_array($keysToRetrieve)) {
        return false;
    }

    $fieldsKeyValues = array();

    foreach ($fieldsArray as $fieldKey => $fieldValue) {
        foreach ($keysToRetrieve as $keyToRetrieve) {
            if (isset($fieldValue[$keyToRetrieve])) {
                $fieldsKeyValues[$keyToRetrieve.'_data'][$fieldKey] = $fieldValue[$keyToRetrieve];
            }
        }
    }
    return $fieldsKeyValues;
}

function getEnquiryWithEmail($email_address)
{
    global $wpdb;
    $enquiryTableName = getEnquiryDetailsTable();
    $sql = "SELECT enquiry_id FROM {$enquiryTableName} WHERE email='{$email_address}'";
    $result = $wpdb->get_col($sql);

    return $result;
}

// terms and conditions functions
/**
 * Replaces placeholders with links to policy page.
 *
 * @param string $text Text to find/replace within.
 * @return string
 */
function quoteupReplacePolicyPageLinkPlaceholders($text)
{
    $privacy_page_id = quoteupPrivacyPolicyPageId();
    $privacy_link    = $privacy_page_id ? '<a href="' . esc_url(get_permalink($privacy_page_id)) . '" class="quoteup-privacy-policy-link" target="_blank">' . __('privacy policy', QUOTEUP_TEXT_DOMAIN) . '</a>' : __('privacy policy', QUOTEUP_TEXT_DOMAIN);

    $find_replace = array(
        '[privacy_policy]' => $privacy_link,
    );

    return str_replace(array_keys($find_replace), array_values($find_replace), $text);
}

/**
 * Get the privacy policy page ID.
 *
 * @return int
 */
function quoteupPrivacyPolicyPageId()
{
    $page_id = get_option('wp_page_for_privacy_policy', 0);
    return apply_filters('quoteup_privacy_policy_page_id', 0 < $page_id ? absint($page_id) : 0);
}

/**
 * Get the privacy policy text, if set.
 *
 * @return string
 */
function quoteupGetPrivacyPolicyText()
{
    $settings = get_option('wdm_form_data');
    $text = isset($settings['enquiry_privacy_policy_text']) && !(empty($settings['enquiry_privacy_policy_text'])) ? quoteupReturnWPMLVariableStrTranslation($settings['enquiry_privacy_policy_text']) : defaultTermsCondFieldText();

    return trim(apply_filters('quoteup_get_privacy_policy_text', $text));
}


/**
 * Returns default enquiry terms and conditions field text.
 *
 * @return string Returns default enquiry terms and conditions field text.
 */
function defaultTermsCondFieldText()
{
    $text = __('I allow the Site owner to contact me via email/phone to discuss this enquiry. (If you want to know more about the way this site handles the data, then please go through our [privacy_policy])', 'quoteup');
    return apply_filters('quoteup_default_terms_cond_field_text', $text);
}
