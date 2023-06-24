<?php

namespace Includes\Admin\Privacy;

/*
 * Personal data eraser.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * QuoteupPrivacyErasers Class.
 */
class QuoteupPrivacyErasers
{
    /**
     * Finds and exports Enquiry data by email address.
     *
     * @since 6.2.0
     *
     * @param string $email_address The user email address.
     * @param int    $page          Page.
     *
     * @return array An array of personal data in name value pairs
     */
    public static function enquiryDataEraser($email_address, $page)
    {
        //Get fields name and datatype which needs to be anonimized.
        $dataToBeDeleted = get_option('wdm_privacy_anonymize_data');

        $response = array(
            'items_removed' => false,
            'items_retained' => false,
            'messages' => array(),
            'done' => true,
        );

        if (!isset($dataToBeDeleted['delete_enquiry_data_during_anonymization']) || $dataToBeDeleted['delete_enquiry_data_during_anonymization'] !=1) {
            $response['messages'][] = __('Personal data from enquiries is not removed as it is disabled', QUOTEUP_TEXT_DOMAIN);
            return $response;
        }

        $enquiryIds = getEnquiryWithEmail($email_address);

        if (0 < count($enquiryIds)) {
            foreach ($enquiryIds as $enquiryId) {
                self::removeEnquiryPersonalData($enquiryId, $dataToBeDeleted);

                $response['messages'][] = sprintf(__('Removed personal data from enquiry #%s.', QUOTEUP_TEXT_DOMAIN), $enquiryId);
                $response['items_removed'] = true;
            }
        }

        unset($page);
        return $response;
    }

    public static function removeEnquiryPersonalData($enquiryId, $dataToBeDeleted = array())
    {
        global $wpdb;
        $enquiryTableName = getEnquiryDetailsTable();
        $enquiryMetaTableName = getEnquiryMetaTable();
        $sql = "SELECT * FROM $enquiryTableName WHERE enquiry_id = $enquiryId";
        $data = $wpdb->get_row($sql, ARRAY_A);
        $sql = "SELECT meta_key,meta_value FROM $enquiryMetaTableName WHERE enquiry_id = $enquiryId";
        $metaDataFromTable = $wpdb->get_results($sql, ARRAY_A);
        $metaData = array();

        //Convert meta deta in key value format.
        foreach ($metaDataFromTable as $value) {
            $metaData[$value['meta_key']] = $value['meta_value'];
        }

        if (empty($dataToBeDeleted)) {
            //Get fields name and datatype which needs to be anonimized.
            $dataToBeDeleted = get_option('wdm_privacy_anonymize_data');
        }

        /**
         * Add Anonimised data to array so we will update values in array
         * in database.
         * This is only for values in enquiry details table
         */
        $anonimisedEnquiryData = self::anonimizedDataArray($dataToBeDeleted['enquiry-detail'], $data);

        /**
         * Update anonimized data in enquiry table.
         */
        if (is_array($anonimisedEnquiryData) && !empty($anonimisedEnquiryData)) {
            $wpdb->update(
                $enquiryTableName,
                $anonimisedEnquiryData,
                array('enquiry_id' => $enquiryId)
            );
        }

        /**
         * Add Anonimised data to array so we will update values in array
         * in database.
         * This is only for values in enquiry meta table
         */
        $anonimisedEnquiryMetaData = self::anonimizedDataArray($dataToBeDeleted['enquiry-meta'], $metaData);

        if (is_array($anonimisedEnquiryMetaData) && !empty($anonimisedEnquiryMetaData)) {
            foreach ($anonimisedEnquiryMetaData as $metaKey => $metaValue) {
                /**
                 * Update anonimized data in enquiry meta table.
                 */
                updateEnquiryMeta($enquiryId, $metaKey, $metaValue);
            }
        }

        /**
         * Delete files while anonymisation.
         */
        if (isset($dataToBeDeleted['delete_file_uploads_during_anonymization']) && $dataToBeDeleted['delete_file_uploads_during_anonymization'] == 1) {
            // Delete files in directory.
            deleteDirectoryIfExists($enquiryId);
            
            // Delete main directory
            $upload_dir = wp_upload_dir();
            $path = $upload_dir[ 'basedir' ].'/QuoteUp_Files/';
            rmdir($path.$enquiryId);
        }

        updateEnquiryMeta($enquiryId, '_anonymized', 'yes');
    }

    protected static function anonimizedDataArray($dataToBeDeleted, $data)
    {
        $anonimisedData = array();
        foreach ($dataToBeDeleted as $key => $dataType) {
            $anonimisedData[$key] = '';
            if (function_exists('wp_privacy_anonymize_data') && isset($data[$key])) {
                $anonimisedData[$key] = wp_privacy_anonymize_data($dataType, $data[$key]);
            }
        }

        return $anonimisedData;
    }

    public static function removeOrderEnquiryData($order)
    {
        $dataToBeDeleted = get_option('wdm_privacy_anonymize_data');

        if (!isset($dataToBeDeleted['delete_enquiry_data_during_order_anonymization']) || $dataToBeDeleted['delete_enquiry_data_during_order_anonymization'] != 1) {
            return;
        }

        $orderId = $order->get_id();
        $checkOrderEnquiry = get_post_meta($orderId, 'quoteup_enquiry_id', true);
        if ($checkOrderEnquiry) {
            self::removeEnquiryPersonalData($checkOrderEnquiry, $dataToBeDeleted);
        }
    }
}
