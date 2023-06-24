<?php

namespace Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class for Database operations.
 * Updated the Order Id of Quote when order is placed.
 * Gets the order Id for that Quote during oder placement.
 */
class QuoteupOrderQuoteMapping
{
    /**
     * This function is used to update order id in the database(enquiry_detail_new) table.
     * It acts as a flag for order Placed as well used for future reference.
     *
     * @param [int] $enquiry_id Enquiry id of the enquiry
     * @param [int] $orderID    Orderr id genrated for that enquiry after the order is placed
     */
    public static function updateOrderIDOfQuote($enquiry_id, $orderID)
    {
        global $wpdb;
        $table_name = getEnquiryDetailsTable();
        $wpdb->update(
            $table_name,
            array(
            'order_id' => $orderID,
            ),
            array(
            'enquiry_id' => $enquiry_id,
            )
        );

        /**
         * After order Id is updated in the 'enquiry_detail_new' table.
         *
         * @param  int  $enquiry_id  Enquiry Id.
         * @param  int  $orderID  Order Id.
         */
        do_action('quoteup_update_order_id_quote', $enquiry_id, $orderID);
    }

    /**
     * Get status of link
     * While link is visited it is used to check is link used before.
     *
     * In enquiry details it is used to notify admin if order is placed for that particular Enquiry ID
     *
     * @param [int] $enquiry_id Enquiry id of the enquiry
     *
     * @return [int] order id if the order is Placed
     */
    public static function getOrderIdOfQuote($enquiry_id)
    {
        global $wpdb;
        $table_name = getEnquiryDetailsTable();
        if ($enquiry_id == 0) {
            if (isset($_GET[ 'quoteupHash' ])) {
                $hash = $_GET[ 'quoteupHash' ];

                $enquiry_id = explode('_', $hash);
                $enquiry_id = $enquiry_id[ 0 ];
                $sql = $wpdb->prepare("SELECT order_id FROM $table_name WHERE enquiry_id=%d AND enquiry_hash=%s", $enquiry_id, $hash);
            }
        } else {
            $sql = $wpdb->prepare("SELECT order_id FROM $table_name WHERE enquiry_id=%d", $enquiry_id);
        }
        if (isset($sql)) {
            $result = $wpdb->get_row($sql, ARRAY_A);

            $orderId = apply_filters('quoteup_order_id_of_quote', isset($result['order_id']) ? $result['order_id'] : null, $enquiry_id);

            return $orderId;
        }
    }
}
