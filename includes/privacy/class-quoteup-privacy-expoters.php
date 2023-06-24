<?php
namespace Includes\Admin\Privacy;

/**
 * Personal data exporters.
 *
 * @since 6.2.0
 */
defined('ABSPATH') || exit;

/**
 * WC_Privacy_Exporters Class.
 */
class QuoteupPrivacyExporters
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
    public static function quoteupGetEnquiryData($email_address, $page)
    {
        $data_to_export = array();

        $enquiryIds = getEnquiryWithEmail($email_address);

        if (0 < count($enquiryIds)) {
            foreach ($enquiryIds as $enquiryId) {
                $data_to_export[] = array(
                    'group_id' => 'quoteup_enquiry',
                    'group_label' => __('Enquiries', QUOTEUP_TEXT_DOMAIN),
                    'item_id' => 'enquiry-'.$enquiryId,
                    'data' => self::quoteupGetSingleEnquiryData($enquiryId),
                );
            }
        }

        return array(
            'data' => $data_to_export,
            'done' => true,
        );
        unset($page);
    }

    /**
     * Get personal data (key/value pairs) for a single enquiry.
     *
     * @since 6.2.0
     *
     * @param int $enquiryId enquiry id.
     *
     * @return array
     */
    protected static function quoteupGetSingleEnquiryData($enquiryId)
    {
        global $wpdb;
        $enquiryTableName = getEnquiryDetailsTable();
        $enquiryMetaTableName = getEnquiryMetaTable();
        $enquiryData = array();
        $sql = "SELECT enquiry_id, name, email, message, phone_number, subject, enquiry_ip FROM $enquiryTableName WHERE enquiry_id = $enquiryId";
        $data = $wpdb->get_row($sql, ARRAY_A);
        $sql = "SELECT meta_key,meta_value FROM $enquiryMetaTableName WHERE enquiry_id = $enquiryId";
        $metaData = $wpdb->get_results($sql, ARRAY_A);

        foreach ($metaData as $key => $value) {
            $data[$value['meta_key']] = $value['meta_value'];
        }

        $metaKeysToRemove = array('_admin_quote_created', 'enquiry_lang_code', 'quotation_lang_code', '_unread_enquiry');

        //Remove keys which we don't want to export
        foreach ($metaKeysToRemove as $key => $value) {
            unset($data[$value]);
        }

        foreach ($data as $key => $value) {
            $enquiryData[] = array(
                    'name' => $key,
                    'value' => $value,
                );
        }

        return $enquiryData;
    }
}
