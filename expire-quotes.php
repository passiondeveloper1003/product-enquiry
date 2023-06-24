<?php

/**
 * This file sets quote expired who meet the expiration date.
 * Selects all the enquiries whose expiration date is less than current_time(It runs as a Cron * job so enquiries remain for a particular amount of time as set in Cron time).
 * Updates the status of all such entries as Expired in the wp_enquiry_history table.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('quoteupExpireQuotes', 'quoteupExpireQuotesCallback');

/**
 * Function that actually expires quotes.
 * this deletes the enquiry ids whose expiration date is less than the current_time.
 * Selects all such entries and implodes all the data in a single string.
 * Gets the required entries from the wp_enquiry_history table.
 * Updates the status of the above selected entries to expired.
 */
function quoteupExpireQuotesCallback()
{
    require_once QUOTEUP_PLUGIN_DIR.'/includes/class-quoteup-manage-history.php';
    global $wpdb, $quoteupManageHistory;

    $today = current_time('Y-m-d');
    //Search all enquiries whose expiration date is last day or before
    $table = getEnquiryDetailsTable();
    $enquiry_ids = $wpdb->get_col($wpdb->prepare("SELECT enquiry_id FROM $table WHERE DATE(expiration_date)  < %s AND DATE(expiration_date) IS NOT NULL ", $today));

    if ($enquiry_ids) {
        //Pastes all the enquiry details to a single string.
        $all_enquiries = implode(', ', $enquiry_ids);
        // Find all Enquries whose last status was either Sent, Saved or Approved in the wp_enquiry_history table.
        $getRequiredEnquiries = $wpdb->get_col("SELECT s1.enquiry_id FROM {$wpdb->prefix}enquiry_history s1 LEFT JOIN {$wpdb->prefix}enquiry_history s2 ON s1.enquiry_id = s2.enquiry_id AND s1.id < s2.id WHERE s2.enquiry_id IS NULL AND s1.status IN ('Sent','Saved','Approved') AND s1.enquiry_id IN ($all_enquiries) AND s1.id > 0");
        if ($getRequiredEnquiries) {
            foreach ($getRequiredEnquiries as $singleEnquiryId) {
                //Expire all such enquiries
                $quoteupManageHistory->addQuoteHistory($singleEnquiryId, '-', 'Expired');
            }
        }
    }
}
